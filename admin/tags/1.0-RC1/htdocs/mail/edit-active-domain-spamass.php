<?php
//
// File: edit-active-domain-spamass.php
//
// Template File: mail/edit-active-domain-spamass.tpl
//
// Template Variables:
//
// tSatag
// tSavalue
//
// Form POST \ GET Variables:
//
// fSatag
// fSavalue
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $tDomain = get_get('domain');

   if (check_owner ($SESSID_USERNAME, $tDomain)) {
     $domain_policy = get_domain_policy ($tDomain);
     $tSaActive  = $domain_policy['bypass_spam_checks'];
     $tSaModSubj = $domain_policy['spam_modifies_subj'];
     $tSatag     = $domain_policy['spam_tag_level'];
     $tSavalue   = $domain_policy['spam_subject_tag'];
     $tSavalue2  = $domain_policy['spam_tag2_level'];
     $tSavalueK  = $domain_policy['spam_kill_level'];
      
     include ("../templates/header.tpl");
     include ("../templates/mail/menu.tpl");
     include ("../templates/mail/edit-active-domain-spamass.tpl");
     include ("../templates/footer.tpl");
   } else {
     header( "Location: ../logout.php");
     exit;
   }
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = get_post('fDomain');

   if (!check_owner($SESSID_USERNAME, $domain)) {
     header ("Location: ../logout.php");
     exit;
   }
   
   $fSatag     = get_post('fSatag');
   $fSavalue   = get_post('fSavalue');
   $fSaActive  = get_post('fSaActive');
   $fSaModSubj = get_post('fSaModSubj');
   $fSavalue2  = get_post('fSavalue2');
   $fSavalueK  = get_post('fSavalueK');

	 if ($fSaActive == "on") {$fSaActive = 'N';} else {$fSaActive = 'Y';}
	 if ($fSaModSubj == "on") {$fSaModSubj = 'Y';} else {$fSaModSubj = 'N';}

	 $tSaActive  = $fSaActive;
	 $tSaModSubj = $fSaModSubj;
	 $tSatag     = $fSatag;
	 $tSavalue   = $fSavalue;
	 $tSavalue2  = $fSavalue2;
	 $tSavalueK  = $fSavalueK;




   $result = db_query ("UPDATE policy SET bypass_spam_checks='$fSaActive',spam_modifies_subj='$fSaModSubj',spam_subject_tag='$fSavalue',spam_tag_level='$fSatag',spam_tag2_level='$fSavalue2',spam_kill_level='$fSavalueK' WHERE domain='$domain'");

  // This is not the right place for thaT... please fix this !
	 //	 $MYSQL_ERRNO = mysql_errno();
	 //	 $MYSQL_ERROR = mysql_error();
	 //	 $errstr = "MySQL error: $MYSQL_ERRNO : $MYSQL_ERROR";

	 //	 print "$errstr </br>\n DBG UPDATE policy SET bypass_spam_checks='$fSaActive',spam_modifies_subj='$fSaModSubj',spam_subject_tag='$fSavalue',spam_tag_level='$fSatag',spam_tag2_level='$fSavalue2',spam_kill_level='$fSavalueK' WHERE domain='$domain'<br>";

	 //	 print "DBG 3 ".$result['rows']."</br>\n ";
   if ($result['rows'] == 1)
   {
		 	header ("Location: overview.php");
   }
   else
   {
	$tMessage = $PALANG['pAdminEdit_active_domain_spamass_result_error'];
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/edit-active-domain-spamass.tpl");
   include ("../templates/footer.tpl");
}
?>
