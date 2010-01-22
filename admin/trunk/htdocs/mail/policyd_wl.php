<?php
//
// File: policyd_bl.php
//
// Template File: policyd_bl.tpl
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

		$list_whitelisted_host = get_wl_host();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_wl.tpl");
		include ("../templates/footer.tpl");

	}

	if ($_SERVER['REQUEST_METHOD'] == "POST"){

		$fIp = get_post('fIp');
		$fDesc = "# ".get_post('fDesc');
		$fExp = get_post('fExp');
		if ( $fExp != 0 ){
			$fExp = time() + 86400 * $fExp;
		}

		$result = add_wl_host($fIp, $fDesc, $fExp);

		$list_whitelisted_host = get_wl_host();

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");
		include ("../templates/mail/policyd_wl.tpl");
		include ("../templates/footer.tpl");

	}


} 

?>