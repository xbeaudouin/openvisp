<?php

	//
	// File: edit-filter.php
	//
	// Template : edit-filter.tpl
	//
	// Form POST \ GET Variables:
	//
	// fNum
	//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");
require_once('Net/IMAP.php');

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fNum = get_get('num');
	$fUsername = $SESSID_USERNAME;

	if ( check_filter_owner($fUsername,$fNum) ){

		$table_filter = get_email_filter($fUsername,$fNum);
		$table_fieldfilter = get_field_filter();
		$table_fieldaction = get_field_action();
		$fActionname = get_mail_action($table_filter[0]['filteraction_id']);

		$sql_query = "SELECT mailbox.*, domain.*
FROM mailbox, domain
WHERE mailbox.username='$fUsername'
AND mailbox.domain_id=domain.id";

		$result = db_query ($sql_query);
		if ($result['rows'] == 1)
			{
				$row = db_array ($result['result']);
				$tPassuser = $row['password'];
				$tImapsrv = $row['pdf_imap'];
			}

		$template = "edit-filter.tpl";



		include ("../templates/header.tpl");
		include ("../templates/mail/menu.tpl");
		include ("../templates/users/$template");
		include ("../templates/footer.tpl");
	}
	else{
		header("Location: list-filter.php");
	}

 }

if ($_SERVER['REQUEST_METHOD'] == "POST" ){
	
	$fUsername = $SESSID_USERNAME;
	$fNum = get_post('fFilterNum');

	if ( check_filter_owner($fUsername,$fNum) ){

		$fExecOrder = get_post('fExecOrder');
		$fFilterName = get_post('fFilterName');
		$fField = get_post('fField');
		$fFieldvalue = get_post('fFieldValue');
		$fAction = get_post('fAction');
		$fActionname = get_mail_action($fAction);

		$domain=explode("@",$fUsername);
		
		if ( $fActionname == "forward" ){
			$fDestination = get_post('fDestination_email');
		}
		elseif ( $fActionname == "archive" ){
			$fArchive_format = get_post('fArchive_format');
			$fDestination = get_post('fDestination')."/".$fArchive_format;
		}
		else{
			$fDestination = get_post('fDestination');
		}
		$fComments = get_post('fComments');
		$fActive = get_post('fActive');

		$result = update_filter($fNum, $fExecOrder, $fFilterName, $fField, $fFieldvalue, $fAction, $fDestination, $fActive, $fComments);

		if ($result['rows'] != 1)
			{
				$tMessage = $PALANG['pEdit_update_filter_error'];
			}
		else
			{
				db_log ($CONF['admin_email'], $domain['1'], "update filter", "$fUsername / $fExecOrder, $fFilterName, $fField, $fFieldvalue, $fAction, $fDestination, $fActive" );
				$tMessage = $PALANG['pEdit_update_filter_succes'];
				
				$result2=update_email_filter_status($fUsername);
				if ($result2['rows'] == 1){
					$tMessage .= " </br> " . $PALANG['pEdit_mailbox_filter_succes'];
				}
				else{
					$tMessage .= " </br> " . $PALANG['pEdit_mailbox_filter_error'];
				}
				header ("Location: list-filter.php");
			}

	}
 
	else{
		header("Location: list-filter.php");
	}

 }


?>