<?php
//
// File: vacation.php
//
// Template File: users/vacation.tpl
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
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$tmp = preg_split ('/@/', $SESSID_USERNAME);     
$USERID_DOMAIN = $tmp[1];

if ( check_admin($SESSID_USERNAME) ){
	$account_information = get_account_info($SESSID_USERNAME);
	$account_quota = get_account_quota($account_information['id']);
	$account_rights = get_account_right($account_information['id']);
	$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));
 }

if ($_SERVER['REQUEST_METHOD'] == "GET")
	{
		$template = "vacation.tpl";
		$result = db_query("SELECT * FROM vacation WHERE email='$SESSID_USERNAME'");
		if ($result['rows'] == 1)
			{
				$row = db_array($result['result']);
				$fSubject = $row['subject'];
				$fBody = $row['body'];
				$fActive = $row['active'];
				$fType = "update";
				//$fSelected = "";
				//				if ( $fActive == 1 ) $fSelected = "selected";
				//$tMessage = $PALANG['pUsersVacation_welcome_text'];
				//      $template = "vacation-get.tpl";
			}
		else
			{
				$fSubject = $PALANG['pUsersVacation_subject_text'];
				$fBody = $PALANG['pUsersVacation_body_text'];
				$fActive = 0;
				$fType = "new";
			}
   
		include ("../templates/header.tpl");
		include ("../templates/users/menu.tpl");
		include ("../templates/users/$template");
		include ("../templates/footer.tpl");
	}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	 
   $fSubject = get_post ('fSubject');
   $fBody    = get_post ('fBody');
   $fStatus  = get_post ('fStatus');
   $fType  = get_post ('fType');

	 if ($fSubject == NULL) $fSubject = "";
	 if ($fBody == NULL)    $fBody = "";

	 if ( $fType == "new" ){
		 $result = db_query ("INSERT INTO vacation (email,subject,body,domain,active,created) VALUES ('$SESSID_USERNAME','$fSubject','$fBody','$USERID_DOMAIN','$fStatus',NOW())");
	 }
	 else{
		 $result = db_query ("UPDATE vacation SET subject='$fSubject',body='$fBody',active='$fStatus'  WHERE email='$SESSID_USERNAME'");
	 }
	 if ($result['rows'] != 1)
		 {
			 $error = 1;
			 $tMessage = $PALANG['pUsersVacation_result_error'];
		 }
	 else
		 {
			 header ("Location: main.php");
			 exit;
		 }
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/vacation.tpl");
   include ("../templates/footer.tpl");
}
?>
