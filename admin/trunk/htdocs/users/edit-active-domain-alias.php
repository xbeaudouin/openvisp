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

$SESSID_USERNAME = check_admin_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDomain = get_get('domain');
   
   $result = db_query ("UPDATE domain_alias SET active=1-active,modified=NOW() WHERE dalias='$fDomain'");
   if ($result['rows'] != 1)
   {
      $error = 1;
      $tMessage = $PALANG['pAdminEdit_domain_result_error'];
   }
   
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
