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
$user_info->check_access("domain");
$user_info->fetch_domains();

$domain_info = new DOMAIN($ovadb);

$body_class = 'class="yui3-skin-sam"';


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
  $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

  $tDomain = get_get('domain');

  include ("../templates/header.tpl");
  include ("../templates/domain/create-domain-alias.tpl"); 
  include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

  $pCreate_domain_alias_goto_text = $PALANG['pCreate_domain_alias_goto_text'];

  $domain_info->fetch_by_domainid(get_post('fDomain_id'));
  $domain_info->fetch_policy_id();
  $domain_info->add_domain_alias(get_post('fNewDomain'));


  $tMessage = $domain_info->msg['text'];

  include ("../templates/header.tpl");
  include ("../templates/domain/create-domain-alias.tpl");
  include ("../templates/footer.tpl");

}

?>
