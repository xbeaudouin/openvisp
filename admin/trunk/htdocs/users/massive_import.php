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

			if ( $user_info->can_add_item(sizeof($new_aliases),"aliases") == FALSE ){
				$result['message'] .= sprintf($PALANG['pMassive_import_aliases_allocation_overquota_part'], $new['aliases'], $user_info->data_quota['aliases'] - $user_info->data_managed['aliases'] );
				$result['message'] .= $PALANG['pMassive_import_aliases_allocation_overquota']."<br/>";
				$error++;
			}

			foreach ($new_aliases as $line_num => $line) {
				$info = explode(";", $line);
				if ( !eregi("@", $info[0]) ){
					$error++;
					$result['message'] .= sprintf($PALANG['pMassive_import_aliases_bad_format'], $info[0]);
				}
				else{

					$alias = explode("@", $info[0]);
					if ( $domain_info->domain_exist($alias[1]) ){
						if ( $user_info->check_domain_access($alias[1],0) == FALSE ){
							$error++;
							$result['message'] .= $PALANG['pMassive_import_aliases_domain_not_managed'];
						}
						debug_info("DBG $alias is good");
					}
					else{
						$error++;
						$result['message'] .= sprintf($PALANG['pMassive_import_aliases_unknown_domain'], $alias[1]);
					}

				}

			}


		}
		if ( $error == 0 ){
			$result['message'] = "YES you can";
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