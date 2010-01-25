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
   
   if (($fDomain != NULL) && domain_exist($fDomain) != false)
   {
      $error = 1;
      $tDomain         = get_post('fDomain');
      $tDescription    = get_post('fDescription');
      $tAliases        = get_post('fAliases');
      $tMailboxes      = get_post('fMailboxes');
      $tMaxquota       = get_post('fMaxquota');
      $tDefaultaliases = get_post('fDefaultaliases');
      $tAntivirus      = get_post('fAntivirus');
      $tSpamass        = get_post('fSpamass');
      $tBackupmx       = get_post('fBackupmx');
      $tVrfySender     = get_post('fVrfySender');
      $tVrfyDomain     = get_post('fVrfyDomain');
      $tGreyListing    = get_post('fGreyListing');
      $tGreyListing    = get_post('fSPF');
			$tForbidden_helo = get_post('fForbidden_helo');
      $pAdminCreate_domain_domain_text = $PALANG['pAdminCreate_domain_domain_text_error'];
   }
      
   if ($error != 1)
   {
      $tAliases = $CONF['aliases'];
      $tMailboxes = $CONF['mailboxes'];
      $tMaxquota = $CONF['maxquota'];
			$tMessage = "";

      if ($fAntivirus == "on")   $fAntivirus = 1;
      if ($fSpamass == "on")     $fSpamass = 1;
      if ($fVrfyDomain == "on")  $fVrfyDomain = 1;
      if ($fVrfySender == "on")  $fVrfySender = 1;
      if ($fGreyListing == "on") $fGreyListing = 1;
      if ($fSPF == "on")         $fSPF = 1;

      if ($fBackupmx == "on")
      {
         $fAliases = 0;
         $fMailboxes = 0;
         $fMaxquota = 0;
         $fBackupmx = 1;
      }

      // Add domain
      $result = db_query ("INSERT INTO domain (domain,description,aliases,mailboxes,maxquota,backupmx,antivirus,vrfydomain,vrfysender,greylist,spf,created,modified) VALUES ('$fDomain','$fDescription','$fAliases','$fMailboxes','$fMaxquota','$fBackupmx','$fAntivirus','$fVrfyDomain','$fVrfySender','$fGreyListing','$fSPF',NOW(),NOW())");
      if ($result['rows'] != 1)
      {
         $tMessage = $PALANG['pAdminCreate_domain_result_error'] . "<br />($fDomain)<br />";
      }
      // Add domain admin
      $result = db_query ("INSERT INTO domain_admins (username,domain,created,active) VALUES ('$SESSID_USERNAME','$fDomain',NOW(),'1')");
      if ($result['rows'] != 1)
      {
         $tMessage = $PALANG['pAdminCreate_domain_result_error3'] . "<br />($fDomain)<br />";
      }

			$goto = preg_replace ('/\r\n/', ',', $fForbidden_helo);
			$array = preg_split ('/,/', $goto);

			for ($i = 0; $i < sizeof ($array); $i++)
				{
					if (empty ($array[$i])) continue;
					add_bl_helo($array[$i].".".$fDomain);
				}
			 add_bl_helo($fDomain);

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

      $result = db_query ("INSERT INTO policy (domain,virus_lover,spam_lover,banned_files_lover,bad_header_lover,bypass_virus_checks,bypass_spam_checks,bypass_banned_checks,bypass_header_checks,spam_modifies_subj,virus_quarantine_to,spam_quarantine_to,banned_quarantine_to,bad_header_quarantine_to,spam_tag_level,spam_tag2_level,spam_kill_level,spam_dsn_cutoff_level,addr_extension_virus,addr_extension_spam,addr_extension_banned,addr_extension_bad_header,warnvirusrecip,warnbannedrecip,warnbadhrecip,newvirus_admin,virus_admin,banned_admin,bad_header_admin,spam_admin,spam_subject_tag,spam_subject_tag2,message_size_limit,banned_rulenames) VALUES ('$fDomain','$virus_lover','$spam_lover','$banned_files_lover','$bad_header_lover','$fAntivirus','$fSpamass','$bypass_banned_checks','$bypass_header_checks','Y','".$CONF['virus_quarantine_to']."','".$CONF['spam_quarantine_to']."','".$CONF['banned_quarantine_to']."','','".$CONF['sa_tag_level']."','".$CONF['sa_tag2_level'] ."','".$CONF['sa_kill_level'] ."','','','','','','N','N','N','','','','','','".$CONF['spam_subject_tag']."','".$CONF['spam_subject_tag2']."','','')");

      if ($result['rows'] != 1)
      {
	       $tMessage .= $PALANG['pAdminCreate_domain_result_error2'] . "<br />($fDomain)<br />";
      }
      else
      {
				//		$tMessage = "";
		
      	$result = db_query ("SELECT id FROM policy WHERE domain = '$fDomain'");
      	if ($result['rows'] == 1)
      	{
      	  $row = db_array ($result['result']);
      	  $id = $row['id'];
      	}
      	if ($fBackupmx == 1)
      	{
      	  $domain_addr = "@" . $fDomain;
      	  $result = db_query ("INSERT INTO alias (address,goto,domain,created,modified,active,policy_id) VALUES ('$domain_addr','$domain_addr','$fDomain',NOW(),NOW(),'1','$id')");
      	}
      	if ( $fGreyListing ){
      		$result = add_domain_greylisting($fDomain, $fGreyListing);
      		$tMessage .= $PALANG['pAdminCreate_domain_result_succes'] . "<br />($fDomain)</br />";
      	}
				if ($fDefaultaliases == "on")
					{
						foreach ($CONF['default_aliases'] as $address=>$goto)
							{
								$address = $address . "@" . $fDomain;
								$result = db_query ("INSERT INTO alias (address,goto,domain,created,modified,active,policy_id) VALUES ('$address','$goto','$fDomain',NOW(),NOW(),'1','$id')");
							}
					}
				$tMessage .= $PALANG['pAdminCreate_domain_result_succes'] . "<br />($fDomain)</br />";
      }
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain.tpl");
   include ("../templates/footer.tpl");
}
?>
