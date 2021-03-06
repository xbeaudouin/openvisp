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

$fDomain_name = get_get("domain_name");

$json_data_array = array();
$json_array = array();



if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

  $fMethod = get_post('method');
	$fDir = get_post('dir');
	$fResults = get_post('results');
	$fSort = get_post('sort');
	$fStartIndex =  get_post('startIndex');
  $fDomain_name = get_post('domainName');


	$user_info->fetch_active_domains_with_mail($fDomain_name);
	$total_domain_with_mail = $user_info->total_managed_active_domain_with_mail;
  if ( $fStartIndex == NULL ) { $fStartIndex = 0;}
  if ( $fResults == NULL ) { $fResults = 10;}
  
	$user_info->fetch_active_domains_with_mail($fDomain_name, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir);
  
  if ( (is_array($user_info->data_managed_active_domain_with_mail)) and sizeof ($user_info->data_managed_active_domain_with_mail > 0))
  {
    
    $json_array['totalRecords'] = intval($total_domain_with_mail);
    $json_array['startIndex'] = intval($fStartIndex);
    $json_array['recordsReturned'] = sizeof($user_info->data_managed_active_domain_with_mail);
    $json_array['sort'] = $fSort;
    $json_array['dir'] = $fDir;
    $json_array['pageSize'] = intval($fResults);

    
    for ( $i=0; $i < sizeof($user_info->data_managed_active_domain_with_mail); $i++)
    {
      $domain_info->fetch_by_domainid($user_info->data_managed_active_domain_with_mail[$i]['id']);
      
      if ( $domain_info->quota['aliases'] != 0 || $domain_info->quota['mailboxes'] != 0 ){
        $domain_info->total_diskspace_used_mailboxes();
        
        if ( $CONF['quota'] == 'YES') {

					// 'domain' => '<a href=".'</a>',          
          $json_data_array[] = array(
																		 'domain' => '<a href="/mail/overview.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'].'">'.$user_info->data_managed_active_domain_with_mail[$i]['domain'].'</a>',
																		 'domain_url' => '/mail/overview.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'],
																		 'aliases' => intval($domain_info->used_quota['aliases']),
																		 'mailboxes' => intval($domain_info->used_quota['mailboxes']),
																		 'maxquota' => intval($domain_info->quota['maxquota']),
																		 'quota_aliases' => intval($domain_info->quota['aliases']),
																		 'quota_mailboxes' => intval($domain_info->quota['mailboxes']),
																		 'diskspace_mailboxes' => intval($domain_info->data['total_diskspace_used_mailboxes']),
																		 'modified' => $user_info->data_managed_active_domain_with_mail[$i]['modified'],
																		 'security' => '<a href="edit-active-domain-policy.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'].'">'.$PALANG['pOverview_get_security_edit'].'</a>',
																		 'security_url' => 'edit-active-domain-policy.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'],
																		 );
        }
        else{
          $json_data_array[] = array(
																		 'domain' => $user_info->data_managed_active_domain_with_mail[$i]['domain'],
																		 'domain_url' => '/mail/overview.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'],
																		 'aliases' => $domain_info->used_quota['mail_alias'],
																		 'mailboxes' => $domain_info->used_quota['mailbox'],
																		 'security' => $PALANG['pOverview_get_security_edit'],
																		 'security_url' => 'edit-active-domain-policy.php?domain='.$user_info->data_managed_active_domain_with_mail[$i]['domain'],
																		 );
        }

      }
    }
		
	}
	
	
	$json_array['records'] = $json_data_array;
  header('Content-type: application/x-json');
  $json_array['records'] = $json_data_array;
  echo json_encode($json_array);
  
//  $xml = new SimpleXMLElement('<root/>');
//  array_walk_recursive($json_data_array, array ($xml, 'addChild'));
//  print $xml->asXML();

/*   header('Content-type: application/xml'); */
/*   echo '<?xml version="1.0"?>'; */
  
/*   echo '<ResultSet totalResultsAvailable="'.$json_array['totalRecords'].'" totalResultsReturned="'.$json_array['recordsReturned'].'" firstResultPosition="'.$json_array['startIndex'].'">'."\n"; */
/*   echo ova_array_to_xml($json_data_array,"0","Result"); */
/*   echo "\n</ResultSet>"; */
  

//echo assocToXML($json_array,"0","ResultSet");
//echo assocToXML($json_data_array,"0","Domains");
	
}


?> 
