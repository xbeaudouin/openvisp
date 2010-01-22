<?php
//
// File: password.php
//
// Template File: users/password.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fPassword_current
// fPassword
// fPassword2
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$tmp = preg_split ('/@/', $SESSID_USERNAME);     
$USERID_DOMAIN = $tmp[1];

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/password.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fPassword_current = get_post ('fPassword_current');
   $fPassword         = get_post ('fPassword');
   $fPassword2        = get_post ('fPassword2');

   $username = $SESSID_USERNAME;
     
   $result = db_query ("SELECT * FROM mailbox WHERE username='$username'");
   if ($result['rows'] == 1)
   {
      $row = db_array ($result['result']);
      $checked_password = pacrypt ($fPassword_current, $row['password']);

		  $result = db_query ("SELECT * FROM mailbox WHERE username='$username' AND password='$checked_password'");      
      if ($result['rows'] != 1)
      {
         $error = 1;
         $pPassword_password_current_text = $PALANG['pPassword_password_current_text_error'];
      }
   }
   else
   {
      $result = db_query ("SELECT * FROM accounts WHERE username='$username'");
      if ($result['rows'] == 1)
      {
        $row = db_array ($result['result']);
        $checked_password = pacrypt ($fPassword_current, $row['password']);
        
        $result = db_query ("SELECT * FROM accounts WHERE username='$username' AND password='checked_password'");
        if ($result['rows'] != 1)
        {
          $error = 1;
          $pPassword_password_current_text = $PALANG['pPalang_password_current_text_error'];
        }
      }
      else
      {
      $error = 1;
      $pPassword_email_text = $PALANG['pPassword_email_text_error'];
      } 
   }

	if (empty ($fPassword) or ($fPassword != $fPassword2))
	{
	   $error = 1;
      $pPassword_password_text = $PALANG['pPassword_password_text_error'];
	}

   if ($error != 1)
   {
      $password = pacrypt ($fPassword);
      $result = db_query ("UPDATE mailbox SET password='$password',modified=NOW() WHERE username='$username'");
      if ($result['rows'] == 1)
      {
         $tMessage = $PALANG['pPassword_result_succes'];
         db_log ($SESSID_USERNAME, $USERID_DOMAIN, "change password", "$SESSID_USERNAME");
      }
      else
      {
         $tMessage = $PALANG['pPassword_result_error'];
      }
   }
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/password.tpl");
   include ("../templates/footer.tpl");
}
?>
