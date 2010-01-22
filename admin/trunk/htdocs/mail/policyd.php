<?php
//
// File: policyd.php
//
// Template File: policyd.tpl
//
// Template Variables:
//


require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ( check_admin($SESSID_USERNAME) ){

	if ($_SERVER['REQUEST_METHOD'] == "GET"){

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd.tpl");
		include ("../templates/footer.tpl");

	}

 }

?>