<?php
//
// File: apc.php
//
// Template File: datacenter/apc.tpl
//                datacenter/apc_none.tpl
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
// fNbPDU
// fPDU<x>
// fPassword
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
require ("../lib/datacenter.inc.php");
require ("../lib/apc.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $apc_list = list_apc_ports_for_admin($SESSID_USERNAME);

   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/verif-js.tpl");
   if(is_array($apc_list)) {
    include ("../templates/datacenter/apc.tpl");
   } else {
    include ("../templates/datacenter/apc_none.tpl");
   }
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $username = $SESSID_USERNAME;
   $apc_list = list_apc_ports_for_admin($username);

   $fNbPdu    = get_post('fNbPdu');
   $fPassword = get_post('fPassword');
 
   if($fNbPdu == "0" && !is_array($apc_list)) {
      header ("Location: main.php");
      exit;
   } 
   
   // Check if password is empty
   if ($fPassword==NULL)
   {
      $error = 1;
      $tMessage = $PALANG['pDataCenter_apc_error1'];
   }
  
   // Check if the password is correct according to DB
   if (!chk_passwd_user($username, $fPassword)) {
      $error = 1;
      $tMessage = $PALANG['pDataCenter_apc_error2'];
   }

   // Now all is ok, then we can do action if needed :)
   if ($error != 1)
   {
     require ("../lib/send_mail.inc.php");
     for ($i = 1; $i <= $fNbPdu; $i++) {
       $what = "fPDU".$i;
       $todo =  $_POST[$what]; 
       $a_apc = explode (" :: ", $todo);
       if (count($a_apc) == 3) {
          // Variable value is ok, so lets make the work.
          $apc_name = $a_apc[0]; // APC name
          $apc_port = $a_apc[1]; // APC outlet number
          $apc_pdu  = $a_apc[2]; // APC status requested
          $apc_stuff= get_apc_properties($apc_name);
          $apc_ip   = $apc_stuff['ip'];
	  //print "apc : name $apc_name, $apc_port, $apc_pdu, $apc_ip";
	  //print "username : $username";
          if ((is_apc_admin($username, $apc_name, $apc_port)) || is_datacenter_full($username)) {
            // Admin has right to modify ports so we can do the job here...
            // Get the outlet status
	    //print "ok";
            $outlet_status = apc_get_outlet_stuff($apc_ip, $apc_port);
            // We get the status, compare if a change is requested, otherwise do nothing :)
            if ( $apc_pdu != $outlet_status['status']) {
               // Ok change the outlet status
               apc_set_outlet_what($apc_ip, $apc_port, $apc_pdu);
               $body  = $CONF['datacenter_apc_status_body'] . $username . "\t" . $apc_name . "\t" . $apc_port . "\t";
               $body .= $outlet_status['label'] . "\t" . apc_outletstatus2human($outlet_status['status']) . "\t";
               $body .= apc_outletstatus2human($apc_pdu) . "\n\n";
               send_mail($CONF['datacenter_apc_status_from'],$CONF['datacenter_apc_status_to'],$CONF['datacenter_apc_status_subject'], $body);
               send_mail($CONF['datacenter_apc_status_from'],$username,$CONF['datacenter_apc_status_subject'], $body);
            }
          }
       }

     }
   }

  include ("../templates/header.tpl");
  include ("../templates/datacenter/menu.tpl");
  include ("../templates/verif-js.tpl");
  include ("../templates/datacenter/apc.tpl");
  include ("../templates/footer.tpl");

}
?>
