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
// fServer_name
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

	$fServer_name = get_get("fServer_name");
	$fType = get_get("fType");
	$fServer_prv_ip = get_get("fServer_prv_ip");
	$fServer_id = get_get("fServer_id");


	if ( $fServer_name != NULL && $fType == "server" ){
		$name_available = "";
		$name_available = check_servername_available($fServer_name);
		$buffer .= '<server_name>';
		$buffer .= '<available>'. $name_available .'</available>';
		$buffer .= '</server_name>';
	}

  if ( $fServer_id != NULL )
		{

			$list_server_job = list_server_apps($fServer_id);
			for ($i = 0; $i < sizeof ($list_server_job); $i++)
				{
					$buffer .= "<application>\n";
					$buffer .= "<role>".$list_server_job[$i]['role']."</role>";
					$buffer .= "<role_id>".$list_server_job[$i]['server_jobmodel_id']."</role_id>";
					$buffer .= "<app>".$list_server_job[$i]['apps']."</app>";
					$buffer .= "<app_id>".$list_server_job[$i]['server_apps_id']."</app_id>";
					$buffer .= "<version>".$list_server_job[$i]['version']."</version>";
					$buffer .= "<login>".$list_server_job[$i]['login']."</login>";
					$buffer .= "<password>".$list_server_job[$i]['password']."</password>";
					$buffer .= "<port>".$list_server_job[$i]['port']."</port>";
					$buffer .= "<active>".$list_server_job[$i]['active']."</active>";
					$buffer .= "</application>\n";
				}
		}

	}

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fServer_name = get_post("fServer_name");
	$fServer_fqdn = get_post("fServer_fqdn");
	$fType = get_post("fType");
	$fServer_prv_ip = get_post("fServer_prv_ip");
	$fServer_pub_ip = get_post("fServer_pub_ip");
	$fServer_form  = get_post("fServer_form");
	$fServer_id = get_post("fServer_id");

  if ( $fServer_id != NULL && $fType == "server" )
		{

			$list_server_job = list_server_apps($fServer_id);
			for ($i = 0; $i < sizeof ($list_server_job); $i++)
				{
					$buffer .= "<application>\n";
					$buffer .= "<role>".$list_server_job[$i]['role']."</role>";
					$buffer .= "<role_id>".$list_server_job[$i]['server_jobmodel_id']."</role_id>";
					$buffer .= "<ip_id>".$list_server_job[$i]['server_ip_id']."</ip_id>";
					$buffer .= "<ip>".$list_server_job[$i]['public']."/".$list_server_job[$i]['private']."</ip>";
					$buffer .= "<hostname>".$list_server_job[$i]['hostname']."</hostname>";
					$buffer .= "<app>".$list_server_job[$i]['apps']."</app>";
					$buffer .= "<app_id>".$list_server_job[$i]['server_apps_id']."</app_id>";
					$buffer .= "<version>".$list_server_job[$i]['version']."</version>";
					$buffer .= "<login>".$list_server_job[$i]['login']."</login>";
					$buffer .= "<password>".$list_server_job[$i]['password']."</password>";
					$buffer .= "<port>".$list_server_job[$i]['port']."</port>";
					$list_server_job[$i]['active'] = ( $list_server_job[$i]['active'] == "1") ? "Yes" : "No";
					$buffer .= "<active>".$list_server_job[$i]['active']."</active>";
					$buffer .= "</application>\n";
				}

		}

	if ( $fServer_name != NULL && $fType == "server" ){
		$name_available = "";
		$name_available = check_servername_available($fServer_name,$fServer_id);
		$buffer .= '<server_name>';
		$buffer .= '<available>'. $name_available .'</available>';
		$buffer .= '</server_name>';
	}

	if ( $fServer_fqdn != NULL && $fType == "server" ){
		$name_available = "";
		$name_available = check_fqdn_available($fServer_fqdn,$fServer_id);
		$buffer .= '<server_fqdn>';
		$buffer .= '<available>'. $name_available .'</available>';
		$buffer .= '</server_fqdn>';
	}


	if ( $fServer_prv_ip != NULL && $fType == "server" ){
		$ip_available = "";
		$ip_available = check_server_prvip_available($fServer_prv_ip,$fServer_id);
		$buffer .= '<server_prv_ip>';
		$buffer .= '<available>'. $ip_available .'</available>';
		$buffer .= '</server_prv_ip>';
	}

	if ( $fServer_pub_ip != NULL && $fServer_prv_ip == NULL && $fType == "server" ){
		$ip_available = "";
		$ip_available = check_server_pubip_available($fServer_pub_ip,$fServer_id);
		$buffer .= '<server_pub_ip>';
		$buffer .= '<available>'. $ip_available .'</available>';
		$buffer .= '</server_pub_ip>';
	}

	if ( $fServer_pub_ip != NULL && $fServer_prv_ip != NULL && $fType == "server" ){
		$ip_available = "";
		$ip_available = check_server_pubip_available($fServer_pub_ip, $fServer_prv_ip,$fServer_id);	
		$buffer .= '<server_pub_ip>';
		$buffer .= '<available>'. $ip_available .'</available>';
		$buffer .= '</server_pub_ip>';
	}



 }


$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 