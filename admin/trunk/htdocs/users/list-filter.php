<?php

	//
	// File: list-filter.php
	//
	// Template : list-filter.tpl
	//
	// Form POST \ GET Variables:
	//
	//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fUsername = $SESSID_USERNAME;
	
	$table_filter = get_email_filter($fUsername);

	$template = "list-filter.tpl";

 }

include ("../templates/header.tpl");
include ("../templates/mail/menu.tpl");
include ("../templates/users/$template");
include ("../templates/footer.tpl");





?>