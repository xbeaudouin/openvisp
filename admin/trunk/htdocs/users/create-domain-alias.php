<?php
//
// File: create-domain-alias.php
//
// Template File: create-domain-alias.tpl
//
// Template Variables:
//
// tMessage
// tNewDomain
// tDomain
//
// Form POST \ GET Variables:
//
// fNewDomain
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
require_once ("../lib/whost.class.php");


$SESSID_USERNAME = check_admin_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

$list_domains = list_domains_local ();


$ovadb = new DB();
$userinfo = new USER($ovadb);
$userinfo->fetch_info ($SESSID_USERNAME);
$userinfo->check_access("domain");

$domain_info = new DOMAIN($ovadb);
$userinfo->check_quota("domains");


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

	 $tDomain = get_get('domain');
  
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain-alias.tpl"); 
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

   $fNewDomain = strtolower (get_post('fNewDomain'));
   $fDomain_id = get_post('fDomain_id');
	 //	 $domain_id = get_domain_id($fDomain);
	 //$domain_policy = get_domain_policy($fDomain);


	 $domain = new DOMAIN($ovadb);
	 $domain->fetch_by_domainid($fDomain_id);


   if (empty($fNewDomain))
   {
     $error = 1;
     $tNewDomain = get_post('fNewDomain');
     $tDomain = $fDomain;
     $tMessage = $PALANG['pCreate_domain_alias_text_error1'];
   }
   if (domain_exist($fNewDomain))
   {
     $error = 1;
     $tNewDomain = get_post('fNewDomain');
     $tDomain = $fDomain;
     $tMessage = $PALANG['pCreate_domain_alias_text_error2'];
   }



   $result = db_query ("SELECT * FROM domain_alias WHERE dalias='$fNewDomain'");
   if ($result['rows'] == 1)
   {
      $error = 1;
      $tMessage = $PALANG['pCreate_domain_alias_text_error3'];
   }

   if ($error != 1)
   {
      $result = db_query ("INSERT INTO domain_alias (dalias,domain_id,created) VALUES ('$fNewDomain','$fDomain_id',NOW())");
      if ($result['rows'] != 1)
      {
         $tDomain = $fDomain;
         $tMessage = $PALANG['pCreate_domain_alias_result_error'] . "<br />($fAddress -> $fGoto)<br />";
      }
      else
      {
         db_log ($SESSID_USERNAME, $domain->data_domain['domain'], "create domain alias", "$fNewDomain -> ".$domain->data_domain['domain']);

         $tDomain = $fDomain;
				 $result = db_query ("INSERT INTO alias (address,goto,policy_id,created,active) VALUES ('@$fNewDomain','@".$domain->data_domain['domain']."','".$domain->data_domain['policy_id']."',NOW(),'1')");
				 if ($result['rows'] != 1)
					 {
						 $tMessage = $PALANG['pCreate_domain_alias_result_error'] . "<br />($fAddress -> $fGoto)<br />";
					 }
				 else{
					 $tMessage = $PALANG['pCreate_domain_alias_result_succes'] . "<br />($fNewDomain -> ".$domain->data_domain['domain'].")</br />";
				 } 
      }
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/create-domain-alias.tpl");
   include ("../templates/footer.tpl");
}
?>
