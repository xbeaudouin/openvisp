<?php
//
// File: ajax/mail/manage_maibox.php
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
	$fUsername = get_post("username");
	$fActive = get_post("active");
	$fAction = get_post("action");
	$fNewvalue = (get_post("newValue") == $PALANG['NO'] ) ? 0 : 1;

	$domain_info->fetch_by_domainname($fDomain_name);
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$mailbox_info = new MAIL($ovadb);
	$mailbox_info->fetch_mailbox_info($fUsername);


	switch ($fAction) {

	case "mod_status" :
		$mailbox_info->mailbox_change_active_status($fNewvalue);
		$json_array['replyCode'] = $mailbox_info->sql_result['return_code'] + 1;
		break;

	case "delete" :
		$mailbox_info->mailbox_delete();
		$json_array['replyCode'] = $mailbox_info->sql_result['return_code'] + 1;
		break;

	case "mod_paid" :
		$mailbox_info->mailbox_paid();
		$json_array['replyCode'] = $mailbox_info->sql_result['return_code'] + 1;
		break;

	case "mod_antispam" :
		$mailbox_info->antispam_en_disable($domain_info->data['policy_id']);
		$json_array['replyCode'] = $mailbox_info->sql_result['return_code'] + 1;
		break;

	default:
		$mailbox_info->mailbox_en_disable();
		$json_array['replyCode'] = $mailbox_info->sql_result['return_code'];
	}

	if ( $json_array['replyCode'] < 500 ){
		$json_array['replyText'] = 'Data Follows';
	}
	else{
		$json_array['replyText'] = 'Error occured : ';
	}


	$mailbox_info->fetch_mailbox_info($fUsername);
	$json_array['log'] = $mailbox_info->sql_result['sql_log'];


	$mailbox_info->data_mailbox['paid'] = ($mailbox_info->data_mailbox['paid'] == 0) ? $PALANG['NO'] : $PALANG['YES'];
	$mailbox_info->data_mailbox['active'] = ($mailbox_info->data_mailbox['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'];
	$mailbox_info->data_mailbox['policy_id'] = ($mailbox_info->data_mailbox['policy_id'] > 1) ? $PALANG['YES'] : $PALANG['NO'];

  header('Content-type: application/x-json');
  $json_array['records'] = $mailbox_info->data_mailbox;
  echo json_encode($json_array);


}

?>