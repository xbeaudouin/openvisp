<?php
//
// File: admin_list.php
//
// Template File: datacenter/admin.tpl
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
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_full_session();

$list_admins = list_datacenter_admins ();
if ((is_array ($list_admins) and sizeof ($list_admins) > 0))
{
   for ($i = 0; $i < sizeof ($list_admins); $i++)
   {
      $admin_properties[$i] = get_datacenter_admin_properties ($list_admins[$i]);
   }
}

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/admin.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/admin.tpl");
   include ("../templates/footer.tpl");
}
?>
