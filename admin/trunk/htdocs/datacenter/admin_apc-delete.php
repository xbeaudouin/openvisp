<?php
//
// File: admin/apc-delete.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
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
   if ($fName != NULL) {
     $apc_stuff=get_apc_properties($fName);
     $apc_id = $apc_stuff['id'];
     $result = db_query ("DELETE FROM apc WHERE name='$fName'");
     if ($result['rows'] != 1)
     {
       $error = 1;
       $tMessage = $PALANG['pDataCenter_adminapc_result_error'];
     }
     if ($error != 1) {
       $result = db_query ("DELETE FROM apc_ports WHERE apc='$apc_id'");
     }
     if ($error != 1) {
       $result = db_query ("DELETE FROM apc_admins WHERE apc='$apc_id'");
     }

     if ($error != 1)
     {
       header ("Location: admin_apc.php");
       exit;
     }
   } else {
     // Bad boy that plays with scripts !
     header ("Location: logout.php");
     exit;
   }
   
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
