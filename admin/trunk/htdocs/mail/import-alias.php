<?php
//
// File: import-mailbox.php
//
// Template File: mail/import-alias.tpl
//
// Template Variables:
//
// Form POST \ GET Variables:
//
// domain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/mail.class.php");
require_once ("../lib/server.class.php");
require_once ("../lib/policyd.class.php");
require_once ("../lib/ova.class.php");


$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);

$domain_info = new DOMAIN($ovadb);

$list_domains = list_local_domains_for_admin ($SESSID_USERNAME);

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	 $tDomain = get_get('domain');

   if ( $tDomain != NULL ) {

		 $domain_info->fetch_by_domainname($tDomain);
		 $user_info->check_domain_access($domain_info->data_domain['id']);
		 

     if ( $domain_info->can_add_mail_alias() == FALSE ) {
       // Be paranoid. If someone is trying to get acces to a
       // a domain that is not in charge, then logout the user
       // directly.
       header ("Location: ../logout.php");
     }
   } else {
     header ("Location: overview.php");
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/import-alias.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

	$fDomain=get_post('fDomain');

	if ( $fDomain != NULL ) {
		
		$domain_info->fetch_by_domainname($fDomain);
		$user_info->check_domain_access($domain_info->data_domain['id']);

		if ( $domain_info->can_add_mail_alias() == FALSE ) {
			// Be paranoid. If someone is trying to get acces to a
			// a domain that is not in charge, then logout the user
			// directly.
			header ("Location: ../logout.php");
		}
	} else {
		header ("Location: overview.php");
	}


	$ova = new OVA($ovadb);
	$server_info = new SERVER($ovadb);
	$tMessage = "";

	$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['user_file']['name']);

	if (move_uploaded_file($_FILES['user_file']['tmp_name'], $uploadfile)) {

		$lines = file($uploadfile);

		foreach ($lines as $line_num => $line) {
			$line = chop ($line);
			if (eregi("^$",$line)){
				unset($lines[$line_num]);
			}
		}

		if ( $domain_info->can_add_mail_alias(sizeof($lines)) == FALSE ) {
			$tMessage = $PALANG['pImport_Alias_file_overquota']."<br/>";
			$tMessage .= $PALANG['pImport_Alias_file_quota_status_1'];
			$tMessage .= $domain_info->quota['mail_aliases'] - $domain_info->used_quota['mail_alias'];
			$tMessage .= $PALANG['pImport_Alias_file_quota_status_2']."<br/>";
			$tMessage .= $PALANG['pImport_Alias_file_quota_request_1'];
			$tMessage .= sizeof($lines);
			$tMessage .= $PALANG['pImport_Alias_file_quota_request_2']."<br/>";
		}
		else {

			$mail_info = new MAIL($ovadb);

			foreach ($lines as $line_num => $line) {
				$line = chop ($line);

				if (!eregi("^$",$line)){
					$info = explode(";", $line);

					$alias_from = $info[0];
					$alias_table_to = preg_split ('/,/', $info[1]);
					$alias_active=1;
					if ( isset($info[2]) ){
						$alias_active = $info[2];
					}

					if ( eregi("@",$alias_from )){
						$array = preg_split ('/@/', $alias_from);
						$alias_from = $array[0];
					}

					$alias_from = chop(escape_string($alias_from)) . "@" .  $fDomain;

					for ( $i=0; $i < sizeof($alias_table_to); $i++)
						{
							if ( !eregi("@",$alias_table_to[$i] )){
								$alias_table_to[$i] =  chop(escape_string($alias_table_to[$i])) . "@" .  $fDomain;
							}
						}
					$alias_to = implode(",", $alias_table_to);

					$result = $mail_info->add_mail_alias($alias_from, $alias_to, $alias_active);
					//print $result['status']."<br/>";
					$tMessage .= $result['message']."<br/>";

					//$add_alias =  add_mailbox_alias( $fDomain, $alias_from, $alias_to );
					//$tMessage .= $add_alias['message'];

				}

			}
		}
	}



	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");

	//print $pImport_Error."<br />";
	print $tMessage;
	//print "OK";

	//	include ("./templates/import-mailbox.tpl");
	print "<br/><a href=\"overview.php?domain=$fDomain\">".$PALANG['pImport_Users_return']."</a><br/>";
	include ("../templates/footer.tpl");
 }
?>
