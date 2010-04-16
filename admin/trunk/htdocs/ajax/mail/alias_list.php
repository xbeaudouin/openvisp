<?php
//
// File: ajax/mail/alias_list.php
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
//

require ("../../variables.inc.php");
require ("../../config.inc.php");
require ("../../lib/functions.inc.php");
include ("../../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../../lib/db.class.php");
require_once ("../../lib/user.class.php");
require_once ("../../lib/domain.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_active_domains();
$domain_info = new DOMAIN($ovadb);


$buffer = '<?xml version="1.0"?>'; 
$buffer .= "<resultat>";

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){


	$fDomain_name = get_post('domain_name');
	$domain_info->fetch_by_domainname($fDomain_name);
	$domain_info->fetch_mail_aliases();


	if ((is_array ($domain_info->list_mail_aliases) and sizeof ($domain_info->list_mail_aliases) > 0))
		{
			for ( $i=0; $i < sizeof($domain_info->list_mail_aliases); $i++)
				{
					$buffer .= "<aliases>\n";
					$buffer .= "<info>".sizeof($domain_info->list_mail_aliases)."</info>\n";
					$buffer .= "<address>".$domain_info->list_mail_aliases[$i]['address']."</address>\n";

					$buffer .= "<goto>".$domain_info->list_mail_aliases[$i]['goto']."</goto>\n";
					$buffer .= "<modified>".$domain_info->list_mail_aliases[$i]['modified']."</modified>\n";
					$buffer .= "</aliases>\n";

				}
		}

}



$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 
