<?php
//
// File: hosting/mailbox_info.php
//
// Template File:
//
// Template Variables:
//
// 
//
// Form POST \ GET Variables:
//
// fDomain
// fUsername
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$buffer = '<?xml version="1.0"?>'; 
$buffer .= "<resultat>";



if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fDomain = get_get("fDomain");
	$fUsername = get_get("fUsername");
	$fType = get_get("fType");


	if ( $fDomain != NULL && $fType == "mailbox_exist" && $fUsername != NULL ){
		$mailbox_account_exist = check_mailbox_exist($fUsername, $fDomain);
		$buffer .= '<mailbox>';
		$buffer .= '<exist>'. $mailbox_account_exist .'</exist>';
		$buffer .= '</mailbox>';
	}

 }


if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain = get_post("fDomain");
	$fUsername = get_post("fUsername");
	$fType = get_post("fType");


	if ( $fDomain != NULL && $fType == "mailbox_exist" && $fUsername != NULL ){
		$mailbox_account_exist = check_mailbox_exist($fUsername, $fDomain);
		$buffer .= '<mailbox>';
		$buffer .= '<exist>'. $mailbox_account_exist .'</exist>';
		$buffer .= '</mailbox>';
	}


 }


$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 