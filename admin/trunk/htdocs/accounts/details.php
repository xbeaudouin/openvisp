<?php
//
// File: details.php
//
// Template File: accounts/details.tpl
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

$SESSID_USERNAME = check_admin_session();

$list_accounts = list_accounts ();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fUsername = get_get('username');
   if ($fUsername != NULL)
   {
		 $user_information = get_account_info($fUsername);
		 $user_domains = list_domains_for_users ($fUsername);
		 $account_properties = get_account_properties ($fUsername);
		 $datacenter_accounts = list_datacenter_accounts ($fUsername);
		 $mailbox_accounts = list_mailbox_accounts($fUsername);
		 $ftp_accounts = list_ftp_accounts ($user_information['id']);
		 $is_big_admin = check_admin($fUsername);
   }
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/details.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fUsername = get_post('fUsername');

   $account_properties = get_accounts_properties ($fUsername);

   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/details.tpl");
   include ("../templates/footer.tpl");
}
?>
