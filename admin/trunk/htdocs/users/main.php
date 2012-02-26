<?php
//
// File: users/main.php
//
// Template File: users/main.tpl
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
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

$body_class = 'class="yui3-skin-sam"';

if ($_SERVER["REQUEST_METHOD"] == "GET")
{

	$policyd_server = policyd_server_exist();

   include ("../templates/header.tpl");
   include ("../templates/users/main.tpl");
   include ("../templates/footer.tpl");

}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/users/main.tpl");
   include ("../templates/footer.tpl");
}
?>
