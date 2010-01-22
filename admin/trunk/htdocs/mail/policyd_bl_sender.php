<?php
//
// File: policyd_bl_sender.php
//
// Template File: policyd_bl_sender.tpl
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

		$list_blacklist_sender = get_bl_sender();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_bl_sender.tpl");
		include ("../templates/footer.tpl");

	}

	if ($_SERVER['REQUEST_METHOD'] == "POST"){

		$fSender = get_post('fSender');
		$fDesc = "# ".get_post('fDesc');
		$fExp = get_post('fExp');
		if ( $fExp != 0 ){
			$fExp = time() + 86400 * $fExp;
		}

		$result = add_bl_sender($fSender,$fDesc, $fExp);

		$list_blacklist_sender = get_bl_sender();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_bl_sender.tpl");
		include ("../templates/footer.tpl");

	}



 }

?>