<?php
//
// File: edit-active.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDomain = get_get('domain');

   $result = db_query ("UPDATE domain SET active=1-active WHERE domain='$fDomain'");
   if ($result['rows'] != 1)
   {
      $error = 1;
      $tMessage = $PALANG['pAdminEdit_domain_result_error'];
   }

   // When master domain is not active then set the aliase to off as well
   $result = db_query ("UPDATE domain_alias, domain SET domain_alias.active=domain.active WHERE domain.domain='$fDomain' AND domain.id=domain_alias.domain_id ");

   // When master domain is not active then set the mailboxes to off as well
   $result = db_query ("UPDATE mailbox, domain SET mailbox.active=domain.active  WHERE domain.domain='$fDomain' AND domain.id=mailbox.domain_id");

   
   if ($error != 1)
   {
      header ("Location: list-domain.php");
      exit;
   }
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
