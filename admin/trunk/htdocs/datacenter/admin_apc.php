<?php
//
// File: admin_apc.php
//
// Template Files: datacenter/admin-apc.tpl
//
// Template Variables:
// 
// --none--
//
// Form POST \ GET Variables:
//
// --none--
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_full_session ();

$list_apc = list_apc();

if ((is_array ($list_apc) and sizeof ($list_apc) > 0))
{
   for ($i = 0; $i < sizeof ($list_apc); $i++)
   {
      $apc_properties[$i] = get_apc_properties ($list_apc[$i]);
   }
}

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/admin/apc.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/admin/apc.tpl");
   include ("../templates/footer.tpl");
}
?>
