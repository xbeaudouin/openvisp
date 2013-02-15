<?php
//
// File: viewlog.php
//
// Template File: viewlog.tpl
//
// Template Variables:
//
// tMessage
// tLog
//
// Form POST \ GET Variables:
//
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/accounts.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$ova_info = new OVA($ovadb);
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_domains();
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();

$body_class = 'class="yui3-skin-sam"';


$list_domains = list_domains_for_admin ($SESSID_USERNAME);

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   if ((is_array ($user_info->data_managed_domain) and sizeof ($user_info->data_managed_domain) > 0)) $fDomain = $user_info->data_managed_domain[0];
   
   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pViewlog_result_error'];
   }

   if ($error != 1)
   {
      $result = db_query ("SELECT accounts.*, log.*, domain.domain FROM log, domain, accounts WHERE domain.domain='$fDomain' AND domain.id=log.domain_id AND log.accounts_id=accounts.id ORDER BY timestamp DESC LIMIT 10");
      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tLog[] = $row;
         }
      }
   }
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu_viewlog.tpl");
   include ("../templates/viewlog.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fDomain = get_post('fDomain');
   
   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pViewlog_error'];
   }

   if ($error != 1)
   {
      $result = db_query ("SELECT accounts.username, domain.domain, log.* FROM log, domain, accounts WHERE domain.domain='$fDomain' AND domain.id=log.domain_id AND log.accounts_id=accounts.id ORDER BY timestamp DESC LIMIT 10");
      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tLog[] = $row;
         }
      }
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu_viewlog.tpl");
   include ("../templates/viewlog.tpl");
   include ("../templates/footer.tpl");
}
?>
