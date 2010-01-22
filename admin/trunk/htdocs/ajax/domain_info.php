<?php
//
// File: hosting/domain_info.php
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
// fType
// fAccount
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$buffer = '<?xml version="1.0"?>'; 
$buffer .= "<resultat>";



if ($_SERVER['REQUEST_METHOD'] == "GET")
	{

	$fDomain = get_get("fDomain");
	$fType = get_get("fType");
	$fAccount = get_get("fAccount");

	if ( $fDomain != NULL && $fType == "db_info" ){
		$dbuser_available = check_dbuser_domain_available($fDomain);
		$db_available = check_db_domain_available($fDomain);
		$buffer .= '<dbuser>';
		$buffer .= '<available>'. $dbuser_available .'</available>';
		$buffer .= '</dbuser>';
		$buffer .= '<db>';
		$buffer .= '<available>'. $db_available .'</available>';
		$buffer .= '</db>';
	}

	if ( $fDomain != NULL && $fType == "virtual_list" ){
		$list_virtual_domain = get_website_list($fDomain);
		if ( (is_array ($list_virtual_domain) and sizeof ($list_virtual_domain) > 0)) {
			for ($j = 0; $j < sizeof ($list_virtual_domain); $j++){
				if ( $list_virtual_domain[$j]['vhost'] == "" ){
					$list_virtual_domain[$j]['vhost'] = " ";
				}
				$buffer .= '<virtual>';
				$buffer .= '<id>'. $list_virtual_domain[$j]['id'] .'</id>';
				$buffer .= '<name>'. $list_virtual_domain[$j]['vhost'] .'</name>';
				$buffer .= '</virtual>';
			}
		}
	}

	if ( $fDomain != NULL && $fType == "account_exist" && $fAccount != NULL ){
		$ftp_account_exist = check_ftp_account_exist($fAccount, $fDomain);
				$buffer .= '<virtual>';
				$buffer .= '<exist>'. $ftp_account_exist .'</exist>';
				$buffer .= '</virtual>';
		}

	if ( $fDomain != NULL && $fType == "db_check_available"  ){
		$db_available = check_db_domain_available($fDomain);
				$buffer .= '<db>';
				$buffer .= '<available>'. $db_available .'</available>';
				$buffer .= '</db>';
		}


	}

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain = get_post("fDomain");
	$fType = get_post("fType");
	$fAccount = get_post("fAccount");

	if ( $fDomain != NULL && $fType == "virtual_list" ){
		$list_virtual_domain = get_website_list($fDomain);
		if ( (is_array ($list_virtual_domain) and sizeof ($list_virtual_domain) > 0)) {
			for ($j = 0; $j < sizeof ($list_virtual_domain); $j++){
				if ( $list_virtual_domain[$j]['vhost'] == "" ){
					$list_virtual_domain[$j]['vhost'] = " ";
				}
				$buffer .= '<virtual>';
				$buffer .= '<id>'. $list_virtual_domain[$j]['id'] .'</id>';
				$buffer .= '<name>'. $list_virtual_domain[$j]['vhost'] .'</name>';
				$buffer .= '</virtual>';
			}
		}
	}

	if ( $fDomain != NULL && $fType == "account_exist" && $fAccount != NULL ){
		$ftp_account_exist = check_ftp_account_exist($fAccount, $fDomain);
				$buffer .= '<virtual>';
				$buffer .= '<exist>'. $ftp_account_exist .'</exist>';
				$buffer .= '</virtual>';
	}

	if ( $fDomain != NULL && $fType == "db_check_available"  ){
		$db_available = check_db_domain_available($fDomain);
				$buffer .= '<db>';
				$buffer .= '<available>'. $db_available .'</available>';
				$buffer .= '</db>';
		}

	if ( $fDomain != NULL && $fType == "db_info" ){
		$dbuser_available = check_dbuser_domain_available($fDomain);
		$db_available = check_db_domain_available($fDomain);
		$buffer .= '<dbuser>';
		$buffer .= '<available>'. $dbuser_available .'</available>';
		$buffer .= '</dbuser>';
		$buffer .= '<db>';
		$buffer .= '<available>'. $db_available .'</available>';
		$buffer .= '</db>';
	}


 }


$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 