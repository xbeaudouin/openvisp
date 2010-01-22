<?php
//
// File: server/add_app.php
//
// Template File: server/add_app.tpl
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

		$list_job_model = list_server_model();
		include ("../templates/header.tpl");
		include ("../templates/server/menu.tpl");
		include ("../templates/server/add_app.tpl");
		include ("../templates/footer.tpl");
	}


if ($_SERVER["REQUEST_METHOD"] == "POST")
	{


		$fApp_name = get_post("fApp_name");
		$fApp_active = get_post("fApp_active");
		$fApp_version = get_post("fApp_version");
		$fApp_desc = get_post("fApp_desc");
		$fApp_model = get_post("fApp_model");

		if ( $fApp_active == "on" ) { $fApp_active = "1"; } else { $fApp_active = "0"; }

		file_put_contents('php://stderr', "NG : $fApp_name $fApp_version\n");

		$add_result = add_new_app($fApp_name, $fApp_active, $fApp_version, $fApp_desc, $fApp_model);

		if ( $add_result == 1 ){
			$message = $PALANG['pAdd_app_successful'];
		}
		else{
			$message = $PALANG['pAdd_app_unsuccessful'];
		}

	include ("../templates/header.tpl");
	include ("../templates/server/menu.tpl");
	include ("../templates/server/add_app.tpl");
	include ("../templates/footer.tpl");

}


?>
