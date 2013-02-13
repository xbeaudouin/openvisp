<?php
//
// File: create-mailbox.php
//
// Template File: create-mailbox.tpl
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
//$user_info->check_quota();


$body_class = 'class="yui3-skin-sam"';

$overview2 = "YES";

if ($_SERVER['REQUEST_METHOD'] == "GET"){

  $tQuota = $CONF['maxquota'];

  $pCreate_mailbox_password_text = $PALANG['pCreate_mailbox_password_text'];
  $pCreate_mailbox_name_text = $PALANG['pCreate_mailbox_name_text'];
  $pCreate_mailbox_quota_text = $PALANG['pCreate_mailbox_quota_text'];

  $tDomain = $_SESSION['sessid']['wdomain'];
  
  $domain_info->fetch_by_domainname($tDomain);
  $user_info->check_domain_access($domain_info->data_domain['id']);

  // if ( $tDomain != NULL ) {
  //   if (!check_owner($SESSID_USERNAME, $tDomain)) {
  //     // Be paranoid. If someone is trying to get acces to a
  //     // a domain that is not in charge, then logout the user
  //     // directly.
  //     header ("Location: logout.php");
  //   }
  // } else {
  //   header ("Location: overview.php");
  // }

  include ("../templates/header.tpl");
  include ("../templates/mail/menu.tpl");
  include ("../templates/mail/create-mailbox.tpl");
  include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

  $pCreate_mailbox_password_text = $PALANG['pCreate_mailbox_password_text'];
  $pCreate_mailbox_name_text = $PALANG['pCreate_mailbox_name_text'];
  $pCreate_mailbox_quota_text = $PALANG['pCreate_mailbox_quota_text'];

  $fUsername  = get_post('fUsername') . "@" . get_post('fDomain');
  $fUsername  = strtolower ($fUsername);
  $fPassword  = get_post('fPassword');
  $fPassword2 = get_post('fPassword2');
  $fName      = get_post('fName');
  $fDomain    = get_post('fDomain');
  $fGreyListing   = get_post('fGreyListing');
  $fQuota  = get_post('fQuota');
  $fActive = get_post('fActive');
  $fSmtp_enabled = get_post('fSmtp_enabled');
  $fMail   = get_post('fMail');
  $fSpamreport   = get_post('fSpamreport');
  $fSpamreport   = get_post('fSpamreport');
  $fImap_enabled   = get_post('fImap_enabled');
  $fPop_enabled   = get_post('fPop_enabled');

  $tName = $fName;
  $tQuota = $fQuota;
  $tDomain = $fDomain;
  $tUsername = $fUsername;

  $domain_info->fetch_by_domainname($fDomain);

  if ($fPassword != $fPassword2){
    $tMessage = $PALANG['pCreate_mailbox_password_text_mismatch'];
  }
  else{
    //string mbx_name, string mbx_info, string mbx_pass, int mbx_quota, int mbx_smtpauth, int mbx_pop3, int mbx_imap)
    $mailbox->add_mailbox($fUsername,$fName,$fPassword,$fQuota,$fSmtpauth,$fPop_enabled,$fImap_enabled);
    $tMessage=$mailbox->last_operation['message'];
    //$tMessage = add_domain_mailbox($fDomain, $fUsername, $fPassword, $fName, $fQuota, $fSmtpauth, $fActive, $fMail, $fSpamreport,$fPop_enabled, $fImap_enabled);
  }



  include ("../templates/header.tpl");
  include ("../templates/mail/menu.tpl");
  include ("../templates/mail/create-mailbox.tpl");
  include ("../templates/footer.tpl");

}
?>
