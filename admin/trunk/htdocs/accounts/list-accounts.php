<?php
//
// File: main.php
//
// Template File: main.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session ();

$list_accounts = list_accounts ();
if ((is_array ($list_accounts) and sizeof ($list_accounts) > 0))
{
   for ($i = 0; $i < sizeof ($list_accounts); $i++)
   {
      $account_properties[$i] = get_account_properties ($list_accounts[$i]);
   }
}

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}
?>
