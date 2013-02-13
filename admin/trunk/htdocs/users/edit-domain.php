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

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");


$ovadb = new DB();
$user_info = new USER($ovadb);
$ova_info = new OVA($ovadb); 

$SESSID_USERNAME = $ova_info->check_session();

$user_info->fetch_info($SESSID_USERNAME);
$user_info->check_domain_admin();
$user_info->fetch_quota_status();

$domain_info = new DOMAIN($ovadb);

$body_class = 'class="yui3-skin-sam"';


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
  $domain = get_get('domain');
  $domain_info->fetch_by_domainname($domain);
  $domain_info->fetch_policy();

	if ( check_policyhosting() ){
    $tGreyListing = check_domain_greylisting($domain);
	}
   
  include ("../templates/header.tpl");
  //include ("../templates/users/menu.tpl");
  include ("../templates/users/edit-domain.tpl");
  include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
  $domain = get_post('domain');
  $action = get_post('action');
  $result_text = "";
  $domain_info->fetch_by_domainname($domain);

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

	}
	 
  else {

    $new_data['id']=$domain_info->data_domain['id'];

    $new_data['description'] = get_post('fDescription');
    $new_data['aliases']     = get_post('fAliases');
    $new_data['mailboxes']   = get_post('fMailboxes');
    $new_data['ftp_account']  = get_post('fFtpaccount');
    $new_data['db_quota']     = get_post('fDbquota');
    $new_data['db_users']     = get_post('fDbusers');
    $new_data['db_count']     = get_post('fDbcount');
    $new_data['maxquota']    = get_post('fMaxquota');
//    $new_data['fs']    = get_post('fSaActive');
    $new_data['pdf_pop']     = get_post('fPdf_Pop');
    $new_data['pdf_imap']    = get_post('fPdf_Imap');
    $new_data['pdf_smtp']    = get_post('fPdf_Smtp'); 
    $new_data['pdf_webmail'] = get_post('fPdf_Webmail');
    $new_data['pdf_custadd'] = get_post('fPdf_Address');
    $new_data['transport']   = get_post('fTransport');

    if ( get_post('fAntivirus') == "on" ) { $new_data['antivirus'] = 1; } else {$new_data['antivirus'] = 0; }
    if ( get_post('fVrfySender') == "on" ) { $new_data['vrfysender'] = 1; } else {$new_data['vrfysender'] = 0; }
    if ( get_post('fVrfyDomain') == "on" ) { $new_data['vrfydomain'] = 1; } else {$new_data['vrfydomain'] = 0; }
    if ( get_post('fSPF') == "on" ) { $new_data['spf'] = 1; } else { $new_data['spf'] = 0; }
    if ( get_post('fGreyListing') == "on" ) { $new_data['greylist'] = 1; } else { $new_data['greylist'] = 0; }
    if ( get_post('fPop3_enabled') == "on" ) { $new_data['pop3_enabled'] = 1; } else { $new_data['pop3_enabled'] = 0; }
    if ( get_post('fSmtp_enabled') == "on" ) { $new_data['smtp_enabled'] = 1; } else { $new_data['smtp_enabled'] = 0; }
    if ( get_post('fActive') == "on" ) { $new_data['active'] = 1; } else { $new_data['active'] = 0; }


    if ( get_post('fBackupmx') == "on" ){
      $new_data['aliases'] = 0;
      $new_data['mailboxes'] = 0;
      $new_data['backupmx'] == 1;
    }


    $result = $ovadb->update_record($new_data, "domain");


    if ($result != 1) {
      $tMessage = $PALANG['pAdminEdit_domain_result_error'];
      $domain_info->fetch_by_domainname($domain);

      include ("../templates/header.tpl");
      //include ("../templates/users/menu.tpl");
      print $result_text;
      include ("../templates/users/edit-domain.tpl");
      include ("../templates/footer.tpl");

    }
    else{

      header ("Location: ".getabsoluteuri()."/users/list-domain.php");


    }


  }



}

?>
