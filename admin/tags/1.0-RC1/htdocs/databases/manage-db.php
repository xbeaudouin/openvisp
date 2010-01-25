<?php
//
// File: add-databases.php
//
// Template File: add-databases.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/dbhosting.class.php");
require_once ("../lib/domain.class.php");



$SESSID_USERNAME = check_user_session();

$ovadb = new DB();
$userinfo = new USER($ovadb);
$userinfo->fetch_info($SESSID_USERNAME);


/* $account_information = get_account_info($SESSID_USERNAME); */
/* $account_quota = get_account_quota($account_information['id']); */
/* $account_rights = get_account_right($account_information['id']); */
/* $total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME)); */


if ($_SERVER['REQUEST_METHOD'] == "POST")
	{

		$db_type = get_post("db_type");
		$userinfo->check_access($db_type);

		$server_id = get_post("server_id");
		$db_id = get_post("db_id");
		$newvalue = get_post("newvalue");

		$db_info = new DBHOSTING($ovadb);
		$db_info->fetch_info_by_id($db_id);

		if ( $newvalue != NULL )
			{
				$db_info->data['description']  = $newvalue;
				$ovadb->update_record($db_info->data, $db_info->table_from);
				print "OK";
			}

	}

?>