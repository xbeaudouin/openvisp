<?php
//
// File: requests.php
//
// Template Files: datacenter/requests.tpl
//                 datacenter/requests_admin.tpl
//                 datacenter/requests_tech.tpl
//                 datacenter/requests_sendmail.tpl
//
// Template Variables:
//
// tWhat
// tReboot
// tLed
// tScreen
// tPing
// tOthers
// tOthers_text
// tComment
// tBody
// tMessage
//
// Form POST \ GET Variables:
//
// fWhat
// fReboot
// fLed
// fScreen
// fPing
// fOthers
// fOthers_text
// fComment
// fType
// fDatetime
// fNames
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/datacenter.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_datacenter_session ();

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   include ("../templates/datacenter/requests.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   $username    = $SESSID_USERNAME;
   $sendingmail = 0; // Set the send mail flag only when mail has been sent !

   // fWhat should be "tech" or "admin"
   $fWhat = get_post('fWhat');
   if($fWhat == "tech_doit") {
      // This handle local technician help for hosting users
      $fReboot      = get_post('fReboot');
      $fLed         = get_post('fLed');
      $fScreen      = get_post('fScreen');
      $fPing        = get_post('fPing');
      $fOthers      = get_post('fOthers');
      $fOthers_text = get_post('fOthers_text');
      $fComment     = get_post('fComment');

      if(isset($fComment) && ($fComment != "")) {
        // Include send mail functions only if needed
        require ("../lib/send_mail.inc.php");

        $tBody = $CONF['datacenter_request_body'] . "\n";
        if ($fReboot == "on" ) {
          $tBody .= "\n" . $CONF['datacenter_request_reboot'];
          $sendingmail = 1;
        }
        if ($fLed == "on" ) {
          $tBody .= "\n" . $CONF['datacenter_request_led'];
          $sendingmail = 1;
        }
        if ($fScreen == "on" ) {
          $tBody .= "\n" . $CONF['datacenter_request_screen'];
          $sendingmail = 1;
        }
        if ($fPing == "on") {
          $tBody .= "\n" . $CONF['datacenter_request_ping'];
          $sendingmail = 1;
        }
        if ($fOthers == "on" && isset($fOthers_text) && ($fOthers_text != "")) {
          $tBody .= "\n" . $CONF['datacenter_request_others'] . $fOthers_text . "\n";
          $sendingmail = 1;
        }
        $tBody .=  "\n\n".$CONF['datacenter_request_comment'] ."\n". $fComment . "\n";

	if ($sendingmail == 0) {
          $tMessage = $PALANG['pDataCenter_requests_tech_error_nocheckboxes'];
        } else {
          send_mail($SESSID_USERNAME, $CONF['datacenter_request_to'],$CONF['datacenter_request_subject'],$tBody . $CONF['datacenter_request_signature'],$CONF['datacenter_request_bcc']);
        }
      } else {
        $tMessage = $PALANG['pDataCenter_requests_tech_error_comment'];
      }

   } elseif ($fWhat == "admin_doit") {
      // This handle hosting users owner acces to the datacenter
      $fType     = get_post('fType');
      $fDatetime = get_post('fDatetime');
      $fNames    = get_post('fNames');
      $fComment  = get_post('fComment');

      if (isset($fComment) && ($fComment != "") && isset($fType) && ($fComment != "") && isset($fDatetime) && ($fDatetime != "") && isset($fNames) && ($fNames != "")) {
         // We will send a mail
         require ("../lib/send_mail.inc.php");
         $tBody  = "\n" . $CONF['datacenter_request_access_body'] . "\n\n" . $CONF['datacenter_request_access_email'] . $SESSID_USERNAME;
         $tBody .= "\n\n" . $CONF['datacenter_request_access_type'] . $fType;
         $tBody .= "\n\n" . $CONF['datacenter_request_access_datetime'] . $fDatetime;
         $tBody .= "\n\n" . $CONF['datacenter_request_access_names'] . $fNames;
         $tBody .= "\n\n" . $CONF['datacenter_request_access_comment'] . $fComment;
         $tBody .= "\n\n" . $CONF['datacenter_request_access_signature'];
         $sendingmail = 1;
         send_mail($CONF['datacenter_request_access_to'],$CONF['datacenter_request_access_to'],$CONF['datacenter_request_access_subject'],$tBody,$SESSID_USERNAME);
         $tMessage .= $CONF['datacenter_request_access_notice'];
      } else {
        $tMessage = $PALANG['pDataCenter_requests_admin_error'];
      }
   }

   include ("../templates/header.tpl");
   include ("../templates/datacenter/menu.tpl");
   if ($fWhat == "tech") {
     include ("../templates/datacenter/requests_tech.tpl");
   } elseif ($fWhat == "admin") {
     include ("../templates/datacenter/requests_admin.tpl");
   } elseif ($sendingmail == 1) {
     include ("../templates/datacenter/requests_sendmail.tpl");
   } else {
     include ("../templates/datacenter/requests.tpl");
   }
   include ("../templates/footer.tpl");
}
?>
