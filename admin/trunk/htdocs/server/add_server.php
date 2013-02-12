<?php
//
// File: server/add_server.php
//
// Template File: server/add_server.tpl
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
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");


$ovadb = new DB();
$user_info = new USER($ovadb);
$ova = new OVA($ovadb); 

$SESSID_USERNAME = $ova->check_session();

$user_info->fetch_info($SESSID_USERNAME);
$user_info->check_domain_admin();
$user_info->fetch_quota_status();

$domain_info = new DOMAIN($ovadb);

$body_class = 'class="yui3-skin-sam"';

require ("../lib/hosting.inc.php");

if ( $_SERVER["REQUEST_METHOD"] == "GET" ){
  include ("../templates/header.tpl");
  include ("../templates/server/menu.tpl");
	$add_type = get_get('fType');
	$list_role = list_server_model();

	if ( $add_type == "server" ){
		include ("../templates/server/add_server.tpl");
	}
	else{
		include ("../templates/server/add_model.tpl");
	}
  include ("../templates/footer.tpl");
}


if ($_SERVER["REQUEST_METHOD"] == "POST"){


	$fServer_name = get_post("fServer_name");
	$fType = get_post("fType");
	$fServer_active = get_post("fServer_active");
	$fServer_desc = get_post("fServer_desc");
	$fServer_pub_ip = get_post("fServer_pub_ip");
	$fServer_prv_ip = get_post("fServer_prv_ip");
	$fServer_fqdn = get_post("fServer_fqdn");
	$fServer_model = get_post("check_role");
	$message = "";

	if ( $fServer_active == "on" ) { $fServer_active = "1"; } else { $fServer_active = "0"; }

	if ( $fType == "server" ){

		$new_server_id = add_new_server($fServer_name, $fServer_fqdn, $fServer_active, $fServer_desc);
		if ( $new_server_id != 0 ){
			$new_server_ip = add_new_ip($fServer_prv_ip, $fServer_pub_ip);
		}

		if ( $new_server_ip != 0 ){

			foreach ($_POST as $name => $value){

				if (ereg ("role-([0-9]*)_app-([0-9]*)", $name, $tab)){

					$app_login = get_post("login_app-".$tab[2]);
					$app_pass = get_post("pass_app-".$tab[2]);

					$add_result = add_new_server_job($new_server_id, $tab[1] , $new_server_ip, $tab[2], $fServer_active, $app_login, $app_pass);
							
					if ( $add_result == 1 ){
						$message = $PALANG['pAdd_server_successful'];
					}
					else {
						$message = $PALANG['pAdd_server_unsuccessful'];
					}
				}
			}
		}

	}

	if ( $fType == "model" ){
	
		$fRole_name = get_post("fRole_name");
		$fRole_desc = get_post("fRole_desc");
		$fRole_active = get_post("fRole_active");

		$add_result = add_new_model($fRole_name, $fRole_active, $fRole_desc);

		if ( $add_result == 1 ){
			$message = $PALANG['pAdd_server_model_successful'];
		}
		else{
			$message = $PALANG['pAdd_server_model_unsuccessful'];
		}
	}



	include ("../templates/header.tpl");
	include ("../templates/server/menu.tpl");
	include ("../templates/server/manage.tpl");
	include ("../templates/footer.tpl");




}


?>
