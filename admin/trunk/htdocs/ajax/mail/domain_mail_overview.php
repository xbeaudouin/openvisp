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
$user_info->fetch_active_domains($fDomain_name);


$buffer = '<?xml version="1.0"?>'; 
$buffer .= "<resultat>";

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	if ( (is_array($user_info->data_managed_active_domain)) and sizeof ($user_info->data_managed_active_domain > 0))
		{
			for ( $i=0; $i < sizeof($user_info->data_managed_active_domain); $i++)
				{
				  $domain_info->fetch_by_domainid($user_info->data_managed_active_domain[$i]['id']);
				  
				  if ( $domain_info->quota['mail_aliases'] != 0 && $domain_info->quota['mailboxes'] != 0 ){
				    $domain_info->total_diskspace_used_mailboxes();
				    
				    $buffer .= "<domain>\n";
				    $buffer .= "<info>".sizeof($user_info->data_managed_active_domain)."</info>\n";
				    $buffer .= "<name>".$user_info->data_managed_active_domain[$i]['domain']."</name>\n";
				    $buffer .= "<aliases>".$domain_info->used_quota['mail_alias']."</aliases>\n";
				    $buffer .= "<mailboxes>".$domain_info->used_quota['mailbox']."</mailboxes>\n";
				    
				    
				    if ($CONF['quota'] == 'YES') {
				      $buffer .= "<maxquota>";
				      switch($domain_info->quota['maxquota']) {
				      case "-1" : $buffer .= "&#8734;"; break;
				        default   : $buffer .= $domain_info->quota['maxquota']; break;
				      }
				      $buffer .= "</maxquota>\n";
				      
				      $buffer .= "<quota_aliases>";
				      switch($domain_info->quota['mail_aliases']) {
				      case "-1" : $buffer .= "&#8734;"; break;
				        default   : $buffer .= $domain_info->quota['mail_aliases']; break;
				      }
				      $buffer .= "</quota_aliases>\n";
				      
				      $buffer .= "<quota_mailboxes>";
				      switch($domain_info->quota['mailboxes']) {
				      case "-1" : $buffer .= "&#8734;"; break;
				        default   : $buffer .= $domain_info->quota['mailboxes']; break;
				      }
				      $buffer .= "</quota_mailboxes>\n";
				      
				    }
				    
				    $buffer .= "<diskspace_mailboxes>".number_format($domain_info->data['total_diskspace_used_mailboxes'],0, ',', ' ')."</diskspace_mailboxes>\n";                                                          
				    $buffer .= "<modified>".$user_info->data_managed_active_domain[$i]['modified']."</modified>\n";
				    $buffer .= "</domain>\n";
					}
				}
		}

}



$buffer .= "</resultat>"; 

header('Content-Type: text/xml'); 
print $buffer; 
?> 
