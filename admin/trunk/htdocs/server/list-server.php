<?php
//
// File: server/list-server.php
//
// Template File: server/list-server.tpl
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

$SESSID_USERNAME = check_user_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/server/menu.tpl");

	 $is_technical = is_technical($SESSID_USERNAME);
	 $fType = get_get("fType");
	 if ( $fType == "model" ){
		 $list_server = list_server_model();
		 include ("../templates/server/list-server_model.tpl");
	 }
	 else{
		 $list_server = list_server();
		 $list_job_model = list_server_model();

		 include ("../templates/server/list-server.tpl");
	 }


   include ("../templates/footer.tpl");




}


?>
