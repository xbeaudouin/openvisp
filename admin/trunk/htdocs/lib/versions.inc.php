<?php
//
// File: versions.inc.php
//
// Versioning of OVA

if (ereg ("versions.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: login.php");
   exit;
}

// Thanks for cacti about this idea
$hash_version = array(
	"0.96" => "0000",
	"0.97-dev" => "0001",
	"1.0-RC1" =>  "0002",
);

//
// This function returns :
//  2 = Unknow upgrade
//  1 = Needs upgrade
//  0 = there is no needs to upgrade.
// call: needs_upgrade()
//
function needs_upgrade() {
 global $hash_version, $version;

 $result = db_query ("SELECT ova FROM ovavers");

 if ($result['rows'] > 0)
 { 
   // We have some results
   // Check now if we have to upgrade
   $row = db_row($result['result']);
   $db_vers = $row[0];

   if($hash_version[$db_vers] == $hash_version[$version]) {
     // DB version is same as running version
     return 0;
   }
   if($hash_version[$db_vers] < $hash_version[$version]) {
     // DB version is lower than running version, we need update
     return 1;
   }
 } else {
   // DB version is undetermined
   return 2;
 }
} 

//
// This function returns the current database version
//
function versionfrom() {

	$result = db_query ("CREATE TABLE IF NOT EXISTS `ovavers` ( `ova` char(20) ) ENGINE=INNODB");
  
	$result = db_query("SELECT ova FROM ovavers");
	if ($result['rows'] > 0)
	{
		$row = db_row($result['result']);
		return  $row[0];
	} else {
		// Version update has started on 0.96, so assume we are on 0.96
		// and update the version into DB to 0.96
		db_query("INSERT INTO ovavers (ova) VALUES ('0.96')");
		return "0.96";
	}
}

// 
// Set the version in DB with the current version string
//
function update_db_vers() {
	global $version;

	$sql_add = "";

	$current_vers = versionfrom();
	if ( $version != $current_vers ) { $sql_add = "AND query=0"; }
	$result = db_query("UPDATE ovavers SET ova = '".$version."' $sql_add WHERE ova = '".$current_vers."'");
}


//
// This function fetch the last query id run on the db.
//
function lastquery_version() {
  
	$result = db_query("SHOW COLUMNS FROM ovavers LIKE 'query'");
	if ($result['rows'] == 0)
	{
		$result = db_query("ALTER TABLE  `ovavers` ADD  `query` SMALLINT( 2 ) NOT NULL DEFAULT  '0'");
	} 

	$result = db_query("SELECT query FROM ovavers");
	$row = db_row($result['result']);
	return  $row[0];
}


// 
// increase the query number by on
//
function increase_query($number) {
	global $version;
	$result = db_query("UPDATE ovavers SET query='$number' ");
	//print "UPDATE ovavers SET query='$number'\n<br/>";
}


?>
