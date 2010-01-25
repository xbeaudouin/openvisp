<?php

	//
	// File: active-filter.php
	//
	// Template : active-filter.tpl
	//
	// Form POST \ GET Variables:
	//
	// fNum
	//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fNum = get_get('num');
	$fUsername = $SESSID_USERNAME;
	$domain=explode("@",$fUsername);

	if (!check_filter_owner ($SESSID_USERNAME, $fNum))
		{
      $error = 1;
      $tMessage = $PALANG['pUsersFilter_not_owner']."!";
		}
	else
		{
      $result = db_query ("DELETE filter.* FROM filter,mailbox WHERE mailbox.username='$fUsername' AND mailbox.id=filter.mailbox_id AND filter.id='$fNum'");
      if ($result['rows'] != 1)
				{
					$error = 1;
					$tMessage = $PALANG['pEdit_delete_filter_error'];
				}
      else
				{
					db_log ($SESSID_USERNAME, $fDomain[1], "delete filter", "$fUsername $fNum");
				}
		}
	
	if ($error != 1)
		{
			header ("Location: list-filter.php");
			exit;
		}
	else{
		$table_filter = get_email_filter($fUsername);
		$template = "list-filter.tpl";

		include ("../templates/header.tpl");
		include ("../templates/mail_menu.tpl");
		
		include ("../templates/users/$template");
		include ("../templates/footer.tpl");
	}
	
 }



?>