<?php

	//
	// File: add-filter.php
	//
	// Template : add-filter.tpl
	//
	// Form POST \ GET Variables:
	//
	//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");
include_once('Net/IMAP.php');

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET"){

	$fUsername = $SESSID_USERNAME;
	$template = "add-filter.tpl";
	$table_fieldfilter = get_field_filter();
	$table_fieldaction = get_field_action();

	$result = db_query ("SELECT mailbox.*, domain.* FROM mailbox, domain WHERE mailbox.username='$fUsername' AND mailbox.domain=domain.domain");
	if ($result['rows'] == 1)
		{
			$row = db_array ($result['result']);
			$tPassuser = $row['password'];
			$tImapsrv = $row['pdf_imap'];
		}

	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	include ("../templates/users/$template");
	include ("../templates/footer.tpl");
	

 }

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	$fUsername = $SESSID_USERNAME;
	$fField = get_post('fField');
	$fExecOrder = get_post('fExecOrder');
	$fFilterName = get_post('fFilterName');
	$fFieldValue = get_post('fFieldValue');
	$fComment = get_post('fComment');
	$fAction = get_post('fAction');
	$fActionname = get_mail_action($fAction);

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


	$template = "add-filter.tpl";

	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	
	$result = add_email_filter($fUsername,$fField, $fFieldValue, $fExecOrder, $fFilterName, $fAction, $fDestination, $fComment);

	if ($result['rows'] == 1)
		{
			db_log ($CONF['admin_email'], $fDomain, "add filter", "$fUsername / $fExecOrder, $fFilterName, $fField, $fFieldValue, $fAction, $fDestination" );
			update_email_filter_status($fUsername);
			$tMessage = $PALANG['pEdit_add_filter_succes'] . "</br>" . $PALANG['pEdit_mailbox_filter_succes'];
		}
	else{
		$tMessage = $PALANG['pEdit_add_filter_error'];
	}


	include ("../templates/users/$template");
	include ("../templates/footer.tpl");

 }





?>