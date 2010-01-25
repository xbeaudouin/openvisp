<?php
//
// File: edit-active-domain-spamass.php
//
// Template File: users/edit-active-domain-spamass.tpl
//
// Template Variables:
//
// tSatag
// tSavalue
// tSaActive
// tSaModSubj
// tSavalue2
// tSavalueK
//
// Form POST \ GET Variables:
//
// fSatag
// fSavalue
// fSaActive
// fSaModSubj
// fSavalue2
// fSavalueK
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $domain = get_get('domain');
   $domain_policy = get_domain_policy ($domain);

   $tSaActive  = $domain_policy['bypass_spam_checks'];
   $tSaModSubj = $domain_policy['spam_modifies_subj'];
   $tSatag     = $domain_policy['spam_tag_level'];
   $tSavalue   = $domain_policy['spam_subject_tag'];
   $tSavalue2  = $domain_policy['spam_tag2_level'];
   $tSavalueK  = $domain_policy['spam_kill_level'];
 
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-active-domain-spamass.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = get_post('domain');
   
	 $fSaActive = get_post('fSaActive');
   $fSaModSubj = get_post('fSaModSubj');
   if ( $fSaActive != NULL) { $fSaActive = 'N'; } else { $fSaActive = 'Y'; }
   if ( $fSaModSubj != NULL ) { $fSaModSubj = 'Y'; } else { $fSaModSubj = 'N'; }

   $fSatag = get_post('fSatag');
   $fSavalue = get_post('fSavalue');
   $fSavalue2 = get_post('fSavalue2');
   $fSavalueK = get_post('fSavalueK');  

	 $tSaActive  = $fSaActive;
	 $tSaModSubj = $fSaModSubj;
	 $tSatag     = $fSatag;
	 $tSavalue   = $fSavalue;
	 $tSavalue2  = $fSavalue2;
	 $tSavalueK  = $fSavalueK;

   $result = db_query ("UPDATE policy SET bypass_spam_checks='$fSaActive',spam_modifies_subj='$fSaModSubj',spam_subject_tag='$fSavalue',spam_tag_level='$fSatag',spam_tag2_level='$fSavalue2',spam_kill_level='$fSavalueK' WHERE domain='$domain'");
   if ($result['rows'] == 1)
   {
		 header ("Location: list-domain.php");
   }
   else
   {
		 $tMessage = $PALANG['pAdminEdit_active_domain_spamass_result_error'];
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-active-domain-spamass.tpl");
   include ("../templates/footer.tpl");
}
?>
