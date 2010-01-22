<?php
//
// File: send_quarantine.php
//
// Template File: 
//
// Template Variables:


require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();


if ( ($_SERVER['REQUEST_METHOD'] == "GET") && (check_admin($SESSID_USERNAME)) ){

	$fUser = get_get('username');
	$info = get_all_spam_key($fUser);
	
	$fTo   = $fUser;
	$fFrom = $SESSID_USERNAME;

	$now = time();
	$error = 1;

	$tSubject = $PALANG['pQuarantine_Subject'];
	$tBody    = $PALANG['pQuarantine_Part1'] . date("d/m/Y\n", $now)."</br>\n";
	
	$tBody    .= '<a href="'.$CONF['release_url'].'?key='.$info['id'].'&key2='.$info['key2'].'">URL</a>';

	require("../lib/send_mail.inc.php");
	send_mail($fFrom,$fTo,$tSubject,$tBody,'','html');

	print "<body onload=\"history.go(-1)\"> </body>";

 }


?>