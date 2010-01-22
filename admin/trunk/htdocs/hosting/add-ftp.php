<?php
//
// File: hosting/add-ftp.php
//
// Template File: hosting/add-ftp.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fDomain
// fPath
// fLogin
// fPassword
// fPassword2
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ( $SESSID_USERNAME != "")
{

  if ( (check_ftp_admin($SESSID_USERNAME)) && ( ( $total_used['ftp'] <= $account_quota['ftp']) || ($account_quota['ftp'] == "-1" ) ) )
  {
    if ($_SERVER['REQUEST_METHOD'] == "GET")
    {
       if (isset ($_GET['username']))
       {
         $fUsername = get_get('username');
         
				 //         $list_domains = list_web_domains_for_admin($fUsername);
				 $list_domains = list_domains_ftpaccount_available($fUsername);
				 $list_ftp_server = list_server_ftpd();
       }
    
       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       include ("../templates/hosting/add-ftp.tpl");
       include ("../templates/footer.tpl");
    }
    
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {

			$fDomain = get_post('fDomain');
			$fPath = get_post('fPath');
			$fLogin = get_post('fLogin');
			$fVhost = get_post('fVirtualid');
			$fPassword = get_post('fPassword');
			$fPassword2 = get_post('fPassword2');
			$fDomain_id = get_domain_id($fDomain);
			$fServerid = get_post('fServerid');

			if ( $fDomain != NULL && $fLogin != NULL )
        {
          $website = explode('.',$fDomain);
          if (sizeof($website) == 2)
						{
							$vhost = '';
							$domain = $website[0] . "." . $website[1];
							if ( $CONF['ftp_login_method'] == '2' ){
								$login = $domain. "_" . $fLogin;
							}
							else{
								$login = $fLogin . "." . $domain;
							}
						}
          if (sizeof($website) == 3)
						{
							$vhost = $website[0];
							$domain = $website[1] . "." . $website[2];
							if ( $CONF['ftp_login_method'] == '2' ){
								$login = $website[1] . "_" . $domain;
							}
							else{
								$login = $fLogin . "." . $domain;
							}
						}
          $result = db_query ("SELECT login FROM ftpaccount WHERE login = '$login'");
          if ($result['rows'] > 0)
						{
							$error = 1;
							$tMessage = $PALANG['pWhostCreate_ftp_exist'];
						}
          if ( $fPassword != NULL && $fPassword2 != NULL )
						{
							if ($fPassword != $fPassword2)
								{
									$error = 1;
									$tMessage = $PALANG['pWhostCreate_ftp_error_password'];
								}
						}
					if ( $fPassword == NULL && $fPassword2 == NULL ){

						if ($CONF['generate_password'] == "YES")
							{
								if ($CONF['password_generator'] == ""){
									$fPassword = generate_password ();
								}
								else
									{
										$fPassword = exec($CONF['password_generator']);
										$fPassword2 = $fPassword;
									}
							}
						else
							{
								$error = 1;
								$tMessage = $PALANG['pWhostCreate_ftp_error_empty_password'];
							}

					}

          if ($error != 1)
						{
            $result = db_query("SELECT DocumentRoot FROM whost WHERE domain_id='$fDomain_id' AND id='$fVhost'");
            $row = db_row ($result['result']);
            $storage = $row[0] . "/" . $fPath;
						$result = db_query("INSERT INTO ftpaccount (login,password,domain_id,dir,uid,gid,created,modified,whost_id,active,owner,server_id) VALUES ('$login','$fPassword','$fDomain_id','$storage','80','80',NOW(),NOW(),'$fVhost','1','$SESSID_USERNAME','$fServerid')");
						db_log ($SESSID_USERNAME, $fDomain, "FTP account (login) has been added", $fUsername);
						header("Location: ./list-ftp.php?username=$SESSID_USERNAME");
						}
					// }
        }

			include ("../templates/header.tpl");
			include ("../templates/hosting/menu.tpl");
			include ("../templates/hosting/add-ftp.tpl");
			include ("../templates/footer.tpl");
		
		}
				
	}
 }

  
?>
