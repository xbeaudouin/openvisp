<?php
//
// File: edit-mailbox.php
//
// Template File: edit-mailbox.tpl
//
// Template Variables:
//
// tMessage
// tName
// tQuota
//
// Form POST \ GET Variables:
//
// fUsername
// fDomain
// fPassword
// fPassword2
// fName
// fQuota
// fActive
// fSmtpauth
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fUsername = get_get('username');
   $fDomain   = get_get('domain');
   $fGpass   = get_get('gpass');
	 $overview2 = "ok";
	 $tDomain = $fDomain;

   if (check_owner ($SESSID_USERNAME, $fDomain))
   {

		 if ( $fGpass != NULL ){
			 if ($CONF['password_generator'] == ""){
         $fPassword = generate_password ();
			 }
			 else{
				 $fPassword = exec($CONF['password_generator']);
			 }

			 $password = pacrypt ($fPassword);
			 $result = db_query ("UPDATE mailbox SET password='$password'  WHERE username='$fUsername'");
			 if ($result['rows'] != 1)
				 {
					 $tMessage = $PALANG['pEdit_mailbox_update_password'];
				 }
			 else
				 {
					 db_log ($SESSID_USERNAME, $fDomain, "Change Password", $fUsername);
				 }
		 }


		 $result = db_query ("SELECT * FROM mailbox WHERE username='$fUsername'");
		 if ($result['rows'] == 1)
			 {
         $row = db_array ($result['result']);
         $tName = $row['name'];
         $tQuota = $row['quota'] / $CONF['quota_multiplier'];
         $tActive = $row['active'];
         $tSmtpauth = $row['smtp_enabled'];
				 $tPop3_enabled = $row['pop3_enabled'];
				 $tImap_enabled = $row['imap_enabled'];
         $tSpamreport = $row['spamreport'];
				 $tPassword = $row['password'];
				 $tMailfilter = $row['update_filter'];
			 }

		 $tGreylisting = 0;
		 if ( check_policyhosting() )
			 {	$tGreylisting = check_user_greylisting($fUsername);	}

   }
   else
		 {
			 $tMessage = $PALANG['pEdit_mailbox_login_error'];
		 }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/edit-mailbox.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
//   $pEdit_mailbox_password_text = $PALANG['pEdit_mailbox_password_text_error'];
   $pEdit_mailbox_quota_text = $PALANG['pEdit_mailbox_quota_text'];
   
   $fUsername  = get_post('fUsername');
   $fDomain    = get_post('fDomain');
   $fPassword  = get_post('fPassword');
   $fPassword2 = get_post('fPassword2');
   $fName      = get_post('fName');
   $fQuota    = get_post('fQuota');
   $fActive   = get_post('fActive');
   $fSmtpauth = get_post('fSmtpauth');
   $fSpamreport = get_post('fSpamreport');
   $fGreylisting = get_post('fGreylisting');
   $fMailfilter = get_post('fMailfilter');
   $fPop3_enabled = get_post('fPop3_enabled');
   $fImap_enabled = get_post('fImap_enabled');

  
   if (!check_owner ($SESSID_USERNAME, $fDomain) )
   {
      $error = 1;
      $tName = $fName;
      $tQuota = $fQuota;
      $tActive = $fActive;
      $tSmtpauth = $fSmtpauth;
			$tGreylisting = $fGreylisting;
			$tMailfilter = $fMailfilter;
      $tMessage = $PALANG['pEdit_mailbox_domain_error'] . "$fDomain</font>";
   }

   if ($fPassword != $fPassword2)
   {
      $error = 1;
      $tName = $fName;
      $tQuota = $fQuota;
      $tActive = $fActive;
      $tSmtpauth = $fSmtpauth;
			$tGreylisting = $fGreylisting;
			$tMailfilter = $fMailfilter;
      $pEdit_mailbox_password_text = $PALANG['pEdit_mailbox_password_text_error'];
   }

   if (!check_quota ($fQuota, $fDomain))
   {
      $error = 1;
      $tName = $fName;
      $tQuota = $fQuota;
      $tActive = $fActive;
      $tSmtpauth = $fSmtpauth;
			$tGreylisting = $fGreylisting;
			$tMailfilter = $fMailfilter;
      $pEdit_mailbox_quota_text = $PALANG['pEdit_mailbox_quota_text_error'];
   }

   if ($error != 1)
   {
      if (!empty ($fQuota)) $quota = $fQuota * $CONF['quota_multiplier'];
      if ($fActive == "on") $fActive = 1;
      if ($fSmtpauth == "on") $fSmtpauth = 1;
      if ($fGreylisting == "on") $fGreylisting = 1;
      if ($fSpamreport == "on") $fSpamreport = 1;
      if ($fMailfilter == "on") $fMailfilter = 1;
			if ($fPop3_enabled == "on") $fPop3_enabled = 1;
			if ($fImap_enabled == "on") $fImap_enabled = 1;

      if (empty ($fPassword) and empty ($fPassword2))
      {
         $result = db_query ("UPDATE mailbox SET name='$fName',quota='$quota',spamreport='$fSpamreport',update_filter='$fMailfilter',active='$fActive',smtp_enabled='$fSmtpauth', imap_enabled='$fImap_enabled', pop3_enabled='$fPop3_enabled' WHERE username='$fUsername'");
      }
      else
      {
         $password = pacrypt ($fPassword);
         $result = db_query ("UPDATE mailbox SET password='$password',name='$fName',quota='$quota',spamreport='$fSpamreport',update_filter='$fMailfilter',active='$fActive',smtp_enabled='$fSmtpauth', imap_enabled='$fImap_enabled', pop3_enabled='$fPop3_enabled' WHERE username='$fUsername' ");
      }

      if ($result['rows'] != 1)
      {
         $tMessage = $PALANG['pEdit_mailbox_result_error'];
      }
      else
      {

				if ( check_policyhosting() ){
					$result2 = db_query("SELECT * FROM policy WHERE _rcpt = '".$fUsername."'","1","policyd");
					if ($result2['rows'] == 1){
						$row = db_array ($result2['result']);
						if ( $row['_optin'] != $fGreylisting ){
							$result = db_query("UPDATE policy SET _optin='".$fGreylisting."' WHERE _rcpt='".$fUsername."'","1","policyd");
						}
					}
					else{
						$result = db_query("INSERT INTO policy(_rcpt,_optin,_priority) VALUES ('".$fUsername."','".$fGreylisting."','50')","1","policyd");
					}
				}
				
				if ($result['rows'] != 1)
					{
						$tMessage = $PALANG['pEdit_mailbox_result_error'];
					}
				else{

					db_log ($SESSID_USERNAME, $fDomain, "edit mailbox", $fUsername);
					
					header ("Location: overview.php?domain=$fDomain");
					exit;
				}

      }
   }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/edit-mailbox.tpl");
   include ("../templates/footer.tpl");
}
?>
