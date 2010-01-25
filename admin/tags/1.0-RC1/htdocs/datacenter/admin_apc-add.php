<?php
//
// File: admin_apc-add.php
//
// Template File: datacenter/admin/apc-add.tpl
//                datacenter/admin/apc-add-done.tpl
//
// Template Variables:
//
// tName
// tIp
// tPrint
// tImportNames
//
// Form POST \ GET Variables:
//
// fName
// fIp
// fPrint
// fImportNames
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_full_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/verif-js.tpl");
   include ("../templates/datacenter/admin/apc-add.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $error = 0;
   $fName = get_post('fName');
   $fIp   = get_post('fIp');
   $fPrint       = get_post('fPrint');
   $fImportNames = get_post('fImportNames');
   
   if (empty ($fName) or empty($fIp) or apc_exist ($fName, $fIp) or !ip_check($fIp))
   {
      $error = 1;
      $tName = get_post('fName');
      $tIp   = get_post('fIp');
      $tPrint       = get_post('fPrint');
      $tImportNames = get_post('fImportNames');
      $tMessage = $PALANG['pDataCenter_adminapc_apc_add_error'];
   }
      
   if ($error != 1)
   {
      // There is no error so we can add the APC into DB
      require("../lib/apc.inc.php");

      $apc_basic_stuff = apc_get_snmp_basic_stuff($fIp);
      $tName = get_post('fName');
      $tIp   = get_post('fIp');
      $tPrint       = get_post('fPrint');
      $tImportNames = get_post('fImportNames');

      if (empty($apc_basic_stuff['uptime'])) {
	$error = 1;
        $tMessage = $PALANG['pDataCenter_adminapc_apc_add_snmp_error'];
      }

      $apc_nbpdu = $apc_basic_stuff['numpdu'];
      // Add the APC into APC table
      $result = db_query ("INSERT INTO apc (name,ip,nbports,created,modified) VALUES ('$fName','$fIp','$apc_nbpdu',NOW(),NOW())");
      if ($result['rows'] != 1)
      {
	$error = 1;
        $tMessage = $PALANG['pDataCenter_adminapc_apc_add_sql_error'];
      }
      else
      {
        $apc_id = get_apc_id($fIp);
	if ($apc_id == '0') {
	  $error = 1;
          $tMessage = $PALANG['pDataCenter_adminapc_apc_add_sql_error'];
	} else {
	  // Creating Outlets array
	  // TODO : move this to apc.inc.php
	  $outlets = array ();
	  for ($itmp=1; $itmp<=$apc_nbpdu; $itmp++) {
	    array_push($outlets, $itmp);
	  }
          if ($fImportNames == "on") {
            foreach($outlets as $i) {
              $pdustuff = apc_get_outlet_stuff($fIp, $i);
              $result = db_query("INSERT INTO apc_ports (port, descr, apc, created, modified) VALUES ('$i','".$pdustuff['label']."','$apc_id',NOW(),NOW())");
              if ($result['rows'] !=1) {
                $error = 1;
                $tMessage = $PALANG['pDataCenter_adminapc_apc_add_sql_error'];
              }
            }
          } else {
            foreach($outlets as $i) {
              $outlet = "Outlet ".$i;
              $result = db_query("INSERT INTO apc_ports (port, descr, apc, created, modified) VALUES ('$i','$outlet','$apc_id',NOW(),NOW())");
              if ($result['rows'] !=1) {
                $error = 1;
                $tMessage = $PALANG['pDataCenter_adminapc_apc_add_sql_error'];
              }
              apc_set_outlet_label($fIp, $i, $outlet); 
            }
          }
        }
      }
   }

  include ("../templates/header.tpl");
  include ("../templates/datacenter/menu.tpl");
  include ("../templates/verif-js.tpl");
  if ($error == 1) {
    include ("../templates/datacenter/admin/apc-add.tpl");
  } else {
    include ("../templates/datacenter/admin/apc-add-done.tpl");
  }
  include ("../templates/footer.tpl");

}
?>
