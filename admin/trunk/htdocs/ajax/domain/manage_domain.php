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
require_once ("../../lib/policyd.class.php");
require_once ("../../lib/server.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);

include ("../../languages/" . check_language() . ".lang");

require_once ("../../lib/domain.class.php");

$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fDomain_name = get_post("domain");
	$fUsername = get_post("username");
	$fActive = get_post("active");
	$fAction = get_post("action");
	$fNewvalue = (get_post("newValue") == $PALANG['NO'] ) ? 0 : 1;

	$domain_info = new DOMAIN($ovadb);
	$domain_info->fetch_by_domainname($fDomain_name);
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$server_info = new SERVER($ovadb);

	$json_array['replyCode'] = 501;

	switch ($fAction) {

	case "delete" :
		$policy_info = new POLICYD($ovadb);
		$domain_info->delete_domain();
		$json_array['replyCode'] = $domain_info->sql_result['return_code'] + 1;
		debug_info("POLICYD : ".$domain_info->policy_message);
		break;

	}

	if ( $json_array['replyCode'] < 500 ){
		$json_array['replyText'] = 'Data Follows';
	}
	else{
		$json_array['replyText'] = 'Error occured : ';
	}


	$json_array['log'] = "";


  header('Content-type: application/x-json');
  $json_array['records'] = "";
  echo json_encode($json_array);


}

?>
