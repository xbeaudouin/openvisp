<?php
//
// File: create-domain.php
//
// Template File: hosting/create-domain.tpl
//
// Template Variables:
//
// tMessage
// tDomain
// tDescription
// tAliases
// tMailboxes
// tMaxquota
// tDefaultaliases
// tAntivirus
// tSpamass
// tVrfyDomain
// tVrfySender
// tGreyListing
// tBackupMx
// tSPF
// tForbidden_helo
//
// Form POST \ GET Variables:
//
// fDomain
// fDescription
// fAliases
// fMailboxes
// fMaxquota
// fDefaultaliases
// fAntivirus
// fSpamass
// fVrfyDomain
// fVrfySender
// fGreyListing
// fBackupMx
// fSPF
// fForbidden_helo
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();

if ( check_domain_admin($SESSID_USERNAME) == false ){
  header ("Location: main.php");
}

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $tAliases = $CONF['aliases'];
   $tMailboxes = $CONF['mailboxes'];
   $tMaxquota = $CONF['maxquota'];
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fDomain         = get_post('fDomain');
   $fDescription    = get_post('fDescription');
   $fAliases        = get_post('fAliases');
   $fMailboxes      = get_post('fMailboxes');
   $fMaxquota       = get_post('fMaxquota');
   $fDefaultaliases = get_post('fDefaultaliases');
   $fAntivirus      = get_post('fAntivirus');
   $fSpamass        = get_post('fSpamass');
   $fBackupmx       = get_post('fBackupmx');
   $fVrfySender     = get_post('fVrfySender');
   $fVrfyDomain     = get_post('fVrfyDomain');
   $fGreyListing    = get_post('fGreyListing');
   $fSPF            = get_post('fSPF');
   $fForbidden_helo = get_post('fForbidden_helo');
   $fImap_enabled   = get_post('fImap_enabled');
   $fPop_enabled    = get_post('fPop_enabled');
   $fSmtp_enabled   = get_post('fSmtp_enabled');
   

	 $tDomain         = $fDomain;
	 $tDescription    = $fDescription;
	 $tAliases        = $fAliases;
	 $tMailboxes      = $fMailboxes;
	 $tMaxquota       = $fMaxquota;
	 $tDefaultaliases = $fDefaultaliases;
	 $tAntivirus      = $fAntivirus;
	 $tSpamass        = $fSpamass;
	 $tBackupmx       = $fBackupmx;
	 $tVrfySender     = $fVrfySender;
	 $tVrfyDomain     = $fVrfyDomain;
	 $tGreyListing    = $fGreyListing;
	 $tSPF            = $fSPF;
	 $tForbidden_helo = $fForbidden_helo;
	 $tImap_enabled   = $fImap_enabled;
	 $tPop_enabled    = $fPop_enabled;
	 $tSmtp_enabled   = $fSmtp_enabled;


	 if ($fAntivirus == "on")    {$fAntivirus = 1;} else {$fAntivirus = 0;}
	 if ($fSpamass == "on")      {$fSpamass = 1;} else {$fSpamass = 0;} 
	 if ($fVrfyDomain == "on")   {$fVrfyDomain = 1;} else {$fVrfyDomain = 0;}
	 if ($fVrfySender == "on")   {$fVrfySender = 1;} else {$fVrfySender = 0;}
	 if ($fGreyListing == "on" ) {$fGreyListing = 1;} else {$fGreyListing = 0;}
	 if ($fSPF == "on")          {$fSPF = 1;} else {$fSPF = 0;}
 	 if ($fBackupmx == "on")     {$fBackupmx = 1;} else {$fBackupmx = 0;}
	 if ($fSmtp_enabled == "on") $fSmtp_enabled = 1;
	 if ($fImap_enabled == "on") $fImap_enabled = 1;
	 if ($fPop_enabled == "on") $fPop_enabled = 1;


	 // Add domain
	 $domain_info = add_domain ( $fDomain, $fDescription, $fAliases, $fMailboxes, $fMaxquota, $fBackupmx, $fAntivirus, $fVrfyDomain, $fVrfySender, $fGreyListing, $fSPF, $fForbidden_helo, $fImap_enabled, $fPop_enabled, $fSmtp_enabled);

	 if ( $domain_info['error'] == 0 ){


		 if ($fAntivirus == 1)
			 { 
				 $fAntivirus = 'N'; 
				 $virus_lover = 'N';
				 $spam_lover = 'N';
				 $banned_files_lover = 'N';
				 $bad_header_lover = 'N';
				 $bypass_banned_checks = 'N';
				 $bypass_header_checks = 'N';
			 } 
		 else 
			 { 
				 $fAntivirus = 'Y';
				 $virus_lover = 'Y';
				 $spam_lover = 'Y';
				 $banned_files_lover = 'Y';
				 $bad_header_lover = 'Y';
				 $bypass_banned_checks = 'Y';
				 $bypass_header_checks = 'Y';
			 }

		 if ($fSpamass == 1) { $fSpamass = 'N'; } else { $fSpamass = 'Y'; }


		 $sql_query = "INSERT INTO policy (domain_id,virus_lover,spam_lover,banned_files_lover,bad_header_lover,bypass_virus_checks,bypass_spam_checks,bypass_banned_checks,bypass_header_checks,spam_modifies_subj,virus_quarantine_to,spam_quarantine_to,banned_quarantine_to,bad_header_quarantine_to,spam_tag_level,spam_tag2_level,spam_kill_level,spam_dsn_cutoff_level,addr_extension_virus,addr_extension_spam,addr_extension_banned,addr_extension_bad_header,warnvirusrecip,warnbannedrecip,warnbadhrecip,newvirus_admin,virus_admin,banned_admin,bad_header_admin,spam_admin,spam_subject_tag,spam_subject_tag2,message_size_limit,banned_rulenames)

VALUES ('".$domain_info['domain_id']."','$virus_lover','$spam_lover','$banned_files_lover','$bad_header_lover','$fAntivirus','$fSpamass','$bypass_banned_checks','$bypass_header_checks','Y','".$CONF['virus_quarantine_to']."','".$CONF['spam_quarantine_to']."','".$CONF['banned_quarantine_to']."','','".$CONF['sa_tag_level']."','".$CONF['sa_tag2_level'] ."','".$CONF['sa_kill_level'] ."','','','','','','N','N','N','','','','','','".$CONF['spam_subject_tag']."','".$CONF['spam_subject_tag2']."','','')";

		 $result = db_query ($sql_query);

		 if ($result['rows'] != 1)
			 {
				 $tMessage = $PALANG['pAdminCreate_domain_result_error2'] . "<br />($fDomain)<br />";
			 }
		 else
			 {
				 $policy_id = $result['inserted_id'];

				 if ($fBackupmx == 1)
					 {
						 $domain_addr = "@" . $fDomain;
						 $result = db_query ("INSERT INTO alias (address,goto,policy_id,created,active) VALUES ('$domain_addr','$domain_addr','".$policy_id."',NOW(),'1')");
					 }
				 if ($fDefaultaliases == "on")
					 {
						 foreach ($CONF['default_aliases'] as $address=>$goto)
							 {
								 $address = $address . "@" . $fDomain;
								 $result = db_query ("INSERT INTO alias (address,goto,created,active,policy_id) VALUES ('$address','$goto',NOW(),'1','".$policy_id."')");
							 }
					 }
				 $tMessage = $PALANG['pAdminCreate_domain_result_succes'] . "<br />($fDomain)</br />";
			 }

   }
	 else
		 {
			 $tMessage = $domain_info['message'];
			 
		 }
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain.tpl");
   include ("../templates/footer.tpl");
 }
?>
