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
$ova = new OVA($ovadb); 

$ova->directory_update = "./";

$ova->fetch_running_version();

$ova->fetch_latest_version();

if ( $ova->latest_version == $ova->running_version ){

	print "OK running the lastest version<br/>";

}

$ova->fetch_latest_sql();

$ova->apply_latest_sql();

print "Allright, everything is up to date<br/>";
print "You can <a href='../login.php'>login</a> now";

?>
