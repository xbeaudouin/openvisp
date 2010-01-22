<?php
//
// File: ajax/server_model.php
//
// Template File:
//
// Template Variables:
//
// 
//
// Form POST \ GET Variables:
//
// fRole_name
// fType
//

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

$buffer = '<?xml version="1.0"?>'; 
$buffer .= "<resultat>";


if ($_SERVER['REQUEST_METHOD'] == "GET")
	{

	$fRole_name = get_get("fRole_name");
	$fType = get_get("fType");

	if ( $fRole_name != NULL && $fType == "model" ){
		$dbuser_available = "";
		$dbuser_available = check_modelname_available($fRole_name);
		$buffer .= '<role_name>';
		$buffer .= '<available>'. $dbuser_available .'</available>';
		$buffer .= '</role_name>';
	}


	}

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fRole_name = get_post("fRole_name");
	$fType = get_post("fType");

	if ( $fRole_name != NULL && $fType == "model" ){
		$dbuser_available = "";
		$dbuser_available = check_modelname_available($fRole_name);
		$buffer .= '<role_name>';
		$buffer .= '<available>'. $dbuser_available .'</available>';
		$buffer .= '</role_name>';
	}


 }


$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 