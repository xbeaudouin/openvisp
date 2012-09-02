<?php
//
// File: stats-domain.php
//
// Template File: stats-domain.tpl
//
// Template Variables:
//
// tAlias
// tDomain
// tMailbox
// tDisplay_back
// tDisplay_next
//
// Form POST \ GET Variables:
//
// domain
// fDomain
// limit
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


$SESSID_USERNAME = check_admin_session();
$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_active_domains();
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();

$body_class = 'class="yui3-skin-sam"';

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDisplay = 0;
   $page_size = $CONF['page_size'];
   $fDomain  = get_get('domain');

	 $list_domains = list_domains_for_admin ($SESSID_USERNAME);
	 check_owner ($SESSID_USERNAME, $fDomain);


	 $template = "stats-domain.tpl";
   $tDomain = $fDomain;
	 $domain_info->fetch_by_domainname($fDomain);

	 $today = getdate();

	 $year = $today['year'];
	 $month = $today['mon'];
	 $nb_month = "3";
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/$template");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fDisplay = 0;
   $page_size = $CONF['page_size'];

	 $list_domains = list_domains_for_admin ($SESSID_USERNAME);
	 check_owner ($SESSID_USERNAME, $fDomain);

	 $fDomain = get_post('fDomain');   

	 $today = getdate();
	 $year = $today['year'];
	 $month = $today['mon'];
	 $nb_month = "3";


   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/stats-domain.tpl");
   include ("../templates/footer.tpl");
}
?>
