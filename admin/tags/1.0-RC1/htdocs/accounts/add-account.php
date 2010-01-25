<?php
//
// File: add-account.php
//
// Template File: accounts/add-account.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// fUsername
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/add-account.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   $fUsername   = get_post('fUsername');
   $fPassword   = get_post('fPassword');
   $fPassword2  = get_post('fPassword2');
   $fMail       = get_post('fMail');
   $fDatacenter = get_post('fDatacenter');
   $fFtp        = get_post('fFtp');
   $fWebsite    = get_post('fWebsite');
	 $fNbmail     = get_post('fNbmail');
	 $fNbaliasmail = get_post('fNbaliasmail');
	 $fNftpaccount = get_post('fNftpaccount');
	 $fNwebsite   = get_post('fNwebsite');
	 $fNdomains   = get_post('fNdomains');
	 $fNmysql     = get_post('fNmysql');
	 $fNpostgresql = get_post('fNpostgresql');
	 $fDomains   = get_post('fDomains');
	 $fMysql     = get_post('fMysql');
	 $fPostgresql = get_post('fPostgresql');

//    if (!check_email ($fUsername))
// 		 {
// 			 $tMessage = $PALANG['p']
// 			 $error = 1;
// 		 }

   if (($fUsername == NULL) or user_exist($fUsername))
   {
      $error = 1;
   }

   if ($fPassword == NULL ){

    if ($CONF['generate_password'] == "YES")
      {
				if ($CONF['password_generator'] == ""){
          $fPassword = generate_password ();
				}
				else
					{
						$fPassword = exec($CONF['password_generator']);
					}
      }
		else
      {
				$error = 1;
				$tUsername = $fUsername;
				$tName = $fName;
				$tQuota = $fQuota;
				$tDomains = $fDomains;
				$pCreate_mailbox_password_text = $PALANG['pCreate_mailbox_password_text_error'];
      }
	 }

	 if ($fPassword != $fPassword2)
     {
			 $tUsername = $fUsername;
			 $tName = $fName;
			 $tQuota = $fQuota;
			 $tDomains = $fDomains;
			 $pCreate_mailbox_password_text = $PALANG['pCreate_mailbox_password_text_error'];
     }
	 
   if ($error != 1)
		 {
			 // Create the user into database
			 $password = pacrypt("$fPassword");
			 if (add_admin_user($fUsername, $password) == NULL)
				 {
					 $tMessage = $PALANG['pAccountCreate_account_result_error'] . "<br />($fUsername)<br />";
				 }
			 else
				 {
					 // Chech and modify the rights
					 if ($fMail       == "on") { $fMail       = 1; } else { $fMail       = 0; }
					 if ($fDatacenter == "on") { $fDatacenter = 1; } else { $fDatacenter = 0; }
					 if ($fFtp        == "on") { $fFtp        = 1; } else { $fFtp        = 0; }
					 if ($fWebsite    == "on") { $fWebsite    = 1; } else { $fWebsite    = 0; }
					 if ($fDomains    == "on") { $fDomains    = 1; } else { $fDomains    = 0; }
					 if ($fMysql      == "on") { $fMysql    = 1; } else { $fMysql    = 0; }
					 if ($fPostgresql     == "on") { $fPostgresql    = 1; } else { $fPostgresql    = 0; }

					 $admin_id = get_account_id($fUsername);
					 $result = db_query("INSERT INTO rights (accounts_id,mail,domain,datacenter,ftp,http,mysql,postgresql) VALUES ('$admin_id','$fMail','$fDomains','$fDatacenter','$fFtp','$fWebsite','$fMysql','$fPostgresql')");
					 if ($result['rows'] != 1)
						 {
							 $tMessage = $PALANG['pAccountCreate_account_result_error2'] . "<br />($fUsername)<br />";
						 }
					 
					 $tMessage = $PALANG['pAccountCreate_account_result_success'] . "<br />($fUsername)</br />";
					 add_quota_admin( $admin_id, $fNmysql, $fNmysql, $fNpostgresql, $fNpostgresql, $fNdomains, $fNwebsite,$fNwebsite, $fNftpaccount, $fNbmail, $fNbaliasmail);
					 
				 } 
		 }	
	 else
		 {
			 $tMessage .= "ERREUR";
		 }
   
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/add-account.tpl");
   include ("../templates/footer.tpl");
 }

?>
