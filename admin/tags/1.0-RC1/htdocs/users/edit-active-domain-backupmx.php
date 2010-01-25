<?php
//
// File: edit-active-domain-backupmx.php
//
// Template File: admin_edit-active-domain-backupmx.tpl
//
// Template Variables:
//
// tTransport
//
// Form POST \ GET Variables:
//
// fTransport
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	$domain = get_get('domain');
   $domain_properties = get_domain_properties ($domain);
   
   $tTransport = $domain_properties['transport'];
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-active-domain-backupmx.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = get_post('domain');
   
   $fTransport = get_post('fTransport');
   if ($fTransport == '')
   {
     $fTransport = 'NULL';
   }
 
   $result = db_query ("UPDATE domain SET modified=NOW(),transport='$fTransport' WHERE domain='$domain'");
   if ($result['rows'] == 1)
   {
	header ("Location: list-domain.php");
   }
   else
   {
	$tMessage = $PALANG['pAdminEdit_active_domain_backupmx_result_error'];
   }

   include ("../templates/header.tpl");
   include ("../templates/admin_menu.tpl");
   include ("../templates/users/edit-active-domain-backupmx.tpl");
   include ("../templates/footer.tpl");
}
?>
