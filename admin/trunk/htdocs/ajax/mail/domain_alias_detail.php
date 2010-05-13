<?php
//
// File: ajax/mail/domain_mail_overview.php
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
$domain_info = new DOMAIN($ovadb);

$fDomain_name = $_GET["domainName"];

$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	if ( strlen($fDomain_name) == 0 ){
		$fDomain_name = get_post('domainName');
	}


  $fMethod = get_post('method');
	$fDir = get_post('dir');
	$fResults = get_post('results');
	$fSort = get_post('sort');
	$fStartIndex =  get_post('startIndex');


	$domain_info->fetch_by_domainname($fDomain_name);
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$domain_info->fetch_mail_aliases(NULL, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir );

 
  if ( (is_array($domain_info->list_mail_aliases)) and sizeof ($domain_info->list_mail_aliases > 0))
  {
    
    $json_array['totalRecords'] = intval($domain_info->used_quota['mail_alias']);
    $json_array['startIndex'] = intval($fStartIndex);
    $json_array['recordsReturned'] = sizeof($domain_info->list_mail_aliases);
    $json_array['sort'] = intval($fSort);
    $json_array['dir'] = intval($fDir);
    $json_array['pageSize'] = intval($fResults);
    $json_array['domainName'] = $fDomain_name;

		//		debug_info( "JS : ".$json_array['totalRecords'] );
        
    for ( $i=0; $i < sizeof($domain_info->list_mail_aliases); $i++)
    {
			
          
			$json_data_array[] = array(
																 'alias' => $domain_info->list_mail_aliases[$i]['address'],
																 'goto' => $domain_info->list_mail_aliases[$i]['goto'], 
																 'modified' => $domain_info->list_mail_aliases[$i]['modified'],
																 'active' => ($domain_info->list_mail_aliases[$i]['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
																 'amavis' => ($domain_info->list_mail_aliases[$i]['policy_id'] == 1) ? $PALANG['NO'] : $PALANG['YES'],
																 'delete' => "delete",
																 'edit' => "<a href=\"edit-alias.php?address=".urlencode($domain_info->list_mail_aliases[$i]['address'])."&domain=$fDomain_name>".$PALANG['edit']."</a>"
																 );
			//																 'edit' => "mail/edit-alias.php?address".urlencode($domain_info->list_mail_aliases[$i]['address'])."&domain=$fDomain"
			//																 'active' => $domain_info->list_mail_aliases[$i]['active'],
    }
		
	}
	
	
  header('Content-type: application/x-json');
  $json_array['records'] = $json_data_array;
  echo json_encode($json_array);
	
	
}


?> 
