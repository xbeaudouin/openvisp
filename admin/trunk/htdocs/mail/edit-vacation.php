<?php
//
// File: edit-vacation.php
//
// Template File: edit-vacation.tpl
//
// Template Variables:
//
// tMessage
// tSubject
// tBody
//
// Form POST \ GET Variables:
//
// fSubject
// fBody
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
	 $overview2 = "";
	 $tDomain = $fDomain;

	 $sql_query = "SELECT vacation.*
FROM vacation, mailbox
WHERE mailbox.username='$fUsername'
AND mailbox.id=vacation.mailbox_id
AND vacation.active='1'";

   $result = db_query ($sql_query);
   if ($result['rows'] == 1)
   {
      $template = "vacation-get.tpl";

   }
   else
   {
      $template = "vacation.tpl";
   }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/users/$template");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

   $overview2 = "";
   $fSubject  = get_post('fSubject');
   $fBody     = get_post('fBody');
   $fAway     = get_post('fAway');
   $fBack     = get_post('fBack');
   $fUsername = get_post('fUsername');
   $fDomain   = get_post('fDomain');
	 $tDomain = $fDomain;

   if (isset ($fBack))
   {
		 $sql_query = "UPDATE vacation, mailbox
SET vacation.active='0'
WHERE mailbox.username='$fUsername'
AND mailbox.id=vacation.mailbox_id
";
     $result = db_query ($sql_query);

      if ($result['rows'] != 1)
      {
         $error = 1;
         $tMessage = $PALANG['pUsersVacation_result_error'];
      }
      else
      {
         $tMessage = $PALANG['pUsersVacation_result_succes'];
      }
   }

   if (!empty ($fAway))
   {

		 $sql_query = "SELECT vacation.*
FROM vacation, mailbox
WHERE mailbox.username='$fUsername'
AND mailbox.id=vacation.mailbox_id";

		 $result = db_query ($sql_query);
		 if ($result['rows'] == 1){

			 $sql_query = "UPDATE vacation, mailbox
SET  vacation.subject='$fSubject', vacation.body='$fBody', vacation.active='1'
WHERE  mailbox.username='$fUsername'
AND mailbox.id=vacation.mailbox_id";

			 $result = db_query ($sql_query);
		 }
		 else{

			 $mailbox_id = get_mailbox_id ($fUsername);
			 $sql_query = "INSERT INTO vacation (mailbox_id, subject, body, created, active)
VALUES ('$mailbox_id','$fSubject','$fBody',NOW(),'1')";
			 
			 $result = db_query ($sql_query);
		 }


      if ($result['rows'] != 1)
      {
         $error = 1;
         $tMessage = $PALANG['pUsersVacation_result_error'];
      }
      else
      {
         header ("Location: overview.php?domain=$fDomain");
         exit;
      }
   }

   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/users/vacation.tpl");
   include ("../templates/footer.tpl");
}
?>
