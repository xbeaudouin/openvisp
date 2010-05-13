<?php
//
// File: create-alias.php
//
// Template File: create-alias.tpl
//
// Template Variables:
//
// tMessage
// tAddress
// tGoto
// tDomain
//
// Form POST \ GET Variables:
//
// fAddress
// fGoto
// fDomain
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

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_active_domains();
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();


//$list_domains = list_local_domains_for_admin ($SESSID_USERNAME);
$overview2 = "YES";

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $pCreate_alias_goto_text = $PALANG['pCreate_alias_goto_text'];
	 $tDomain = get_get('domain');

	 if ( $tDomain != NULL ) {
	 
	   $domain_info->fetch_by_domainname($tDomain);
	   $user_info->check_domain_access($domain_info->data_domain['id']);
	   
	   if( $domain_info->can_add_mail_alias() == FALSE ){
	     header ("Location: overview.php");
	   }
	   
	   /*
	   if (!check_owner($SESSID_USERNAME, $tDomain)) {
	   // Be paranoid, if someone is trying to get access to a
	   // domain that is not in charge, then logout the user 
	   // directly;
	   header ("Location: logout.php");
	   }
	   
	   } else {
	   
	   }
	   */
	   
	   include ("../templates/header.tpl");
	   include ("../templates/mail/menu.tpl");
	   include ("../templates/create-alias.tpl");
	   include ("../templates/footer.tpl");
   }
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $pCreate_alias_goto_text = $PALANG['pCreate_alias_goto_text'];

   $fAddress = get_post('fAddress');
   $fAddress = strtolower ($fAddress);
   $fGoto    = get_post('fGoto');
   $fGoto    = strtolower ($fGoto);
   $fDomain  = get_post('fDomain');

   if ( $fDomain != NULL ) {
     $domain_info->fetch_by_domainname($fDomain);
	   $user_info->check_domain_access($domain_info->data_domain['id']);
	   
	   if( $domain_info->can_add_mail_alias() == FALSE ){
	     header ("Location: overview.php");
	   }
	      
	   $tAddress = $fAddress;
	   $tGoto = $fGoto;
	   $tDomain = $fDomain;
	   
	   $add_alias_status = add_mailbox_alias($fDomain, $fAddress, $fGoto);
	   $tMessage = $add_alias_status['message'];
   }


   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/create-alias.tpl");
   include ("../templates/footer.tpl");
}
?>
