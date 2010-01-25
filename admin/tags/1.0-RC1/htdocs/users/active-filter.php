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

	if (!check_filter_owner ($SESSID_USERNAME, $fNum))
		{
      $error = 1;
      $tMessage = $PALANG['pEdit_update_filter_error'] . "!";
		}
	else
		{
      $result = db_query ("UPDATE filter SET active=1-active WHERE email='$fUsername' AND num='$fNum'");
      if ($result['rows'] != 1)
				{
					$error = 1;
					
					$tMessage = $PALANG['pEdit_update_filter_error'];
				}
      else
				{
					db_log ($SESSID_USERNAME, $fDomain, "update filter", "$fUsername ");
				}
		}
	
	if ($error != 1)
		{
			header ("Location: list-filter.php");
			exit;
		}
	
	
 }


?>