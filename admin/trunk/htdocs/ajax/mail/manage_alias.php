<?php
//
// File: ajax/mail/manage_alias.php
//
// Form POST \ GET Variables:
//
// domainName
// alias
// goto
// modified
// amavis
// active
// delete
// edit

require ("../../variables.inc.php");
require ("../../config.inc.php");
require ("../../lib/functions.inc.php");
include ("../../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../../lib/db.class.php");
require_once ("../../lib/user.class.php");
require_once ("../../lib/domain.class.php");
require_once ("../../lib/mail.class.php");
require_once ("../../lib/server.class.php");
require_once ("../../lib/policyd.class.php");
require_once ("../../lib/ova.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$domain_info = new DOMAIN($ovadb);

$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain_name = get_post("domainName");
	$fAlias = get_post("address");
	$fActive = get_post("active");
	$fAction = get_post("action");
	$fNewvalue = (get_post("newValue") == $PALANG['NO'] ) ? 0 : 1;

	$domain_info->fetch_by_domainname($fDomain_name);
	$domain_info->fetch_policy_id();
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$ova_info = new OVA($ovadb);
	$server_info = new SERVER($ovadb);


	$alias_info = new MAIL($ovadb);
	$alias_info->fetch_alias_info($fAlias);


	switch ($fAction) {

	case "mod_status" :
		$alias_info->alias_change_active_status($fNewvalue);
		$json_array['replyCode'] = $alias_info->sql_result['return_code'] + 1;
		break;

	case "delete" :
		$return = $alias_info->alias_delete();
		$json_array['replyCode'] = $return['status_code'];
		$json_array['replyText'] = $return['message'];
		//$json_array['replyCode'] = $alias_info->sql_result['return_code'] + 1;
		break;

	case "mod_antispam" :
		$alias_info->antispam_en_disable($domain_info->data['policy_id']);
		$json_array['replyCode'] = $alias_info->sql_result['return_code'] + 1;
		break;


	default:
		$alias_info->alias_en_disable();
		$json_array['replyCode'] = $alias_info->sql_result['return_code'];
	}

	if ( $json_array['replyCode'] < 500 ){
		$json_array['replyText'] = 'Data Follows';
	}
	else{
		$json_array['replyText'] = 'Error occured : ';
	}


	$alias_info->fetch_alias_info($fAlias);
	if ( isset($alias_info->sql_result['sql_log']) ){
		$json_array['log'] = $alias_info->sql_result['sql_log'];
	}

	$alias_info->data_alias['active'] = ($alias_info->data_alias['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'];
	$alias_info->data_alias['policy_id'] = ($alias_info->data_alias['policy_id'] > 1) ? $PALANG['YES'] : $PALANG['NO'];

  header('Content-type: application/x-json');
  $json_array['records'] = $alias_info->data_alias;
  echo json_encode($json_array);


}

?>