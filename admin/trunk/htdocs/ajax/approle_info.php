<?php
//
// File: ajax/approle_info.php
//
// Template File:
//
// Template Variables:
//
// 
//
// Form POST \ GET Variables:
//
// fRole_id
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

	$fServer_name = get_get("fServer_name");
	$fType = get_get("fType");
	$fServer_prv_ip = get_get("fServer_prv_ip");

	if ( $fServer_name != NULL && $fType == "server" ){
		$name_available = "";
		$name_available = check_servername_available($fServer_name);
		$buffer .= '<server_name>';
		$buffer .= '<available>'. $name_available .'</available>';
		$buffer .= '</server_name>';
	}


	}

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fAppmodel_id = get_post("fAppmodel_id");

	if ( $fAppmodel_id != NULL ){
		$list_app = "";
		$list_app = list_app_job($fAppmodel_id);

		for ( $i=0; $i < sizeof($list_app); $i++ ){

			$buffer .= '<application>';
			$buffer .= '<id>'. $list_app[$i]['id'] .'</id>';
			$buffer .= '<name>'. $list_app[$i]['apps'] .'</name>';
			$buffer .= '<version>'. $list_app[$i]['version'] .'</version>';
			$buffer .= '</application>';
		}

	}

	$buffer .= "</resultat>"; 
	header('Content-Type: text/xml'); 
	print $buffer;
	


 }



?> 