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


$SESSID_USERNAME = check_user_session();
$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

$list_domains = list_domains_for_admin ($SESSID_USERNAME);

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   if ((is_array ($list_domains) and sizeof ($list_domains) > 0)) $fDomain = $list_domains[0];
   
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
