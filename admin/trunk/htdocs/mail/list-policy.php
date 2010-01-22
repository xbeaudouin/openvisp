<?php
//
// File: list-policy.php
//
// Template File: list-policy.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

$tPolicy = array();

$result = db_query("SELECT * FROM policy order by domain");
if ($result['rows'] > 0) {
  while ( $row = db_array ($result['result']) ) {
    $tPolicy[] = $row;
  }
}

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/list-policy.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/list-policy.tpl");
   include ("../templates/footer.tpl");
}
?>
