<?php
//
// File: server/manage.php
//
// Template File: server/manage.tpl
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
//require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{

	$message="";

	include ("../templates/header.tpl");
	include ("../templates/server/menu.tpl");
	include ("../templates/server/manage.tpl");
	include ("../templates/footer.tpl");

}


?>
