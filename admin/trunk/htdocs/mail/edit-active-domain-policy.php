<?php
//
// File: edit-domain-policy.php
//
// Template File: admin_edit-active-domain-policy.tpl
//
// Template Variables:
//
// tSatag
// tSavalue
// tSaActive
// tSaModSubj
// tSavalue2
// tSavalueK
// tAVactive
// tAVheader
// tAVbanned
// tWarnVRcp
// tWarnBRcp
// tWarnBHRcp
//
// Form POST \ GET Variables:
//
// fSatag
// fSavalue
// fSaActive
// fSaModSubj
// fSavalue2
// fSavalueK
// fAVactive
// fAVheader
// fAVbanned
// fWarnVRcp
// fWarnBRcp
// fWarnBHRcp
//

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");
require_once ("../lib/mail.class.php");

$ovadb = new DB();
$user_info = new USER($ovadb);
$ova_info = new OVA($ovadb); 
$mailbox = new MAIL($ovadb); 
$domain_info = new DOMAIN($ovadb);

$SESSID_USERNAME = $ova_info->check_session();

$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_quota_status();
$user_info->check_mail_admin();
//$user_info->check_quota();


$body_class = 'class="yui3-skin-sam"';


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $domain = get_get('domain');
   $domain_info->fetch_by_domainname($domain);
   $domain_info->fetch_policy();
   //$domain_policy = get_domain_policy($domain);

   // $tSaActive  = $domain_policy['bypass_spam_checks'];
   // $tSaModSubj = $domain_policy['spam_modifies_subj'];
   // $tSatag     = $domain_policy['spam_tag_level'];
   // $tSavalue   = $domain_policy['spam_subject_tag'];
   // $tSavalue2  = $domain_policy['spam_tag2_level'];
   // $tSavalueK  = $domain_policy['spam_kill_level'];
 
   // $tAVactive  = $domain_policy['bypass_virus_checks'];
   // $tAVheader  = $domain_policy['bypass_header_checks'];

   // $tAVbanned  = $domain_policy['bypass_banned_checks'];
   // $tWarnVRcp  = $domain_policy['warnvirusrecip'];
   // $tWarnBRcp  = $domain_policy['warnbannedrecip'];
   // $tWarnBHRcp = $domain_policy['warnbadhrecip'];

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/users/edit-active-domain-policy.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = escape_string($_GET['domain']);
   
	 $fSaActive = get_post('fSaActive');
	 $tSaActive = get_post('fSaActive');
   $fSaModSubj = get_post('fSaModSubj');
   $tSaModSubj = get_post('fSaModSubj');
   $fSatag = get_post('fSatag');
   $tSatag = get_post('fSatag');
   $fSavalue = get_post('fSavalue');
   $tSavalue = get_post('fSavalue');
   $fSavalue2 = get_post('fSavalue2');
   $tSavalue2 = get_post('fSavalue2');
   $fSavalueK = get_post('fSavalueK');  
   $tSavalueK = get_post('fSavalueK');  

   $fAVactive = get_post('fAVactive');
   $tAVactive = get_post('fAVactive');
   $fAVheader = get_post('fAVheader');
   $tAVheader = get_post('fAVheader');
   $fAVbanned = get_post('fAVbanned');
   $tAVbanned = get_post('fAVbanned');
   $tWarnVRcp = get_post('fWarnVRcp');
   $fWarnVRcp = get_post('fWarnVRcp');
   $tWarnBHRcp = get_post('fWarnBHRcp');
   $fWarnBHRcp = get_post('fWarnBHRcp');
   $tWarnBRcp = get_post('fWarnBRcp');
   $fWarnBRcp = get_post('fWarnBRcp');

   $fSaActive = convert_on_to_no($fSaActive);
   $tSaActive = convert_on_to_no($tSaActive);
   $fSaModSubj = convert_on_to_no($fSaModSubj);
   $tSaModSubj = convert_on_to_no($tSaModSubj);
	 $fAVactive = convert_on_to_no($fAVactive);
	 $tAVactive = convert_on_to_no($tAVactive);
	 $fAVheader = convert_on_to_no($fAVheader);
	 $tAVheader = convert_on_to_no($tAVheader);
	 $fAVbanned = convert_on_to_no($fAVbanned);
	 $tAVbanned = convert_on_to_no($tAVbanned);
   $tWarnVRcp = convert_on_to_no($tWarnVRcp);
   $fWarnVRcp = convert_on_to_no($fWarnVRcp);
   $tWarnBHRcp = convert_on_to_no($tWarnBHRcp);
   $fWarnBHRcp = convert_on_to_no($fWarnBHRcp);
   $tWarnBRcp = convert_on_to_no($tWarnBRcp);
   $fWarnBRcp = convert_on_to_no($fWarnBRcp);

   $result = db_query ("
UPDATE policy, domain
SET bypass_spam_checks='$fSaActive',spam_modifies_subj='$fSaModSubj',spam_subject_tag='$fSavalue',spam_tag_level='$fSatag',spam_tag2_level='$fSavalue2',spam_kill_level='$fSavalueK',
bypass_virus_checks='$fAVactive',bypass_header_checks='$fAVheader',bypass_banned_checks='$fAVbanned',
warnvirusrecip='$fWarnVRcp', warnbannedrecip='$fWarnBRcp',warnbadhrecip='$fWarnBHRcp'
WHERE domain.domain='$domain'
AND domain.id=policy.domain_id"
);

   if ($result['rows'] == 1)
   {
		 //header ("Location: list-policy.php");
		 header ("Location: overview.php");
   }
   else
   {
		 $tMessage = $PALANG['pAdminEdit_active_domain_policy_result_error'];
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/users/edit-active-domain-policy.tpl");
   include ("../templates/footer.tpl");
}
?>
