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
$ova = new OVA($ovadb); 

$SESSID_USERNAME = $ova->check_session();

$user_info->fetch_info($SESSID_USERNAME);
$user_info->check_domain_admin();
$user_info->fetch_quota_status();

$domain_info = new DOMAIN($ovadb);

$body_class = 'class="yui3-skin-sam"';

//$account_information = get_account_info($SESSID_USERNAME);
//$account_quota = get_account_quota($account_information['id']);
//$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
  $domain = get_get('domain');
  $domain_info->fetch_by_domainname($domain);
  //$domain_properties = $domain_info->fetch_quota_status();

  //$domain_properties = get_domain_properties ($domain);
  $domain_info->fetch_policy();
	//$domain_policy = get_domain_policy($domain);

/*
  $tDescription = $domain_info->data_domain['description'];
  $tAliases     = $domain_info->data_domain['aliases'];
  $tMailboxes   = $domain_info->data_domain['mailboxes'];
  $tFtpaccount  = $domain_info->data_domain['ftp_account'];
  $tDbusers     = $domain_info->data_domain['db_users'];
  $tDbquota     = $domain_info->data_domain['db_quota'];
  $tDbcount     = $domain_info->data_domain['db_count'];
  $tMaxquota    = $domain_info->data_domain['maxquota'];
  $tBackupmx    = $domain_info->data_domain['backupmx'];
  $tActive      = $domain_info->data_domain['active'];
  $tAntivirus   = $domain_info->data_domain['antivirus'];
  $tSaActive    = $domain_policy['bypass_spam_checks'];
  $tVrfyDomain  = $domain_info->data_domain['vrfydomain'];
  $tVrfySender  = $domain_info->data_domain['vrfysender'];
  $tSPF         = $domain_info->data_domain['spf'];
  $tPdf_Pop     = $domain_info->data_domain['pdf_pop'];
  $tPdf_Imap    = $domain_info->data_domain['pdf_imap'];
  $tPdf_Smtp    = $domain_info->data_domain['pdf_smtp'];
  $tPdf_Webmail = $domain_info->data_domain['pdf_webmail'];
  $tPdf_Custadd = $domain_info->data_domain['pdf_custadd'];
  $tTransport   = $domain_info->data_domain['transport'];
  $tPaid        = $domain_info->data_domain['paid'];
  $tImap_enabled = $domain_info->data_domain['imap_enabled'];
  $tPop3_enabled = $domain_info->data_domain['pop3_enabled'];
  $tSmtp_enabled = $domain_info->data_domain['smtp_enabled'];
*/

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
  //$domain_id = get_domain_id($domain);

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

/*
    include ("../templates/header.tpl");
    //include ("../templates/users/menu.tpl");
    print $result_text;
    include ("../templates/footer.tpl");

*/
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

    // $new_data['vrfydomain']  = get_post('fVrfyDomain');
    // $new_data['vrfysender']  = get_post('fVrfySender');
    // $new_data['greylist'] = get_post('fGreyListing');
    // $new_data['imap_enabled'] = get_post('fImap_enabled');
    // $new_data['pop3_enabled'] = get_post('fPop3_enabled');
    // $new_data['smtp_enabled'] = get_post('fSmtp_enabled');

    // if ($fAntivirus == "on")   $fAntivirus = 1;
    // if ($fSaActive == "on"){   $fSaActive = 'Y';} else {$fSaActive = 'N';}
    // if ($fVrfyDomain == "on")  $fVrfyDomain = 1;
    // if ($fVrfySender == "on")  $fVrfySender = 1;
    // if ($fSPF == "on")         $fSPF = 1;
    // if ($fGreyListing == "on") $fGreyListing = 1;
    // if ($fImap_enabled == "on") $fImap_enabled = 1;
    // if ($fPop3_enabled == "on") $fPop3_enabled = 1;
    // if ($fSmtp_enabled == "on") $fSmtp_enabled = 1;

    if ( get_post('fBackupmx') == "on" ){
      $new_data['aliases'] = 0;
      $new_data['mailboxes'] = 0;
      $new_data['backupmx'] == 1;
    }

    // if ($fBackupmx == "on"){
    //   $fAliases = 0;
    //   $fMailboxes = 0;
    //   $fMaxquota = 0;
    //   $fBackupmx = 1;
    // }

    // if ($fActive == "on") { $fActive = 1; }

    // $DB_SQL = "";

    // if ( check_dbhosting() ){
    //   $DB_SQL=", db_count='$fDbcount', db_users='$fDbusers', db_quota='$fDbquota'";
    // }

    $result = $ovadb->update_record($new_data, "domain");
    
  // 	$sql_query = "UPDATE domain
  // SET description='$fDescription', aliases='$fAliases', mailboxes='$fMailboxes', maxquota='$fMaxquota',
  // backupmx='$fBackupmx', active='$fActive', modified=NOW(), antivirus='$fAntivirus', vrfydomain='$fVrfyDomain',
  // vrfysender='$fVrfySender', spf='$fSPF', pdf_pop='$fPdf_Pop', pdf_imap='$fPdf_Imap', pdf_smtp='$fPdf_Smtp',
  // pop3_enabled='$fPop3_enabled', imap_enabled='$fImap_enabled', smtp_enabled='$fSmtp_enabled', 
  // pdf_webmail='$fPdf_Webmail', pdf_custadd='$fPdf_Address', transport='$fTransport', ftp_account='$fFtpaccount' $DB_SQL
  // WHERE domain='$domain'";

  // 	$result = db_query ($sql_query);

    if ($result == 1) {

      // if ( check_policyhosting() ){
      //   $result2 = db_query("SELECT * FROM policy WHERE _rcpt='@".$domain."'","1","policyd");
      //   if ($result2['rows'] == 1){
      //     $result = db_query("UPDATE policy SET _optin='".$fGreyListing."' WHERE _rcpt='@".$domain."'","1","policyd");
      //   }
      //   else{
      //   $result = db_query("INSERT INTO policy(_rcpt,_optin,_priority) VALUES ('@".$domain."','".$fGreyListing."','10')","1","policyd");
      //   }
      // }

      //header ("Location: list-domain.php");
    }
    else{
      $tMessage = $PALANG['pAdminEdit_domain_result_error'];
    }


  }

  $domain_info->fetch_by_domainname($domain);
  
  include ("../templates/header.tpl");
  //include ("../templates/users/menu.tpl");
  print $result_text;
  include ("../templates/users/edit-domain.tpl");
  include ("../templates/footer.tpl");


}

?>
