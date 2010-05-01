<?php
//
// File: ajax/mail/domain_mail_overview.php
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
$domain_info = new DOMAIN($ovadb);

$fDomain_name = get_post("domain_name");



/* $buffer = '<?xml version="1.0"?>';  */
/* $buffer .= "<resultat>\n"; */



$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

  $fMethod = get_post('method');
	$fDir = get_post('dir');
	$fResults = get_post('results');
	$fSort = get_post('sort');
	$fStartIndex =  get_post('startIndex');

	$user_info->fetch_active_domains_with_mail($fDomain_name);
	$total_domain_with_mail = $user_info->total_managed_active_domain_with_mail;

	$user_info->fetch_active_domains_with_mail($fDomain_name, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir);
  
  if ( (is_array($user_info->data_managed_active_domain_with_mail)) and sizeof ($user_info->data_managed_active_domain_with_mail > 0))
  {
    
/*     $buffer .= "<totalRecords>".$total_domain_with_mail."</totalRecords>\n"; */
/*     $buffer .= "<startIndex>".$fStartIndex."</startIndex>\n"; */
/*     $buffer .= "<dir>".$fDir."</dir>\n"; */
/*     $buffer .= "<pageSize>".$fResults."</pageSize>\n"; */
    
    $json_array['totalRecords'] = $total_domain_with_mail;
    $json_array['startIndex'] = $fStartIndex;
    $json_array['recordsReturned'] = sizeof($user_info->data_managed_active_domain_with_mail);
    $json_array['sort'] = $fSort;
    $json_array['dir'] = $fDir;
    $json_array['pageSize'] = $fResults;

		debug_info( "JS : ".$json_array['totalRecords'] );
    
    // method=json&sort=id&dir=asc&startIndex=0&results=25\
    // "recordsReturned":25,"totalRecords":1397,"startIndex":0,"sort":"id","dir":"asc","pageSize":25,"records"
    
    for ( $i=0; $i < sizeof($user_info->data_managed_active_domain_with_mail); $i++)
    {
      $domain_info->fetch_by_domainid($user_info->data_managed_active_domain_with_mail[$i]['id']);
      
      if ( $domain_info->quota['mail_aliases'] != 0 && $domain_info->quota['mailboxes'] != 0 ){
        $domain_info->total_diskspace_used_mailboxes();
        
        if ( $CONF['quota'] == 'YES') {
          
          $json_data_array[] = array(
            'domain' => $user_info->data_managed_active_domain_with_mail[$i]['domain'],
            'aliases' => $domain_info->used_quota['mail_alias'],
            'mailboxes' => $domain_info->used_quota['mailbox'],
            'maxquota' => $domain_info->quota['maxquota'],
            'quota_aliases' => $domain_info->quota['mail_aliases'],
            'quota_mailboxes' => $domain_info->quota['mailboxes'],
            'diskspace_mailboxes' => $domain_info->data['total_diskspace_used_mailboxes'],                                                         
            'modified' => $user_info->data_managed_active_domain_with_mail[$i]['modified']
            );
        }
        else{
          $json_data_array[] = array(
            'domain' => $user_info->data_managed_active_domain_with_mail[$i]['domain'],
            'aliases' => $domain_info->used_quota['mail_alias'],
            'mailboxes' => $domain_info->used_quota['mailbox']
            );
        }
        
/*         $buffer .= "<domain>\n"; */
/*         $buffer .= "<info>".sizeof($user_info->data_managed_active_domain_with_mail)."</info>\n"; */
/*         $buffer .= "<name>".$user_info->data_managed_active_domain_with_mail[$i]['domain']."</name>\n"; */
/*         $buffer .= "<aliases>".$domain_info->used_quota['mail_alias']."</aliases>\n"; */
/*         $buffer .= "<mailboxes>".$domain_info->used_quota['mailbox']."</mailboxes>\n"; */
        
        
/*         if ($CONF['quota'] == 'YES') { */
/*           $buffer .= "<maxquota>"; */
/*           switch($domain_info->quota['maxquota']) { */
/*           case "-1" : $buffer .= "&#8734;"; break; */
/*             default   : $buffer .= $domain_info->quota['maxquota']; break; */
/*           } */
/*           $buffer .= "</maxquota>\n"; */
          
/*           $buffer .= "<quota_aliases>"; */
/*           switch($domain_info->quota['mail_aliases']) { */
/*           case "-1" : $buffer .= "&#8734;"; break; */
/*             default   : $buffer .= $domain_info->quota['mail_aliases']; break; */
/*           } */
/*           $buffer .= "</quota_aliases>\n"; */
          
/*           $buffer .= "<quota_mailboxes>"; */
/*           switch($domain_info->quota['mailboxes']) { */
/*           case "-1" : $buffer .= "&#8734;"; break; */
/*             default   : $buffer .= $domain_info->quota['mailboxes']; break; */
/*           } */
/*           $buffer .= "</quota_mailboxes>\n"; */
          
/*         } */
        
/*         $buffer .= "<diskspace_mailboxes>".number_format($domain_info->data['total_diskspace_used_mailboxes'],0, ',', ' ')."</diskspace_mailboxes>\n"; */
/*         $buffer .= "<modified>".$user_info->data_managed_active_domain_with_mail[$i]['modified']."</modified>\n"; */
/*         $buffer .= "</domain>\n"; */
      }
    }
		
	}
	
	
  header('Content-type: application/x-json');
  $json_array['records'] = $json_data_array;
  echo json_encode($json_array);
	
	
}






?> 
