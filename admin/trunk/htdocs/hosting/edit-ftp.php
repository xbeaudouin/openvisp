<?php
//
// File: hosting/edit-ftp.php
//
// Template File: hosting/edit-ftp.tpl
//
// Template Variables:
//
// tMessage
// tGoto
//
// Form POST \ GET Variables:
//
// fAddress
// fDomain
// fGoto
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ( check_ftp_admin($SESSID_USERNAME) == FALSE ){
	header ("Location: ../users/main.php");
	exit;
 }

if ($_SERVER['REQUEST_METHOD'] == "GET")
{



	$fFtpaccount = get_get('ftpaccount');
	$fDomain  = get_get('domain');
	$fGpass  = get_get('gpass');
	$fDomain_id = get_domain_id($fDomain);


   if (check_owner($SESSID_USERNAME, $fDomain))
   {
      $result = db_query ("SELECT * FROM ftpaccount WHERE login='$fFtpaccount' AND domain_id='$fDomain_id'");
      if ($result['rows'] == 1)
      {
         $row = db_array ($result['result']);
				 $tLogin = $row['login'];
				 $tQuotafs = $row['quotafs'];
				 $tQuotasz = $row['quotasz'];
				 $tActive = $row['active'];
				 $tDir = $row['dir'];
				 $tRatioul = $row['ratioul'];
				 $tRatiodl = $row['ratiodl'];
				 $tBwul = $row['bandwidthul'];
				 $tBwdl = $row['bandwidthdl'];

				 //         $tFtpaccount = $row['goto'];
      }

			if ( $fGpass == true ){
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
				$password = pacrypt ($fPassword);	
				$result = db_query ("UPDATE ftpaccount SET password='$fPassword', modified=NOW() where login='$fFtpaccount' AND domain='$fDomain'");

				if ($result['rows'] != 1)
					{
						$tMessage = $PALANG['pWhostCreate_ftp_update_password_error'];
					}
				else
					{
						$tMessage = $PALANG['pWhostCreate_ftp_update_password'];
						db_log ($SESSID_USERNAME, $fDomain, "FTP Change Password", $fFtpaccount." ".$fDomain);
					}

			}
   }

   
   include ("../templates/header.tpl");
   include ("../templates/hosting/menu.tpl");
   include ("../templates/hosting/edit-ftp.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   
   $fActive = get_post('fActive');
   $fQuotadisk  = get_post('fQuotadisk');
   $fMaxfilesize  = get_post('fMaxfilesize');
   $fBwul  = get_post('fBwul');
   $fBwdl  = get_post('fBwdl');
	 if ($CONF['ftp_ratio'] == 'YES'){
		 $fRtdl  = get_post('fRtdl');
		 $fRtul  = get_post('fRtul');
	 }
	 else{
		 $fRtdl  = '-1';
		 $fRtul  = '-1';
	 }
	 
	 $fLogin = get_post('ftpaccount');
	 $fDomain = get_post('domain');

	 $tLogin = $fLogin;
	 $tDomain = $fDomain;
	 $tActive = $fActive;
	 $tMaxfilesize = $fMaxfilesize;
	 $tBwdl = $fBwdl;
	 $tBwul = $fBwul;
	 $tQuotafs = $fQuotadisk;
	 $tQuotasz = $fMaxfilesize;


   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_ftp_error'] . "$fUsername</font>";
   }   
   
   if (empty ($fQuotadisk))
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_ftp_quota_error'];
   }

   if (empty ($fMaxfilesize))
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_ftp_maxsize_error'];
   }

   if (empty ($fBwul))
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_ftp_bwul_error'];
   }


   
   if ($error != 1)
   {
		 if ($fActive == "on") $fActive = 1;
      $result = db_query ("UPDATE ftpaccount SET active='$fActive',quotafs='$fQuotadisk',ratioul='$fRtul',ratiodl='$fRtdl',quotasz='$fMaxfilesize',bandwidthul='$fBwul',bandwidthdl='$fBwdl',modified=NOW() WHERE login='$fLogin' AND domain='$fDomain'");
      if ($result['rows'] != 1)
      {
         $tMessage = $PALANG['pEdit_alias_result_error'];
      }
      else
      {
         db_log ($SESSID_USERNAME, $fDomain, "edit ftpaccount", "active=>$fActive, ratioul=>$fRtul, ratiodl=>$fRtdl, quotafs=>$fQuotadisk, quotasz=>$fMaxfilesize, bandwidthul=>$fBwul, bandwidthdl=>$fBwdl");
				 header ("Location: list-ftp.php?username=$SESSID_USERNAME");
         exit;
      }
   }
   
   include ("../templates/header.tpl");
   include ("../templates/hosting/menu.tpl");
   include ("../templates/hosting/edit-ftp.tpl");
   include ("../templates/footer.tpl");
}
?>
