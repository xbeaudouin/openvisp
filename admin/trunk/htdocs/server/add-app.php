<?php
//
// File: server/add_server.php
//
// Template File: server/add_server.tpl
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
require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/server/menu.tpl");

   include ("../templates/footer.tpl");
}


if ($_SERVER["REQUEST_METHOD"] == "POST")
{


}
