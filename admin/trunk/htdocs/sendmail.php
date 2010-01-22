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
require ("./variables.inc.php");
require ("./config.inc.php");
require ("./lib/functions.inc.php");
include ("./languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("./templates/header.tpl");
   include ("./templates/mail/menu.tpl");
   include ("./templates/sendmail.tpl");
   include ("./templates/footer.tpl");
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
     require("./lib/send_mail.inc.php");
     send_mail($fFrom,$fTo,get_post('fSubject'),get_post('fBody'));
     $tMessage .= $PALANG['pSendmail_result_succes'];
   }

   include ("./templates/header.tpl");
   include ("./templates/mail_menu.tpl");
   include ("./templates/sendmail.tpl");
   include ("./templates/footer.tpl");
}
?>
