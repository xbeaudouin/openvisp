<?php
//
// File: policyd_bl_helo.php
//
// Template File: policyd_bl_helo.tpl
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

		$list_blacklist_helo = get_bl_helo();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_bl_helo.tpl");
		include ("../templates/footer.tpl");

	}


	if ($_SERVER['REQUEST_METHOD'] == "POST"){

		$fHelo = get_post('fHelo');
		$result = add_bl_helo($fHelo);

		$list_blacklist_helo = get_bl_helo();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_bl_helo.tpl");
		include ("../templates/footer.tpl");

	}


 }

?>