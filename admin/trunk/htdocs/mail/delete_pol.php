<?php
//
// File: delete_pol.php
//
// Template File: delete_pol.tpl
//
// Template Variables:
//


require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
global $SESSID_USERNAME;

if ( check_admin($SESSID_USERNAME) ){

	if ($_SERVER['REQUEST_METHOD'] == "GET"){

		$fSrc = get_get('fsrc');
		$fIp = get_get('fip');
		$fHelo = get_get('fhelo');
		$fEmail = get_get('femail');

		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/mail/menu-policyd.tpl");

		if ( $fSrc == "blacklist" ){
			$result = del_bl_host($fIp);
			$list_blacklisted_host = get_bl_host();
			include ("../templates/mail/policyd_bl.tpl");
		}

		if ( $fSrc == "whitelist" ){
			$result = del_wl_host($fIp);
			$list_whitelisted_host = get_wl_host();
			include ("../templates/mail/policyd_wl.tpl");
		}

		if ( $fSrc == "helo" ){
			$result = del_bl_helo($fHelo);
			$list_blacklist_helo = get_bl_helo();
			include ("../templates/mail/policyd_bl_helo.tpl");
		}

		if ( $fSrc == "email" ){
			$result = del_bl_sender($fEmail);
			$list_blacklist_sender = get_bl_sender();
			include ("../templates/mail/policyd_bl_sender.tpl");
		}

		include ("../templates/footer.tpl");

	}

 }

?>