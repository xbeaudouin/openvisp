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
include ("../languages/" . check_language () . ".lang");

//$SESSID_USERNAME = check_session ();
// if ( $SESSID_USERNAME == NULL ){
$SESSID_USERNAME = check_user_session ();
//  }

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
	 //	 include("../templates/users/mail_main.tpl");
	 include("../templates/mail/main.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/main.tpl");
   include ("../templates/footer.tpl");
}
?>
