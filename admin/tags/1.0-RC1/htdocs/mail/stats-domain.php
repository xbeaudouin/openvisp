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

$SESSID_USERNAME = check_admin_session();


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDisplay = 0;
   $page_size = $CONF['page_size'];
   $fDomain  = get_get('domain');

	 $list_domains = list_domains_for_admin ($SESSID_USERNAME);

	 check_owner ($SESSID_USERNAME, $fDomain);


	 $template = "stats-domain.tpl";
   $tDomain = $fDomain;

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
