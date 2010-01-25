<?php
//
// File: edit-domain.php
//
// Template File: admin_edit-domain.tpl
//
// Template Variables:
//
// tDescription
// tAliases
// tMailboxes
// tMaxquota
// tActive
// tAntivirus
// tSaActive
// tVrfyDomain
// tVrfySender
// tSPF
// tGreyListing
//
// Form POST \ GET Variables:
//
// fDescription
// fAliases
// fMailboxes
// fMaxquota
// fActive
// fAntivirus
// fSaActive
// fVrfyDomain
// fVrfySender
// fSPF
// fGreyListing
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $domain = get_get('domain');
   $domain_properties = get_domain_properties ($domain);
	 $domain_policy = get_domain_policy($domain);

   $tDescription = $domain_properties['description'];
   $tAliases     = $domain_properties['aliases'];
   $tMailboxes   = $domain_properties['mailboxes'];
   $tFtpaccount  = $domain_properties['ftp_account'];
   $tDbusers     = $domain_properties['db_users'];
   $tDbquota     = $domain_properties['db_quota'];
   $tDbcount     = $domain_properties['db_count'];
   $tMaxquota    = $domain_properties['maxquota'];
   $tBackupmx    = $domain_properties['backupmx'];
   $tActive      = $domain_properties['active'];
   $tAntivirus   = $domain_properties['antivirus'];
   $tSaActive    = $domain_policy['bypass_spam_checks'];
   $tVrfyDomain  = $domain_properties['vrfydomain'];
   $tVrfySender  = $domain_properties['vrfysender'];
   $tSPF         = $domain_properties['spf'];
   $tPdf_Pop     = $domain_properties['pdf_pop'];
   $tPdf_Imap    = $domain_properties['pdf_imap'];
   $tPdf_Smtp    = $domain_properties['pdf_smtp'];
   $tPdf_Webmail = $domain_properties['pdf_webmail'];
   $tPdf_Custadd = $domain_properties['pdf_custadd'];
   $tTransport   = $domain_properties['transport'];
   $tPaid        = $domain_properties['paid'];
   $tImap_enabled = $domain_properties['imap_enabled'];
   $tPop3_enabled = $domain_properties['pop3_enabled'];
   $tSmtp_enabled = $domain_properties['smtp_enabled'];

	 if ( check_policyhosting() ){
		 $tGreyListing = check_domain_greylisting($domain);
	 }
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-domain.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = get_post('domain');
	 $action = get_post('action');
	 $result_text = "";
	 $domain_id = get_domain_id($domain);

	 if ( $action != NULL ){

		 require ("../lib/hosting.inc.php");

		 if ( $action == "lock" ){
			 $result_text .= modify_domain_lock($domain_id,"0")."<br/>";
			 $result_text .= modify_domain_ftp($domain_id,"0")."<br/>";
			 $result_text .= modify_domain_mailbox($domain_id,"0")."<br/>"; 
			 $result_text .= modify_domain_whost($domain_id,"0")."<br/>"; 
		 }

		 if ( $action == "unlock" ){
			 $result_text .= modify_domain_lock($domain_id,"1")."<br/>";
			 $result_text .= modify_domain_ftp($domain_id,"1")."<br/>";
			 $result_text .= modify_domain_mailbox($domain_id,"1")."<br/>"; 
			 $result_text .= modify_domain_whost($domain_id,"1")."<br/>"; 
		 }

		 include ("../templates/header.tpl");
		 include ("../templates/users/menu.tpl");
		 print $result_text;
		 include ("../templates/footer.tpl");

	 }
	 
	 else {

		 $fDescription = get_post('fDescription');
		 $fAliases     = get_post('fAliases');
		 $fMailboxes   = get_post('fMailboxes');
		 $fFtpaccount  = get_post('fFtpaccount');
		 $fDbquota     = get_post('fDbquota');
		 $fDbusers     = get_post('fDbusers');
		 $fDbcount     = get_post('fDbcount');
		 $fMaxquota    = get_post('fMaxquota');
		 $fBackupmx    = get_post('fBackupmx');
		 $fActive      = get_post('fActive'); 
		 $fAntivirus   = get_post('fAntivirus');
		 $fSaActive    = get_post('fSaActive');
		 $fSaActive    = get_post('fSaActive');
		 $fVrfyDomain  = get_post('fVrfyDomain');
		 $fVrfySender  = get_post('fVrfySender');
		 $fSPF         = get_post('fSPF'); 
		 $fPdf_Pop     = get_post('fPdf_Pop');
		 $fPdf_Imap    = get_post('fPdf_Imap');
		 $fPdf_Smtp    = get_post('fPdf_Smtp'); 
		 $fPdf_Webmail = get_post('fPdf_Webmail');
		 $fPdf_Address = get_post('fPdf_Address');
		 $fGreyListing = get_post('fGreyListing');
		 $fTransport   = get_post('fTransport');
		 $fImap_enabled = get_post('fImap_enabled');
		 $fPop3_enabled = get_post('fPop3_enabled');
		 $fSmtp_enabled = get_post('fSmtp_enabled');

		 
		 if ($fAntivirus == "on")   $fAntivirus = 1;
		 if ($fSaActive == "on"){   $fSaActive = 'Y';} else {$fSaActive = 'N';}
		 if ($fVrfyDomain == "on")  $fVrfyDomain = 1;
		 if ($fVrfySender == "on")  $fVrfySender = 1;
		 if ($fSPF == "on")         $fSPF = 1;
		 if ($fGreyListing == "on") $fGreyListing = 1;
		 if ($fImap_enabled == "on") $fImap_enabled = 1;
		 if ($fPop3_enabled == "on") $fPop3_enabled = 1;
		 if ($fSmtp_enabled == "on") $fSmtp_enabled = 1;

		 if ($fBackupmx == "on")
			 {
				 $fAliases = 0;
				 $fMailboxes = 0;
				 $fMaxquota = 0;
				 $fBackupmx = 1;
			 }

		 if ($fActive == "on") { $fActive = 1; }

		 $DB_SQL = "";
   
		 if ( check_dbhosting() ){
			 $DB_SQL=", db_count='$fDbcount', db_users='$fDbusers', db_quota='$fDbquota'";
		 }

		 $sql_query = "UPDATE domain
SET description='$fDescription', aliases='$fAliases', mailboxes='$fMailboxes', maxquota='$fMaxquota',
backupmx='$fBackupmx', active='$fActive', modified=NOW(), antivirus='$fAntivirus', vrfydomain='$fVrfyDomain',
vrfysender='$fVrfySender', spf='$fSPF', pdf_pop='$fPdf_Pop', pdf_imap='$fPdf_Imap', pdf_smtp='$fPdf_Smtp',
pop3_enabled='$fPop3_enabled', imap_enabled='$fImap_enabled', smtp_enabled='$fSmtp_enabled', 
pdf_webmail='$fPdf_Webmail', pdf_custadd='$fPdf_Address', transport='$fTransport', ftp_account='$fFtpaccount' $DB_SQL
WHERE domain='$domain'";

		 $result = db_query ($sql_query);
		 if ($result['rows'] == 1)
			 {
				 if ( check_policyhosting() ){
					 $result2 = db_query("SELECT * FROM policy WHERE _rcpt='@".$domain."'","1","policyd");
					 if ($result2['rows'] == 1){
						 $result = db_query("UPDATE policy SET _optin='".$fGreyListing."' WHERE _rcpt='@".$domain."'","1","policyd");
					 }
					 else{
						 $result = db_query("INSERT INTO policy(_rcpt,_optin,_priority) VALUES ('@".$domain."','".$fGreyListing."','10')","1","policyd");
					 }
				 }

				 header ("Location: list-domain.php");
			 }
		 else
			 {
				 $tMessage = $PALANG['pAdminEdit_domain_result_error'];
			 }

		 include ("../templates/header.tpl");
		 include ("../templates/users/menu.tpl");
		 include ("../templates/users/edit-domain.tpl");
		 include ("../templates/footer.tpl");

	 }


}
?>
