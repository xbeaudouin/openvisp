<?php
//
// File: massive_import.php
//
// Template File: massive_import_get.tpl massive_import_post.tpl
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
// 
// 
// 
//
require ("../variables.inc.php");


// Required Libs
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/ova.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);

include ("../languages/" . check_language() . ".lang");

require_once ("../lib/server.class.php");
require_once ("../lib/mail.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/policyd.class.php");
//require_once ("../lib/ajax_yui.class.php");

//$SESSID_USERNAME = check_user_session ();

$domain_info = new DOMAIN($ovadb);
$server_info = new SERVER($ovadb);
$mail_info = new MAIL($ovadb);
$ova_info = new OVA($ovadb);

$user_info->fetch_quota_status();

//$user_info->fetch_quota("mailboxes");

if ($_SERVER['REQUEST_METHOD'] == "GET"){
	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	include ("../templates/users/massive_import.tpl");
	include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

	$fMassiveType = get_post("massive_type");


	switch ($fMassiveType){

	case "mail_alias":
		$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['alias_file']['name']);
		move_uploaded_file($_FILES['alias_file']['tmp_name'], $uploadfile);
		$new_aliases=clean_empty_line($uploadfile);

		$error = 0;

		if ( $user_info->rights['manage'] == 0 ){

			// Check if the user can add as much aliases as he want.
			if ( $user_info->can_add_item(sizeof($new_aliases),"aliases") == FALSE ){
				$result['message'] .= sprintf($PALANG['pMassive_import_aliases_allocation_overquota_part'], sizeof($new_aliases), $user_info->data_quota['aliases'] - $user_info->data_managed['aliases'] );
				$result['message'] .= $PALANG['pMassive_import_aliases_allocation_overquota']."<br/>";
				$error++;
			}

			foreach ($new_aliases as $line_num => $line) {
				
				$info = explode(";", chop($line));
				// Check if the alias contain a valid email alias
				if ( !eregi('@', $info[0]) ){
					$error++;
					$result['message'] .= sprintf($PALANG['pMassive_import_aliases_bad_format'], $info[0]);
				}
				else{

					$alias = explode("@", $info[0]);
					if ( $domain_info->domain_exist($alias[1]) == FALSE ){
						$error++;
						$result['message'] .= sprintf($PALANG['pMassive_import_aliases_unknown_domain'], $alias[1]);
					}
					else{

						if ( ! isset($total_alias[$alias[1]]) ){
							$total_alias[$alias[1]] = 0;
						}
						$total_alias[$alias[1]]++;
						$newAliases[$alias[1]][] = array ($info[0], $info[1]);

					}

				}

			}

			foreach ($total_alias as $domain => $number_new_aliases){

				$domain_info->fetch_by_domainname($domain);
				if ( $user_info->check_domain_access($domain_info->data_domain['id'],0) == FALSE ){
					$error++;
					$result['message'] .= $PALANG['pMassive_import_aliases_domain_not_managed'];
				}
				else{
					// Don't add info to domain that are inactive.
					if ( $domain_info->data_domain['active'] == 0 ){
						$error++;
						$result['message'] .= sprintf($PALANG['pMassive_import_aliases_inactive_domain'], $alias[1]);
					}
					else{
						if ( $domain_info->can_add_mail_alias($number_new_aliases) == FALSE ){
							$error++;
							$result['message'] .= sprintf($PALANG['pMassive_import_aliases_domain_overquota'], $domain);
						}
					}
				}

			}



		}

		if ( $error == 0 ){
			
			$result['message'] = "YES you can";
			foreach ($newAliases as $domain => $item){
				$domain_info->fetch_by_domainname($domain);
				$domain_info->fetch_policy_id();
				foreach( $item as $line => $alias_to){
					$alias_from = $item[$line][0];
					$alias_to = $item[$line][1];
					$result_create = $mail_info->add_mail_alias($alias_from, $alias_to);
					if ($result_create['status'] != 0){
						$result['message'] .= sprintf($PALANG['pMassive_import_aliases_error'], $alias_from);
					}
					else{
						$result['message'] .= sprintf($PALANG['pMassive_import_aliases_ok'], $alias_from);
					}
					$result['message'] .= $result_create['message'];
				}

			}

		}

		break;

	case "mailboxes":
		$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['mailbox_file']['name']);
		move_uploaded_file($_FILES['mailbox_file']['tmp_name'], $uploadfile);
		$new_mailboxes=clean_empty_line($uploadfile);

		$error = 0;

		if ( $user_info->rights['manage'] == 0 ){

			// Check if the user can add as much email accont as he want.
			if ( $user_info->can_add_item(sizeof($new_mailboxes),"mailboxes") == FALSE ){
				$result['message'] .= sprintf($PALANG['pMassive_import_mailbox_allocation_overquota_part'], $new_mailboxes, $user_info->data_quota['mailboxes'] - $user_info->data_managed['mailboxes'] );
				$result['message'] .= $PALANG['pMassive_import_mailboxes_allocation_overquota']."<br/>";
				$error++;
			}

		}
		
		debug_info("COUCOU");

		$consumed_quota = 0;
		$consumed_quota_domain = array();

		foreach ($new_mailboxes as $line_num => $line) {
				
			//$info = explode(";", chop($line));
			list($new_mailbox['mailbox'], $new_mailbox['description'], $new_mailbox['password'], $new_mailbox['quota'], $new_mailbox['smtpauth'], $new_mailbox['pop'], $new_mailbox['imap']) = explode(";", chop($line));

			$consumed_quota += $new_mailbox['quota'];

			// Check if the email contain a valid email alias
			if ( !eregi('@', $new_mailbox['mailbox']) ){
				$error++;
				$result['message'] .= sprintf($PALANG['pMassive_import_mailboxes_bad_format'], $info[0]);
			}
			else{

				if ( $mail_info->check_mailbox_not_exist($new_mailbox['mailbox']) == FALSE ){
					$error++;
					$result['message'] .= $PALANG['pCreate_mailbox_username_text_error2'] . " <b>(".$new_mailbox['mailbox'].")</b></br/>";
				}
				else{
					$mailbox = explode("@", $new_mailbox['mailbox']);
					if ( $domain_info->domain_exist($mailbox[1]) == FALSE ){
						$error++;
						$result['message'] .= sprintf($PALANG['pMassive_import_mailboxes_unknown_domain'], $mailbox[1]);
					}
					else{

						if ( ! isset($total_mailbox[$mailbox[1]]) ){
							$total_mailbox[$mailbox[1]] = 0;
						}
						$total_mailbox[$mailbox[1]]++;
						// Create an array per domain of new mailbox
						$newMailboxList[$mailbox[1]][] = explode(";", chop($line));
						$consumed_quota_domain[$mailbox[1]] += $new_mailbox['quota'];

					}

				}

			}

		}

		//		if ( ) 


		foreach ($total_mailbox as $domain => $number_new_mailboxes){

				$domain_info->fetch_by_domainname($domain);
				if ( $user_info->check_domain_access($domain_info->data_domain['id'],0) == FALSE ){
					$error++;
					$result['message'] .= $PALANG['pMassive_import_mailboxes_domain_not_managed'];
				}
				else{
					// Don't add info to domain that are inactive.
					if ( $domain_info->data_domain['active'] == 0 ){
						$error++;
						$result['message'] .= sprintf($PALANG['pMassive_import_mailboxes_inactive_domain'], $alias[1]);
					}
					else{
						if ( $domain_info->can_add_mailbox($number_new_mailboxes) == FALSE ){
							$error++;
							$result['message'] .= sprintf($PALANG['pMassive_import_mailboxes_domain_overquota'], $domain);
						}
					}
				}

			}



		if ( $error == 0 ){
			
			$result['message'] = "YES you can";
			foreach ($newMailboxList as $domain => $mailbox){
				$domain_info->fetch_by_domainname($domain);
				$domain_info->fetch_policy_id();
				foreach( $mailbox as $line => $item){
					$mbx_name = $mailbox[$line][0];
					$mbx_info = $mailbox[$line][1];
					$mbx_pass = $mailbox[$line][2];
					$mbx_quota = $mailbox[$line][3] * 1000 * 1000;
					$mbx_smtpauth = ($mailbox[$line][4] == "") ? 0 : $mailbox[$line][4];
					$mbx_pop3 = ($mailbox[$line][5] == "") ? 0 : $mailbox[$line][5];
					$mbx_imap = ($mailbox[$line][6] == "") ? 0 : $mailbox[$line][6];

					$result_create = $mail_info->add_mailbox($mbx_name, $mbx_info, $mbx_pass, $mbx_quota, $mbx_smtpauth, $mbx_pop3, $mbx_imap);
					if ($result_create['status'] != 0){
						$result['message'] .= sprintf($PALANG['pMassive_import_mailbox_error'], $alias_from);
					}
					else{
						$result['message'] .= sprintf($PALANG['pMassive_import_mailbox_ok'], $alias_from);
					}
					$result['message'] .= $result_create['message'];
				}

			}

		}

		break;

	case "domains":
		$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['domain_file']['name']);                                                                                                
		move_uploaded_file($_FILES['domain_file']['tmp_name'], $uploadfile);
		$new_domains=clean_empty_line($uploadfile);

		$error = 0;

		if ( $user_info->rights['manage'] == 0 ){

			foreach ($new_domains as $line_num => $line) {
				$info = explode(";", $line);
				$new['mailboxes'] += $info[2];
				$new['aliases'] += $info[3];
				$new['maxquota'] += $info[4];
			}

			if ( $user_info->can_add_item(sizeof($new_domains),"domains") == FALSE ){
				$result['message'] = sprintf($PALANG['pMassive_import_domain_overquota_part'], sizeof($new_domains), $user_info->data_quota['domains'] - $user_info->data_managed['domains']);
				$result['message'] .= $PALANG['pMassive_import_domain_overquota']."<br/>";
				$error++;
			}

			if ( $user_info->can_add_item($new['mailboxes'],"mailboxes") == FALSE ){
				$result['message'] .= sprintf($PALANG['pMassive_import_mailbox_allocation_overquota_part'], $new['mailboxes'], $user_info->data_quota['mailboxes'] - $user_info->data_managed['mailboxes'] );
				$result['message'] .= $PALANG['pMassive_import_mailbox_allocation_overquota']."<br/>";
				$error++;
			}

			if ( $user_info->can_add_item($new['aliases'],"aliases") == FALSE ){
				$result['message'] .= sprintf($PALANG['pMassive_import_aliases_allocation_overquota_part'], $new['aliases'], $user_info->data_quota['aliases'] - $user_info->data_managed['aliases'] );
				$result['message'] .= $PALANG['pMassive_import_aliases_allocation_overquota']."<br/>";
				$error++;
			}
			
		}

			if ( $error == 0 ){
				$result = $domain_info->import_domains_list($new_domains);
			}

		unlink($uploadfile);
		break;


	}

	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	print "Import RESULT :".$result['result']."<br/>";
	print "Import MESSAGE :".$result['message']."<br/>";
	print "<br/>";
	include ("../templates/users/massive_import.tpl");
	include ("../templates/footer.tpl");
}

?>