<?php
//
// File: admin_user-apc.php
//
// Template File: datacenter/users-apc.tpl
//
// Template Variables:
//
// Form POST \ GET Variables:
//
// fName
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_full_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fName = get_get('name');

   $list_apc = list_apc_ports();
   $tApc = list_apc_ports_for_admin($fName);

   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/users-apc.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fName = get_post('name');
   $fApcs = get_post('fApcs');
   if( ($fApcs == NULL) || ($fName == NULL) ) {
     header ("Location: admin_list.php");
     exit;
   }

   $admin_id = get_datacenter_id($fName);
   
   if (sizeof ($fApcs) > 0)
   {
     delete_apc_admin($admin_id);
     foreach($fApcs as $apc) {
       //$result = db_query ("INSERT INTO apc_admins (id,apc,port,created,active) VALUES ('$admin_id','".string_to_apc($apc)."','".string_to_apc_outlet($apc)."',NOW(),'1')");
       add_apc_right_to_admin($admin_id,get_apc_id_from_name(string_to_apc($apc)),string_to_apc_outlet($apc));
     }
     header ("Location: admin_list.php");
     exit;
   }
   else
   {
     $tMessage = $PALANG['pAdminEdit_admin_result_error'];
   }
	
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/users-apc.tpl");
   include ("../templates/footer.tpl");
}
?>
