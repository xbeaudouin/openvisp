<?php
//
// File: sendmail.php
//
// Template File: sendmail.tpl
//
// Template Variables:
//
// tMessage
// tFrom
// tSubject
// tBody
//
// Form POST \ GET Variables:
//
// fTo
// fSubject
// fBody
//
require_once ("../variables.inc.php");
require_once ("../config.inc.php");
require_once ("../lib/functions.inc.php");
require_once ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/ova.class.php");
require_once ("../lib/user.class.php");

require_once ("../lib/ajax_yui.class.php");
$body_class = 'class="yui3-skin-sam"';

$ovadb = new DB();
$ova_info = new OVA($ovadb); 
$user_info = new USER($ovadb); 

$SESSID_USERNAME = check_user_session ();
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_domains();
$user_info->fetch_quota_status();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/sendmail.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fTo   = get_post('fTo');
   $fFrom = $SESSID_USERNAME;
   
   if (empty ($fTo) or !check_email ($fTo) or ($fTo == NULL))
   {
      $error    = 1;
      $tTo      = get_post('fTo');
      $tSubject = get_post('fSubject');
      $tBody    = get_post('fBody');
      $tMessage = $PALANG['pSendmail_to_text_error'];
   }

   if ($error != 1)
   {
     require("../lib/send_mail.inc.php");
     send_mail($fFrom,$fTo,get_post('fSubject'),get_post('fBody'));
     $tMessage .= $PALANG['pSendmail_result_succes'];
   }

   include ("../templates/header.tpl");
   include ("../templates/mail_menu.tpl");
   include ("../templates/sendmail.tpl");
   include ("../templates/footer.tpl");
}
?>
