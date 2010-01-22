<?php
//
// File: hosting/edit-active.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fVhost
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fVhost = get_get('vhost');
   $fDomain = get_get('domain');
   
   if ( ( $fVhost != NULL ) && ( $fDomain != NULL ) ){
		 $result = db_query ("UPDATE whost SET active=1-active,modified=NOW() WHERE vhost='$fVhost' AND domain='$fDomain'");
		 if ($result['rows'] != 1)
			 {
				 $error = 1;
				 $tMessage = $PALANG['pWhostEdit_whost_active_error'];
			 }
		 
		 if ($error != 1)
			 {
				 db_log ($SESSID_USERNAME, $fDomain, "Virtual Website disabled ($fVhost)", $fUsername);
				 header ("Location: list-webvirtual.php?username=$SESSID_USERNAME");
				 exit;
			 }
   }

   include ("../templates/header.tpl");
   include ("../templates/admin_menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/admin_menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
