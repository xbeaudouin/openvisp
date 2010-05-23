<?php
//
// File: ajax/domain/domain_list.php
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
require_once ("../../lib/mail.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$domain_info = new DOMAIN($ovadb);


$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fMethod = get_post('method');
	$fDir = get_post('dir');
	$fResults = get_post('results');
	$fSort = get_post('sort');
	$fStartIndex =  get_post('startIndex');

	$user_info->fetch_quota_status();
	$user_info->fetch_domains(NULL, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir);


	$json_array['totalRecords'] = intval($user_info->data_managed['domain']);
	$json_array['startIndex'] = intval($fStartIndex);
	$json_array['recordsReturned'] = sizeof($domain_info->list_mailboxes);
	$json_array['sort'] = $fSort;
	$json_array['dir'] = $fDir;
	$json_array['pageSize'] = intval($fResults);
		

	for ( $i=0; $i < sizeof($user_info->data_managed_domain); $i++){

		$domain_info->fetch_by_domainname($user_info->data_managed_domain[$i]['domain']);
		$mail_alias = 

		$json_data_array[] = array(
															 'domain' => $domain_info->data_domain['domain'],
															 'description' => $domain_info->data_domain['description'], 
															 'aliases' => $domain_info->used_quota['mail_alias'],
															 'quota_aliases' => ($domain_info->quota['mail_aliases'] == "-1" ) ? "&infin;" : $domain_info->quota['mail_aliases'],
															 'mails' => $domain_info->used_quota['mailbox'],
															 'quota_mails' => ($domain_info->quota['mailboxes'] == "-1" ) ? "&infin;" : $domain_info->quota['mailboxes'],
															 'backupmx' => ($domain_info->data_domain['backupmx'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
															 'ftp' => $domain_info->used_quota['ftpaccount'],
															 'quota_ftp' => ($domain_info->quota['ftp_account'] == "-1" ) ? "&infin;" : $domain_info->quota['ftp_account'],
															 'web' => $domain_info->used_quota['http'],
															 'quota_web' => ($domain_info->quota['whost_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['whost_quota'],
															 'databases' => $domain_info->used_quota['http'],
															 'quota_databases' => ($domain_info->quota['db_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['db_quota'],
															 'state' => $domain_info->data_domain['status'],
															 'active'  => ($domain_info->data_domain['paid'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
															 'modified' => $domain_info->data_domain['modified'],
															 'delete' => "delete",
															 'edit' => "<a href=\"edit-domain.php?domain=".urlencode($domain_info->data_domain['domain']).">".$PALANG['edit']."</a>"
															 );
	}


	
  header('Content-type: application/x-json');
  $json_array['records'] = $json_data_array;
  echo json_encode($json_array);


}


?>