<?php
//
// File: apc.php
//
// Template File: datacenter/stats.tpl
//                datacenter/stats_none.tpl
//                datacenter/stats_ymwd.tpl
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $stats_list = list_stats_for_admin ($SESSID_USERNAME);

   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   if (is_array($stats_list)) {
     include ("../templates/datacenter/stats.tpl");
   } else {
     include ("../templates/datacenter/stats_none.tpl");
   }
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $stats_list = list_stats_for_admin ($SESSID_USERNAME);

   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   if (is_array($stats_list)) {
     include ("../templates/datacenter/stats.tpl");
   } else {
     include ("../templates/datacenter/stats_none.tpl");
   }
   include ("../templates/footer.tpl");
}
?>
