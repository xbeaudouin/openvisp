<?php
//
// File: import-mailbox.php
//
// Template File: mail/import-mailbox.tpl
//
// Template Variables:
//
// tMessage
// tUsername
// tName
// tQuota
// tDomain
//
// Form POST \ GET Variables:
//
// fUsername
// fPassword
// fPassword2
// fName
// fQuota
// fDomain
// fActive
// fMail
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session ();
$list_domains = list_local_domains_for_admin ($SESSID_USERNAME);
$overview2 = "YES";

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $pCreate_mailbox_password_text = $PALANG['pCreate_mailbox_password_text'];
   $pCreate_mailbox_name_text = $PALANG['pCreate_mailbox_name_text'];
   $pCreate_mailbox_quota_text = $PALANG['pCreate_mailbox_quota_text'];

	 $tDomain = get_get('domain');

   if ( $tDomain != NULL ) {

     if (!check_owner($SESSID_USERNAME, $tDomain)) {
       // Be paranoid. If someone is trying to get acces to a
       // a domain that is not in charge, then logout the user
       // directly.
       header ("Location: ../logout.php");
     }
   } else {
     header ("Location: overview.php");
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/import-mailbox.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

	$fDomain=get_post('fDomain');

	$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['user_file']['name']);

	if (move_uploaded_file($_FILES['user_file']['tmp_name'], $uploadfile)) {

		$lines = file($uploadfile);
		foreach ($lines as $line_num => $line) {
			$line = chop ($line);

			/* { print "VIDE\n<br/>";next();} */

			if (!eregi("^$",$line)){
				$info = explode(";", $line);
				chop($info[3]);

				if ( isset($info[0]) ) { $fFirstName = $info[0]; } else { $fFirstName="";}
				if ( isset($info[1]) ) { $fLastName = $info[1]; } else { $fLastName="";}
				$fName=$fLastName." ".$fFirstName;
				if ( isset($info[2]) and ( $info[2] != "\n" ) ){ $fPassword = $info[2]; } else {$fPassword = "";}
				if ( $CONF['force_password'] == "YES" ){
					$fPassword="";
				}

				if ( isset($info[3]) ) { 
					if ( eregi("@",$info[3] )){
						$array = preg_split ('/@/', $info[3]);
						$info[3] = $array[0];
					}
					$fUsername = chop(escape_string($info[3])) . "@" .  $fDomain;
				}
				else { $fUsername = escape_string($fFirstName) . "." . escape_string($fLastName) . "@" .  get_post('fDomain'); }

				if ( isset($info[4]) and ( $info[4] != "\n" )   ) { $fQuota = $info[4]; }  else { $fQuota=$CONF['maxquota'];}

				if ( isset($info[5]) and ( $info[5] != "\n" )  ) { $fActive = $info[5]; } else { $fActive="1";}

				if ( isset($info[6]) and ( $info[6] != "\n" )  ) { $fMail = $info[6]; } else { $fMail="1";}

				if ( isset($info[7]) and ( $info[7] != "\n" )  ) { $fSmtpAuthActivated = $info[7]; } else { $fSmtpAuthActivated=0;}		

				$tUsername = $fUsername;
				$tName = $fName;
				$tQuota = $fQuota;
				$tDomain = $fDomain;

				$tMessage .= add_domain_mailbox($fDomain,$fUsername,$fPassword,$fName,$fQuota,$fSmtpAuthActivated,$fActive,$fMail);

			}

		}

	}



	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");

	//print $pImport_Error."<br />";
	print $tMessage;

	//	include ("./templates/import-mailbox.tpl");
	print "<br/><a href=\"overview.php?domain=$fDomain\">".$PALANG['pImport_Users_return']."</a><br/>";
	include ("../templates/footer.tpl");
 }
?>
