<?php
//
// File: import-domain.php
//
// Template File: admin_import-domain.tpl
//
//
// Template Variables:
//
// tMessage
// tUsername
// tDomains
//
// Form POST \ GET Variables:
//
// fFilename
//

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

include ("../templates/header.tpl");
include ("../templates/admin_menu.tpl");


if ($_SERVER['REQUEST_METHOD'] == "GET")
	{
		$pAdminCreate_admin_username_text = $PALANG['pAdminCreate_admin_username_text'];
		$tDomains[] = "";
		
		include ("../templates/admin_import-domain.tpl");
	}

if ($_SERVER['REQUEST_METHOD'] == "POST"){


        $uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['domain_file']['name']);                                                                                                

	if (move_uploaded_file($_FILES['domain_file']['tmp_name'], $uploadfile)) {

		$lines = file($uploadfile);
		foreach ($lines as $line_num => $line) {
			$info = explode(";", $line);
			$fDomain = $info[0]; chop ($fDomain);
			if ( isset($info[1]) ) { $fBackupMx = $info[1]; } else { $fBackupMx=0;}
			if ( isset($info[2]) ) { $fMailboxes = $info[2]; } else { $fMailboxes=$CONF['mailboxes'];}
			if ( isset($info[3]) ) { $fAliases = $info[3]; } else { $fAliases=$CONF['aliases'];}
			if ( isset($info[4]) ) { $fMaxquota = $info[4]; }  else { $fMaxquota=$CONF['maxquota'];}
			if ( isset($info[5]) ) { $fDescription = $info[5]; } else { $fDescription="";}
			if ( isset($info[6]) ) { $fAntivirus = $info[6]; } else { $fAntivirus=0;}
			if ( isset($info[7]) ) { $fSpamass = $info[7]; } else { $fSpamass=0;}
			if ( isset($info[8]) ) { $fVrfySender = $info[8]; }  else { $fVrfySender=0;}
			if ( isset($info[9]) ) { $fVrfyDomain = $info[9]; } else { $fVrfyDomain=0;}
			if ( isset($info[10]) ) { $fGreyListing = $info[10]; } else { $fGreyListing=0;}
			if ( isset($info[11]) ) { $fSPF = $info[11]; } else { $fSPF=0;}
			if ( isset($info[12]) ) { $fActive = $info[12]; } else { $fActive=0;}
			//			print "$fDomain, $fBackupMx<br>";

			if ( domain_exist ($fDomain) ){
				$error = 1;
				$pAdminCreate_domain_domain_text = $PALANG['pAdminCreate_domain_domain_text_error'];
				print "$fDomain : $pAdminCreate_domain_domain_text<br />";

			}
			else {
				if ($fBackupmx == "1")
					{
						$fAliases = 0;
						$fMailboxes = 0;
						$fMaxquota = 0;
					}
				$result = db_query ("INSERT INTO domain (domain,description,aliases,mailboxes,maxquota,backupmx,antivirus,spamass,vrfydomain,vrfysender,greylist,spf,created,modified,active) VALUES ('$fDomain','$fDescription','$fAliases','$fMailboxes','$fMaxquota','$fBackupmx','$fAntivirus','$fSpamass','$fVrfyDomain','$fVrfySender','$fGreyListing','$fSPF',NOW(),NOW(),'$fActive')");
				if ($result['rows'] != 1)
					{
						$tMessage = $PALANG['pAdminCreate_domain_result_error'] . "<br />($fDomain)<br />";
					}


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
						$tMessage = $PALANG['pAdminCreate_domain_result_error2'] . "<br />($fDomain)<br />";
					}
				else
					{
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
						if ($fDefaultaliases == "on")
							{
								foreach ($CONF['default_aliases'] as $address=>$goto)
									{
										$address = $address . "@" . $fDomain;
										$result = db_query ("INSERT INTO alias (address,goto,domain,created,modified,active,policy_id) VALUES ('$address','$goto','$fDomain',NOW(),NOW(),'1','$id')");
									}
							}
						$tMessage = $PALANG['pAdminCreate_domain_result_succes'] . "<br />($fDomain)</br />";
					}
			}
			print $tMessage."<br />";
		}
		//	include ("../templates/admin_create-domain.tpl");

	}
	$tMessage="TT";
	include ("../templates/footer.tpl");

//		phpinfo();

}


?>
