<?php
//
// File: manage_domain.php
//
// Template File: 
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
// 
// 
// 
//
require ("../../variables.inc.php");


// Required Libs
require ("../../config.inc.php");
require ("../../lib/functions.inc.php");
require_once ("MDB2.php");
require_once ("../../lib/db.class.php");
require_once ("../../lib/user.class.php");
require_once ("../../lib/ova.class.php");
//require_once ("../../lib/policyd.class.php");
require_once ("../../lib/domain.class.php");
require_once ("../../lib/server.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_quota_status();


include ("../../languages/" . check_language() . ".lang");


$json_data_array = array();
$json_array = array();



if ( $_SERVER['REQUEST_METHOD'] == "GET" ){

//	$fAction = get_get('action');
//  $fDomain = get_get('domain');
//  if ($fDomain == NULL){}
  // $fMethod = get_get('method');
  // $fDir = get_get('sortdir');
  // $fResults = get_get('results');
  // $fSort = get_get('sort');
  // $fStartIndex =  get_get('startIndex');
  // $fDomainAlias = get_get('domain_alias');

  if ( $fStartIndex == NULL ) { $fStartIndex = 0;}
  if ( $fResults == NULL ) { $fResults = 10;}


  $json_array['startIndex'] = intval($fStartIndex);
  $json_array['sort'] = $fSort;
  $json_array['dir'] = $fDir;
  $json_array['pageSize'] = intval($fResults);




}


if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain = get_post("domain");
	$fActive = get_post("active");
	$fAction = get_post("action");
  $fMethod = get_post('method');
  $fDir = get_post('sortdir');
  $fResults = get_post('results');
  $fSort = get_post('sort');
  $fStartIndex =  get_post('startIndex');
  $fDomainAlias = get_post('domain_alias');
  $fAction_target = get_post('action_target');

	$fNewvalue = (get_post("newValue") == $PALANG['NO'] ) ? 0 : 1;

  if ( $fStartIndex == NULL ) { $fStartIndex = 0;}
  if ( $fResults == NULL ) { $fResults = 10;}

  $json_array['replyCode'] = 501;


  switch ($fAction) {

    case "delete" :
    // $policy_info = new POLICYD($ovadb);
    // $domain_info->delete_domain();
    // $json_array['replyCode'] = $domain_info->sql_result['return_code'] + 1;
    // debug_info("POLICYD : ".$domain_info->policy_message);
    // break;

      if ( $fAction_target == "domain_alias" ){

        $json_array['replyText'] = "Delete domain alias : $fDomain";
      }
      else{
        $json_array['replyText'] = "Delete domain : $fDomain";
      }
      
      $json_array['replyCode'] = 200;
      break;

    case "list" :

      $json_array['startIndex'] = intval($fStartIndex);
      $json_array['sort'] = $fSort;
      $json_array['dir'] = $fDir;
      $json_array['pageSize'] = intval($fResults);


      if ( $fDomain != NULL) {
        $domain_info->fetch_by_domainname($fDomain);
      }

      if ( $fDomainAlias == 1 ){
        $user_info->fetch_domains_aliases($fDomain, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir);
        $json_array['recordsReturned'] = $user_info->total_managed_domain_alias;
        $json_array['totalRecords'] = $user_info->data_managed['domains_alias'];
        
        for ( $i=0; $i < $user_info->total_managed_domain_alias; $i++){
          $json_data_array[] = array(
            'domain_alias' => $user_info->data_managed_domain_alias[$i]['dalias'],
            'domain' => $user_info->data_managed_domain_alias[$i]['domain'],
            'modified' => $user_info->data_managed_domain_alias[$i]['modified'],
            'active' => $user_info->data_managed_domain_alias[$i]['active'],
            'delete' => 'delete'
          );
        }

        $json_array['replyCode'] = 200;

        
      }
      else{
        $user_info->fetch_domains($fDomain, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir);
        $json_array['recordsReturned'] = $user_info->total_managed_domain;
        $json_array['totalRecords'] = $user_info->data_managed['domains']; //"20";#$user_info->data_managed['domains'];  
        
        for ( $i=0; $i < $user_info->total_managed_domain; $i++){

          $domain_info->fetch_by_domainname($user_info->data_managed_domain[$i]['domain']);

          $json_data_array[] = array(
            'domain' => $domain_info->data_domain['domain'],
            'description' => $domain_info->data_domain['description'], 
            'aliases' => $domain_info->used_quota['aliases'],
            'quota_aliases' => ($domain_info->quota['aliases'] == "-1" ) ? "&infin;" : $domain_info->quota['aliases'],
            'aliases_w_quota' => ($domain_info->data_domain['backupmx'] == 1) ? "" : $domain_info->used_quota['aliases']."/".(($domain_info->quota['aliases'] == "-1" ) ? "&infin;" : $domain_info->quota['aliases']),
            'mails' => $domain_info->used_quota['mailboxes'],
            'quota_mails' => ($domain_info->quota['mailboxes'] == "-1" ) ? "&infin;" : $domain_info->quota['mailboxes'],
            'mails_w_quota' => ($domain_info->data_domain['backupmx'] == 1) ? "" : $domain_info->used_quota['mailboxes']."/".(($domain_info->quota['mailboxes'] == "-1" ) ? "&infin;" : $domain_info->quota['mailboxes']),
            'backupmx' => ($domain_info->data_domain['backupmx'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
            'ftp' => $domain_info->used_quota['ftpaccount'],
            'quota_ftp' => ($domain_info->quota['ftp_account'] == "-1" ) ? "&infin;" : $domain_info->quota['ftp_account'],
            'ftp_w_quota' => $domain_info->used_quota['ftpaccount']."/".(($domain_info->quota['ftp_account'] == "-1" ) ? "&infin;" : $domain_info->quota['ftp_account']),
            'web' => $domain_info->used_quota['http'],
            'quota_web' => ($domain_info->quota['whost_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['whost_quota'],
            'web_w_quota' => $domain_info->used_quota['http']."/".(($domain_info->quota['whost_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['whost_quota']),
            'databases' => $domain_info->used_quota['db'],
            'quota_databases' => ($domain_info->quota['db_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['db_quota'],
            'databases_w_quota' => $domain_info->used_quota['db']."/".(($domain_info->quota['db_quota'] == "-1" ) ? "&infin;" : $domain_info->quota['db_quota']),
            'state' => $domain_info->data_domain['status'],
            'active'  => ($domain_info->data_domain['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
            'paid'  => ($domain_info->data_domain['paid'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
            'modified' => $domain_info->data_domain['modified'],
            'delete' => "delete",
            'edit' => "<a href=\"edit-domain.php?domain=".urlencode($domain_info->data_domain['domain'])."\">".$PALANG['edit']."</a>"
          );
        }
        $json_array['replyCode'] = 200;
        
      }


      break;

  }



//	$server_info = new SERVER($ovadb);

	

	if ( $json_array['replyCode'] > 500 ){
		$json_array['replyText'] = 'Error occured : ';
	}
  //  $json_array['replyText'] = 'Data Follows';
  // }
  // else{

	$json_array['log'] = "";

}


header('Content-type: application/x-json');
$json_array['records'] = $json_data_array;
echo json_encode($json_array);

?>
