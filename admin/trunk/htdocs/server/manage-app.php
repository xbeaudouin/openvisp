<?php
//
// File: server/manage-app.php
//
// Template File: server/manage-app.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/server/menu.tpl");
	 $fServer_id = get_get('fServer_id');
	 $list_server_job = list_server_apps($fServer_id);
	 $list_job_model = list_server_model_with_app();
	 $list_role = list_server_model();
	 //	 $list_server_apps = list_server_app($fServer_id);
	 $server_info = server_info($fServer_id);
	 include ("../templates/server/manage-app.tpl");


   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{


	$deleteRow = get_post('deleteRow');
	$action = get_post('action');
	$server_id = get_post('server_id');
	$role_id = get_post('role_id');
	$app_id = get_post('app_id');
	$ip_id = get_post('ip_id');
	$upfield = get_post('upfield');
	$newvalue = get_post('newvalue');
	$priv_ip = get_post('priv_ip');
	$pub_ip = get_post('pub_ip');
	$login = get_post('login');
	$password = get_post('password');
	$port = get_post('port');

	$fServer_id = $server_id;
	$list_server_job = list_server_apps($fServer_id);
	$list_job_model = list_server_model();
	$list_role = list_server_model();
	//	 $list_server_apps = list_server_app($fServer_id);
	$server_info = server_info($fServer_id);

	$result = "";

	if ( $upfield == "active" ){
		$newvalue = ($newvalue == "Yes") ? 1 : 0;

	}
	if ( $upfield != FALSE ){
		$result = modify_app_server ($server_id, $ip_id, $app_id, $role_id, $port, $upfield, $newvalue );
	}

	if ( $action == "delete" ){
		$result = delete_app_server ($server_id, $ip_id, $app_id, $role_id, $port);
	}

	if ( $action == "add" ){
		$ip_id = add_ip_ifnot_exist($pub_ip, $priv_ip);
		if ( $ip_id > 0 )
			{
				$result = add_app_server ($server_id,  $app_id, $role_id, $ip_id, $login, $password, $port);
			}

		//		add_app_server($server_id, $app_id, $jobmodel_id, $ip_id, $login="", $password="" ){
		include ("../templates/header.tpl");
		include ("../templates/server/menu.tpl");
		include ("../templates/server/manage-app.tpl");
		include ("../templates/footer.tpl");
	}
	else
		{
			print $result;
		}






}


?>