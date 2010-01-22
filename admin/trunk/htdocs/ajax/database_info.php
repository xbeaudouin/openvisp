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

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){


	$fDomain_name = get_post('domain_name');
	$fDb_name = get_post('db_name');
	$fComment = get_post('comment');

	$domain_alias_list = list_array_domains_alias('id');

	$database_list = list_databases($fDomain_name, $fDb_name, $fComment);

	if ((is_array ($database_list) and sizeof ($database_list) > 0))
		{
			for ( $i=0; $i < sizeof($database_list); $i++)
				{
					$buffer .= "<database>\n";
					$buffer .= "<info>".sizeof($database_list)."</info>\n";
					if ( $database_list[$i]['dalias_id'] > 0 )
						{ $buffer .= "<domain>".$domain_alias_list[$database_list[$i]['dalias_id']]['dalias']."</domain>\n";	}
					else
						{ $buffer .= "<domain>".$database_list[$i]['domain']."</domain>\n";	}

					$buffer .= "<db_id>".$database_list[$i]['db_id']."</db_id>\n";
					$buffer .= "<db_name>".$database_list[$i]['dbname']."</db_name>\n";
					$buffer .= "<description>".$database_list[$i]['description']."</description>\n";
					$buffer .= "<db_type>".$database_list[$i]['dbtype_name']."</db_type>\n";
					$buffer .= "<server_name>".$database_list[$i]['server_name']."</server_name>\n";
					$buffer .= "<server_id>".$database_list[$i]['server_id']."</server_id>\n";
					$buffer .= "<server_ip_id>".$database_list[$i]['server_ip_id']."</server_ip_id>\n";
					$buffer .= "<server_port>".$database_list[$i]['port']."</server_port>\n";
					$buffer .= "</database>\n";

				}
		}

}



$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 