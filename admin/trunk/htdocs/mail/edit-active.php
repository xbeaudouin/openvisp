<?php
//
// File: edit-active.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fUsername
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	
  $fUsername = get_get('username');
  $fDomain   = get_get('domain');
   
   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pEdit_mailbox_domain_error'] . "<b>$fDomain</b>!</font>";
   }
   else
   {
		 $result = db_query ("UPDATE mailbox SET active=1-active WHERE username='$fUsername' AND domain_id='".get_domain_id($fDomain)."'");
      if ($result['rows'] != 1)
      {
         $error = 1;
         $tMessage = $PALANG['pEdit_mailbox_result_error'];
      }
      else
      {
         db_log ($SESSID_USERNAME, $fDomain, "edit active", $fUsername);
      }
   }
   
   if ($error != 1)
   {
      header ("Location: overview.php?domain=$fDomain");
      exit;
   }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
