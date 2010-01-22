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

		$fApp_name = get_get("fApp_name");
		$fApp_version = get_get("fApp_version");

		
		if ( $fApp_name != NULL && $fApp_version != NULL )
			{
				$app_available = check_appname_available($fApp_name, $fApp_version);
				$buffer .= '<app_name>';
				$buffer .= '<available>'. $app_available .'</available>';
				$buffer .= '</app_name>';
			}


	}

if ( $_SERVER['REQUEST_METHOD'] == "POST" )
	{

		$fApp_name = get_post("fApp_name");
		$fApp_version = get_post("fApp_version");

		if ( $fApp_name != NULL && $fApp_version != NULL )
			{
				
				$app_available = check_appname_available($fApp_name, $fApp_version);
				$buffer .= '<app_name>';
				$buffer .= '<available>'. $app_available .'</available>';
				$buffer .= '</app_name>';
			}
	}


$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 

?> 