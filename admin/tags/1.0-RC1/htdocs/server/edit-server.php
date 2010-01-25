<?php
//
// File: server/edit_server.php
//
// Template File: server/edit_server.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// server
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
	 $server_info = server_info($fServer_id);
	 $list_role = list_server_model();
	 $server_role = server_role($fServer_id);

	 include ("../templates/server/edit-server.tpl");

   include ("../templates/footer.tpl");
}


if ($_SERVER["REQUEST_METHOD"] == "POST")
{

	$fServer_name = get_post("fServer_name");
	$fType = get_post("fType");
	$fServer_active = get_post("fServer_active");
	$fServer_desc = get_post("fServer_desc");
	$fServer_pub_ip = get_post("fServer_pub_ip");
	$fServer_prv_ip = get_post("fServer_prv_ip");
	$fServer_fqdn = get_post("fServer_fqdn");
	$fServer_role = get_post("fServer_role");
	$fServer_id = get_post("fServer_id");
 
	if ( $fServer_active == "on" ) { $fServer_active = "1"; } else { $fServer_active = "0"; }

	if ( $fType == "server" ){

		$message = modify_server($fServer_name, $fServer_fqdn, $fServer_active, $fServer_desc, $fServer_prv_ip, $fServer_pub_ip, $fServer_role, $fServer_id);

	}

	include ("../templates/header.tpl");
	include ("../templates/server/menu.tpl");
	include ("../templates/server/manage.tpl");
	include ("../templates/footer.tpl");




}


?>
