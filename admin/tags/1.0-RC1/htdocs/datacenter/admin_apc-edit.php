<?php
//
// File: admin_apc-edit.php
//
// Template File: datacenter/admin/apc-edit.tpl
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
// fName
// fName2 (In case of Name changing)
// fIp
// fPDU1 to fPDUxx
// fOutlet1 to fOutletxx
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
require ("../lib/apc.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_full_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $fName = get_get('name');	
   if ( $fName != NULL ) {
     // Check if APC exist and get properties
     if(! apc_exist($fName,"%")) {
       header ("Location: logout.php");
       exit;
     }
   } else {
     // Bad boy shouldn't play with URLs...
     header ("Location: logout.php");
     exit;
   }
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/verif-js.tpl");
   include ("../templates/datacenter/admin/apc-edit.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fName = get_post('fName');
   $fName2= get_post('fName2');
   $fIp   = get_post('fIp');
   
   // Minimal check before testing all others variables...
   if (($fName==NULL) or ($fIp==NULL) or ($fName2==NULL) or !apc_exist ($fName, $fIp) or !ip_check($fIp))
   {
      $error = 1;
      $tName = get_post('fName');
      $tIp   = get_post('fIp');
      $tMessage = $PALANG['pDataCenter_adminapc_apc_edit_error'];
   }
      
   if ($error != 1)
   {
      $tName = get_post('fName');
      $tIp   = get_post('fIp');
      $tName2= get_post('fName2');
      
      $apc_id    = get_apc_id($tIp);
      $apc_nbpdu = get_apc_nbpdu($tIp);

      // Check if name has changed, then update it into DB.
      if ($tName != $tName2) {
         // Change the name.
	 apc_rename($apc_id, $tName2);
      }

      // Create the outlet array depending of number of outlets detected in the APC
      $outlets = array ();                                                         
      for ($itmp = 1; $itmp <= $apc_nbpdu; $itmp++) {              
        array_push($outlets, $itmp);                                               
      }
      foreach($outlets as $i) {
        $pdustuff = apc_get_outlet_stuff($fIp, $i);
        // Check if the name has changed
        $what = "fPDU".$i;
        $label = get_post($what);
        if ($label != $pdustuff['label']) {
          // Update the label on the APC
          apc_set_outlet_label($fIp, $i, $label); 
          // Update the database label
	  apc_rename_outlet($apc_id, $i, $label);
        }
        $what = "fOutlet".$i;
        $status = get_post($what);
        if ($status != $pdustuff['status']) {
           // Set the outlet to the requested state
           apc_set_outlet_what($fIp, $i, $status);
        }
      }
      $tMessage = $PALANG['pDataCenter_adminapc_apc_edit_ok'];
   }

  include ("../templates/header.tpl");
  include ("../templates/datacenter/menu.tpl");
  include ("../templates/verif-js.tpl");
  include ("../templates/datacenter/admin/apc-edit.tpl");
  include ("../templates/footer.tpl");

}
?>
