<?php
//
// File: histo-ftp.php
//
// Template File: hosting/histo-ftp.tpl
//
// Template Variables:
//
// tFtpaccount
//
// Form POST \ GET Variables:
//
// account
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



if ( $SESSID_USERNAME != "" )
{
  if (check_webhosting_admin($SESSID_USERNAME))
  {

		if ($_SERVER['REQUEST_METHOD'] == "GET")
			{
				$fFtplogin = get_get('account');
				$list_date_ftp = get_list_date_ftp($fFtplogin);
				$fPeriode = get_get('fPeriode');
				include ("../templates/header.tpl");
				include ("../templates/hosting/menu.tpl");
				include ("../templates/hosting/histo-ftp.tpl");
				include ("../templates/footer.tpl");
			}

		if ($_SERVER['REQUEST_METHOD'] == "POST")
			{
				$fFtplogin = get_post('account');
				$list_date_ftp = get_list_date_ftp($fFtplogin);
				$fPeriode = get_post('fPeriode');
				include ("../templates/header.tpl");
				include ("../templates/hosting/menu.tpl");
				include ("../templates/hosting/histo-ftp.tpl");
				include ("../templates/footer.tpl");
				
			}

  }
}




?>
