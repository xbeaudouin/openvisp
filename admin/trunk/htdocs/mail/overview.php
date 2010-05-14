<?php
//
// File: overview.php
//
// Template File: overview.tpl overview-get.tpl
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

$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_active_domains();
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();

//$list_domains = list_domains_for_admin ($SESSID_USERNAME);

//$account_information = get_account_info($SESSID_USERNAME);
// replaced by fetch_info method

//$account_quota = get_account_quota($account_information['id']);
// replaced by fetch_quota method

//$account_rights = get_account_right($account_information['id']);
// replaced by fetch_rights

//$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


//$tAlias = array();
//$tMailbox = array();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $page_size = $CONF['page_size'];
   
   $fDomain  = get_get('domain');
   $fDisplay = get_get('limit');
   if($fDisplay == NULL) $fDisplay = 0;

   if ( $fDomain != NULL)
   {
      //$limit = get_domain_properties ($fDomain);

      $domain_info->fetch_by_domainname($fDomain);
      $user_info->check_domain_access($domain_info->data_domain['id']);
      $domain_info->fetch_mail_aliases();

      /*
      $result = db_query ($sql_query);
      
      if ($result['rows'] > 0)
      {
      while ($row = db_array ($result['result']))
      {
      $tAlias[] = $row;
      }
      }
      */

			//$result = db_query ("SELECT mailbox.*, alias.policy_id, spamreport.* FROM mailbox,alias,spamreport WHERE mailbox.domain='$fDomain' and mailbox.username=spamreport.email and mailbox.username=alias.address ORDER BY ".$CONF['order_display']." ASC LIMIT $fDisplay, $page_size");
			//$tMailbox = list_mailbox($fDomain);

			/*
			$result = db_query ($sql_query);


      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tMailbox[] = $row;
         }
      }
      
      */
      
      $domain_info->fetch_mailboxes();
			$ajax_alias = new AJAX_YUI($ovadb);
			$ajax_mailbox = new AJAX_YUI($ovadb);
      
      $template = "overview.tpl";
   }
   else
   {
		 $ajax_domain = new AJAX_YUI($ovadb);
      $template = "overview-get.tpl";
   }

   $tDomain = $fDomain;
   
   /*
   if ($fDisplay >= $page_size)
   {
     $tDisplay_back_show = 1;
     $tDisplay_back = $fDisplay - $page_size;
   }  
   if (($domain_info->used_quota['mail_alias'] > $page_size) or ($domain_info->used_quota['mailbox'] > $page_size))
   {
     $tDisplay_up_show = 1;
   }      
   if ((($fDisplay + $page_size) < $domain_info->used_quota['mail_alias']) or (($fDisplay + $page_size) < $domain_info->used_quota['mailbox']))
   {
     $tDisplay_next_show = 1;
     $tDisplay_next = $fDisplay + $page_size;
   }

   */
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/$template");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $page_size = $CONF['page_size'];

   $fMail_Search = get_post('fMail_Search');
   $fDisplay     = get_post('limit');
   if($fDisplay == NULL) $fDisplay = 0;
   $fDomain = get_post('fDomain');   
   
   if ($fDomain != NULL)
   {
      $domain_info->fetch_by_domainname($fDomain);
      $user_info->check_domain_access($domain_info->data_domain['id']);
      $domain_info->fetch_mail_aliases();
      $domain_info->fetch_mailboxes();
      
      //$limit = get_domain_properties ($fDomain);
      //$domain_policy = get_domain_policy ($fDomain);

      if ( $fMail_Search != NULL ) $pSearch_Data="AND alias.address LIKE '%$fMail_Search%'";

      //$domain_info->list_mail_aliases();

#         $query = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias LEFT JOIN mailbox ON alias.address=mailbox.username WHERE alias.domain='$fDomain' AND mailbox.maildir IS NULL $pSearch_Data ORDER BY alias.address LIMIT $fDisplay, $page_size";

   }

   $tDomain = $fDomain;
   
   if ($fDisplay >= $page_size)
   {
     $tDisplay_back_show = 1;
     $tDisplay_back = $fDisplay - $page_size;
   }
   if (($domain_info->used_quota['mail_alias'] > $page_size) or ($domain_info->used_quota['mailbox'] > $page_size))
   {
     $tDisplay_up_show = 1;
   }      
   if ((($fDisplay + $page_size) < $domain_info->used_quota['mail_alias']) or (($fDisplay + $page_size) < $domain_info->used_quota['mailbox']))
   {
     $tDisplay_next_show = 1;
     $tDisplay_next = $fDisplay + $page_size;
   }

   

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/overview.tpl");
   include ("../templates/footer.tpl");
}
?>
