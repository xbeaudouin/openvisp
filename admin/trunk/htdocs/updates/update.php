<?php
//
// File: update.php
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/versions.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/ova.class.php");

$ovadb = new DB();
$ova_info = new OVA($ovadb); 

$ova_info->directory_update = "./";

$ova_info->fetch_running_version();

$ova_info->fetch_latest_version();

if ( $ova_info->latest_version == $ova_info->running_version ){

	print "OK running the lastest version<br/>";

}

$ova_info->fetch_latest_sql();

//$ova->show_latest_sql();

$ova_info->apply_latest_sql();

print "Allright, everything is up to date<br/>";
print "You can <a href='../login.php'>login</a> now";

?>
