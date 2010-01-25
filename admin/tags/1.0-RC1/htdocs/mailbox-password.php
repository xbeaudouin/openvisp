<?php
//
// File: mailbox-password.php
//
// Template File: password.tpl
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
require ("./variables.inc.php");
require ("./config.inc.php");
require ("./lib/functions.inc.php");
include ("./languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$error = 0;

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   include ("./templates/header.tpl");
   include ("./templates/menu.tpl");
   include ("./templates/password.tpl");
   include ("./templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

   $fPassword_current = get_post('fPassword_current');
   $fPassword         = get_post('fPassword');
   $fPassword2        = get_post('fPassword2');

   $username = $SESSID_USERNAME;

   $result = db_query ("SELECT * FROM mailbox WHERE username='$username'");
   $admin_result = db_query ("SELECT * FROM accounts WHERE username='$username'");

	 if ( $admin_result['rows'] == 1 ){
      $row = db_array ($admin_result['result']);
      $checked_password = pacrypt ($fPassword_current, $row['password']);

      $result = db_query ("SELECT * FROM accounts WHERE username='$username' AND password='$checked_password'");      
      if ($result['rows'] != 1)
      {
 
         $error = 1;
         $pError_text = $PALANG['pPassword_password_current_text_error'];
      }
		 
	 }
	 elseif ($result['rows'] == 1)
   {
      $row = db_array ($result['result']);
      $checked_password = pacrypt ($fPassword_current, $row['password']);

      $result = db_query ("SELECT * FROM mailbox WHERE username='$username' AND password='$checked_password'");      
      if ($result['rows'] != 1)
      {
	
         $error = 1;
         $pError_text = $PALANG['pPassword_password_current_text_error'];
      }
   }
   else
   {
      $error = 1;
      $pError_text = $PALANG['pPassword_email_text_error'];
   }

   if (empty ($fPassword) or ($fPassword != $fPassword2))
   {
      $error = 1;
      $pError_text = $PALANG['pPassword_password_text_error'];
   }

   if ($error != 1)
   {

		 if ( check_little_admin($username) ){
			 $result_admin = update_admin_password($username, $fPassword);
		 }

		 if ( is_enduser_mailbox_account($username) ){
			 $result = update_mailbox_password($username, $fPassword);
		 }


		 if ( ( $result['rows'] == 1 ) || ( $result_admin['rows'] == 1 ) )
      {
         $tMessage = $PALANG['pPassword_result_succes'];
      }
      else
      {
         $tMessage = $PALANG['pPassword_result_error'];
      }

   } else {
   echo $pError_text;
   }
   
   include ("./templates/header.tpl");
   include ("./templates/menu.tpl");
   include ("./templates/password.tpl");
   include ("./templates/footer.tpl");
}
?>
