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
$user_info = new USER($ovadb);
$user_info->fetch_info ($SESSID_USERNAME);
$user_info->check_access("domain");

$domain_info = new DOMAIN($ovadb);
$user_info->fetch_quota_status();

$body_class = 'class="yui3-skin-sam"';


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
  $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

  $tDomain = get_get('domain');

  include ("../templates/header.tpl");
  include ("../templates/users/create-domain-alias.tpl"); 
  include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

  $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

  $domain_info->fetch_by_domainid(get_post('fDomain_id'));
  $domain_info->add_domain_alias(get_post('fNewDomain'));


  $tMessage = $domain->msg['text'];

  include ("../templates/header.tpl");
  include ("../templates/users/create-domain-alias.tpl");
  include ("../templates/footer.tpl");

}

?>
