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


require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");


$ovadb = new DB();
$user_info = new USER($ovadb);
$ova = new OVA($ovadb); 

$SESSID_USERNAME = $ova->check_session();

$user_info->fetch_info($SESSID_USERNAME);

$user_info->check_domain_admin();
$user_info->fetch_active_domains();

$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();


$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

$body_class = 'class="yui3-skin-sam"';

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

   $tAliases = $CONF['aliases'];
   $tMailboxes = $CONF['mailboxes'];
   $tMaxquota = $CONF['maxquota'];
   
   include ("../templates/header.tpl");
   //include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

  
  $fSpamass        = get_post('fSpamass');
  $fForbidden_helo = get_post('fForbidden_helo');
  
	$domain_array['name']             = get_post('fDomain');
  $domain_array['description']      = get_post('fDescription');
  $domain_array['mailbox_aliases']  = get_post('fAliases');
  $domain_array['mailboxes']        = get_post('fMailboxes');
  $domain_array['maxquota']         = get_post('fMaxquota');
  $domain_array['backupmx']         = ( get_post('fBackupmx') == "on" ) ? 1 : 0;
  $domain_array['antivirus']        = ( get_post('fAntivirus') == "on" ) ? 1 : 0;
  $domain_array['vrfydomain']       = ( get_post('fVrfyDomain') == "on" ) ? 1 : 0;
  $domain_array['vrfysender']       = ( get_post('fVrfySender') == "on" ) ? 1 : 0;
  $domain_array['greylisting']      = ( get_post('fGreyListing') == "on" ) ? 1 : 0;
  $domain_array['spf']              = ( get_post('fSPF') == "on" ) ? 1 : 0;
  $domain_array['smtp_enabled']     = ( get_post('fStmp_enabled') == "on" ) ? 1 : 0;
  $domain_array['pop3_enabled']     = ( get_post('fPop_enabled') == "on" ) ? 1 : 0;
  $domain_array['imap_enabled']     = ( get_post('fImap_enabled') == "on" ) ? 1 : 0;

  $fDefaultaliases = ( get_post('fDefaultaliases') == "on" ) ? 1 : 0;;

  $domain_array['active'] = 1;

  $domain_info->create_domain($domain_array);

	if ( $domain_info->last_operation['result'] == 0 ){


		if ( get_post('fAntivirus') == 1 ){ 
      $policy_array['virus_lover']          = 'N'; 
      $policy_array['spam_lover']           = 'N';
      $policy_array['banned_files_lover']   = 'N';
      $policy_array['bad_header_lover']     = 'N';
      $policy_array['antivirus']            = 'N';
      $policy_array['bypass_banned_checks'] = 'N';
      $policy_array['bypass_header_checks'] = 'N';
		} 
		else { 
      $policy_array['virus_lover']          = 'Y';
      $policy_array['spam_lover']           = 'Y';
      $policy_array['banned_files_lover']   = 'Y';
      $policy_array['bad_header_lover']     = 'Y';
      $policy_array['antivirus']            = 'Y';
      $policy_array['bypass_banned_checks'] = 'Y';
      $policy_array['bypass_header_checks'] = 'Y';
		}

    $domain_info->create_domain_policy($policy_array);


    if ( $domain_info->last_operation['result'] == 1 ){
      $tMessage = $domain_info->last_operation['message'];
    }
		else{

      $tMessage  = $domain_info->last_operation['message'];
      $domain_info->create_domain_mailaliases($domain_array['backupmx'],$fDefaultaliases);
      $tMessage .= $domain_info->last_operation['message'];
    }

    include ("../templates/header.tpl");
    include ("../templates/users/menu.tpl");
    include ("../templates/users/create-domain.tpl");
    include ("../templates/footer.tpl");
  }
}

?>
