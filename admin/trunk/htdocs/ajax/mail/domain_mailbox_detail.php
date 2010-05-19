<?php
//
// File: ajax/mail/domain_mailbox_detail.php
//
// Form POST \ GET Variables:
//
// fDomain
//

require ("../../variables.inc.php");
require ("../../config.inc.php");
require ("../../lib/functions.inc.php");
include ("../../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../../lib/db.class.php");
require_once ("../../lib/user.class.php");
require_once ("../../lib/domain.class.php");
require_once ("../../lib/mail.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$domain_info = new DOMAIN($ovadb);
$mail_info = new MAIL($ovadb);

$fDomain_name = $_GET["domainName"];

$json_data_array = array();
$json_array = array();

if ( $_SERVER['REQUEST_METHOD'] == "POST" ){

	if ( strlen($fDomain_name) == 0 ){
		$fDomain_name = get_post('domainName');
	}


  $fMethod = get_post('method');
	$fDir = get_post('dir');
	$fResults = get_post('results');
	$fSort = get_post('sort');
	$fStartIndex =  get_post('startIndex');


	$domain_info->fetch_by_domainname($fDomain_name);
	$user_info->check_domain_access($domain_info->data_domain['id']);

	$domain_info->fetch_mailboxes(NULL, "$fStartIndex,". ($fStartIndex + $fResults), $fSort, $fDir );

 
  if ( (is_array($domain_info->list_mailboxes)) and sizeof ($domain_info->list_mailboxes > 0))
  {
    
    $json_array['totalRecords'] = intval($domain_info->used_quota['mailbox']);
    $json_array['startIndex'] = intval($fStartIndex);
    $json_array['recordsReturned'] = sizeof($domain_info->list_mailboxes);
    $json_array['sort'] = $fSort;
    $json_array['dir'] = $fDir;
    $json_array['pageSize'] = intval($fResults);
    $json_array['domainName'] = $fDomain_name;


        
    for ( $i=0; $i < sizeof($domain_info->list_mailboxes); $i++)
    {

			$mail_info->fetch_mailbox_info($domain_info->list_mailboxes[$i]['username']);
			$mail_info->mailbox_fetch_quota_used();
			$mail_info->fetch_spam_key();
			$mail_info->fetch_vacation_info();
			$mail_info->fetch_forward_state();

			$vacation = ($mail_info->data_mailbox['vacation_status'] == 0) ? $PALANG['pOverview_mailbox_responder_inactive'] : $PALANG['pOverview_mailbox_responder_active'];
			$vacation .= " <a href=\"edit-vacation.php?username=".$domain_info->list_mailboxes[$i]['username']."&domain=$fDomain_name\">".$PALANG['edit']."</a>";
			$forward = ($mail_info->data_mailbox['forwarded'] == 0) ? $PALANG['pOverview_mailbox_forward_inactive'] : $PALANG['pOverview_mailbox_responder_active'];
			$forward .= " <a href=\"edit-alias.php?address=".$domain_info->list_mailboxes[$i]['username']."&domain=$fDomain_name\">".$PALANG['edit']."</a>";
			$quarantine_url = "";
			if ( $mail_info->data_mailbox['spam_key'] != NULL ){
				$quarantine_url = $CONF['release_url']."?key=".$mail_info->data_mailbox['spam_key'].'&key2='.$mail_info->data_mailbox['spam_key2'];
			}

			$json_data_array[] = array(
																 'username' => $domain_info->list_mailboxes[$i]['username'],
																 'name' => $domain_info->list_mailboxes[$i]['name'], 
																 'quota_used' => $mail_info->mailbox_quota_used,
																 'quota' => ($domain_info->list_mailboxes[$i]['quota'] == "-1024000" ) ? "&infin;" : $domain_info->list_mailboxes[$i]['quota'] / (1024),
																 'modified' => $domain_info->list_mailboxes[$i]['modified'],
																 'policy_id' => ($domain_info->list_mailboxes[$i]['policy_id'] > 1) ? $PALANG['YES'] : $PALANG['NO'],
																 'active' => ($domain_info->list_mailboxes[$i]['active'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
																 'vacation' => $vacation,
																 'forward' => $forward,
																 'paid'  => ($domain_info->list_mailboxes[$i]['paid'] == 0) ? $PALANG['NO'] : $PALANG['YES'],
																 'quarantine' => $PALANG['pOverview_mailbox_quarantine'],
																 'quarantine_url' => $quarantine_url,
																 'pdf' => "PDF",
																 'pdf_url' => "../gen-pdf.php?username=".urlencode($domain_info->list_mailboxes[$i]['username'])."&domain=$fDomain_name&type=email",
																 'delete' => "delete",
																 'edit' => $PALANG['edit'],
																 'edit_url' => "edit-mailbox.php?username=".urlencode($domain_info->list_mailboxes[$i]['username'])."&domain=$fDomain_name"
																 );
    }
		
	}
	
	
  header('Content-type: application/x-json');
  $json_array['records'] = $json_data_array;
  echo json_encode($json_array);
	
	
}


?> 
