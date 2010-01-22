<?php
//
// File: hosting/edit-active-ftp.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fAccount
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fAccount = get_get('account');
   $fDomain = get_get('domain');
   
   $result = db_query ("UPDATE ftpaccount SET active=1-active,modified=NOW() WHERE login='$fAccount'");
   if ($result['rows'] != 1)
   {
      $error = 1;
      $tMessage = $PALANG['pWhostEdit_ftp_active_error'];
   }

   if ($error != 1)
   {
      db_log ($SESSID_USERNAME, $fDomain, "FTP account disabled ($fAccount)", $fUsername);
      header ("Location: list-ftp.php?username=$SESSID_USERNAME");
      exit;
   }
   
   include ("../templates/header.tpl");
   include ("../templates/admin_menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/admin_menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
