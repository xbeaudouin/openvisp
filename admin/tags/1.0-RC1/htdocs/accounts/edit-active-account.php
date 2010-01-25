<?php
//
// File: accounts/edit-active-account.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fUsername
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fUsername = get_get('username');
   
   $result = db_query ("UPDATE accounts SET enabled=1-enabled WHERE username='$fUsername'");
   if ($result['rows'] != 1)
   {
      $error = 1;
      $tMessage = $PALANG['pAccountEdit_account_active_error'];
   }
   
   if ($error != 1)
   {
      header ("Location: list-accounts.php");
      exit;
   }
   
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
