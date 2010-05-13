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

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$domain_info = new DOMAIN($ovadb);

$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain_name = get_post("domainName");
	$fAlias = get_post("alias");
	$fActive = get_post("active");
	$fAction = get_post("action");
	$fNewvalue = (get_post("newValue") == $PALANG['NO'] ) ? 0 : 1;

	$domain_info->fetch_by_domainname($fDomain_name);
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$alias_info = new MAIL($ovadb);
	$alias_info->fetch_alias_info($fAlias);


	switch ($fAction) {

	case "mod_status" :
		$alias_info->change_active_status($fNewvalue);
		$json_array['replyCode'] = $alias_info->sql_result['return_code'] + 1;
		break;

	case "delete" :
		$alias_info->delete();
		$json_array['replyCode'] = $alias_info->sql_result['return_code'] + 1;
		break;


	default:
		$alias_info->en_disable();
		$json_array['replyCode'] = $alias_info->sql_result['return_code'];
	}

	if ( $json_array['replyCode'] < 500 ){
		$json_array['replyText'] = 'Data Follows';
	}
	else{
		$json_array['replyText'] = 'Error occured : ';
	}


	$alias_info->fetch_alias_info($fAlias);
	$json_array['log'] = $alias_info->sql_result['sql_log'];



	//	print "OK";

	$alias_info->data_alias['active'] = ($alias_info->data_alias['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'];

  header('Content-type: application/x-json');
  $json_array['records'] = $alias_info->data_alias;
  echo json_encode($json_array);


}

?>