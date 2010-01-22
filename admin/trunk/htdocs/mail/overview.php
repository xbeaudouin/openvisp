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

$SESSID_USERNAME = check_user_session ();

$list_domains = list_domains_for_admin ($SESSID_USERNAME);

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


$tAlias = array();
$tMailbox = array();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $page_size = $CONF['page_size'];
   
   $fDomain  = get_get('domain');
   $fDisplay = get_get('limit');
   if($fDisplay == NULL) $fDisplay = 0;

   if (($fDomain != NULL) && check_owner ($SESSID_USERNAME, $fDomain))
   {
      $limit = get_domain_properties ($fDomain);
   
      if ($CONF['alias_control'] == "YES")
      {
         $sql_query = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias WHERE alias.domain='$fDomain' ORDER BY alias.address LIMIT $fDisplay, $page_size";
      }
      else
      {
				$sql_query = "SELECT alias.address, alias.goto, alias.modified, alias.policy_id
FROM policy, domain, alias
LEFT JOIN mailbox ON alias.address=mailbox.username
WHERE domain.domain='$fDomain'
AND domain.id=policy.domain_id
AND policy.id=alias.policy_id
AND mailbox.maildir IS NULL
AND alias.address NOT LIKE '@%'
ORDER BY alias.address
LIMIT $fDisplay, $page_size";
      }

      $result = db_query ($sql_query);
      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tAlias[] = $row;
         }
      }

			//$result = db_query ("SELECT mailbox.*, alias.policy_id, spamreport.* FROM mailbox,alias,spamreport WHERE mailbox.domain='$fDomain' and mailbox.username=spamreport.email and mailbox.username=alias.address ORDER BY ".$CONF['order_display']." ASC LIMIT $fDisplay, $page_size");
			//$tMailbox = list_mailbox($fDomain);

			$sql_query = "SELECT mailbox.*, alias.policy_id, spamreport.*, vacation.active as vacation_active
FROM alias, domain, mailbox
LEFT OUTER JOIN spamreport ON ( mailbox.id = spamreport.mailbox_id )
LEFT OUTER JOIN vacation ON ( mailbox.id = vacation.mailbox_id )
WHERE domain.domain='$fDomain'
AND domain.id=mailbox.domain_id
AND mailbox.username=alias.address
ORDER BY ".$CONF['order_display']." ASC
LIMIT $fDisplay, $page_size";

			$result = db_query ($sql_query);


      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tMailbox[] = $row;
         }
      }
      $template = "overview.tpl";
   }
   else
   {
      $template = "overview-get.tpl";
   }

   $tDomain = $fDomain;

   if (isset ($limit))
   {
      if ($fDisplay >= $page_size)
      {
         $tDisplay_back_show = 1;
         $tDisplay_back = $fDisplay - $page_size;
      }
      if (($limit['alias_count'] > $page_size) or ($limit['mailbox_count'] > $page_size))
      {
         $tDisplay_up_show = 1;
      }      
      if ((($fDisplay + $page_size) < $limit['alias_count']) or (($fDisplay + $page_size) < $limit['mailbox_count']))
      {
         $tDisplay_next_show = 1;
         $tDisplay_next = $fDisplay + $page_size;
      }
   }
   
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
   
   if (($fDomain != NULL) && check_owner ($SESSID_USERNAME, $fDomain))
   {
      $limit = get_domain_properties ($fDomain);
      $domain_policy = get_domain_policy ($fDomain);

      if ( $fMail_Search != NULL ) $pSearch_Data="AND alias.address LIKE '%$fMail_Search%'";
   
      if ($CONF['alias_control'] == "YES")
      {
         $query = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias WHERE alias.domain='$fDomain' $pSearch_Data ORDER BY alias.address LIMIT $fDisplay, $page_size";
      }
      else
      {
				$sql_query = "SELECT alias.address, alias.goto, alias.modified, alias.policy_id
FROM policy, domain, alias
LEFT JOIN mailbox ON alias.address=mailbox.username
WHERE domain.domain='$fDomain'
AND domain.id=policy.domain_id
AND policy.id=alias.policy_id
AND mailbox.maildir IS NULL
$pSearch_Data
ORDER BY alias.address
LIMIT $fDisplay, $page_size";
      }

#         $query = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias LEFT JOIN mailbox ON alias.address=mailbox.username WHERE alias.domain='$fDomain' AND mailbox.maildir IS NULL $pSearch_Data ORDER BY alias.address LIMIT $fDisplay, $page_size";

      $result = db_query ("$sql_query");
      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tAlias[] = $row;
         }
      }

      if ( $fMail_Search != NULL ) $pSearch_Data="and mailbox.username like '%$fMail_Search%'";

			//      $result = db_query ("SELECT mailbox.*, alias.policy_id, spamreport.* FROM mailbox,alias,spamreport WHERE mailbox.domain='$fDomain' and mailbox.username=spamreport.email and mailbox.username=alias.address ".$pSearch_Data." ORDER BY ".$CONF['order_display']." LIMIT $fDisplay, $page_size");

			$sql_query = "SELECT mailbox.*, alias.policy_id, spamreport.*
FROM alias, mailbox 
LEFT OUTER JOIN spamreport ON ( mailbox.username = spamreport.email )
WHERE mailbox.domain='$fDomain'
  AND mailbox.username=alias.address
  ".$pSearch_Data."
ORDER BY ".$CONF['order_display']." ASC LIMIT $fDisplay, $page_size";

			$sql_query = "SELECT mailbox.*, alias.policy_id, spamreport.*, vacation.active as vacation_active
FROM alias, domain, mailbox
LEFT OUTER JOIN spamreport ON ( mailbox.id = spamreport.mailbox_id )
LEFT OUTER JOIN vacation ON ( mailbox.id = vacation.mailbox_id )
WHERE domain.domain='$fDomain'
AND domain.id=mailbox.domain_id
AND mailbox.username=alias.address
$pSearch_Data
ORDER BY ".$CONF['order_display']." ASC
LIMIT $fDisplay, $page_size";

			$result = db_query ($sql_query);

			//      $result = db_query ("SELECT mailbox.*, alias.policy_id FROM mailbox,alias WHERE mailbox.domain='$fDomain' and mailbox.username=alias.address ".$pSearch_Data." ORDER BY ".$CONF['order_display']." LIMIT $fDisplay, $page_size");

      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tMailbox[] = $row;
         }
      }
   }

   if (isset ($limit))
   {
      if ($fDisplay >= $page_size)
      {
         $tDisplay_back_show = 1;
         $tDisplay_back = $fDisplay - $page_size;
      }
      if (($limit['alias_count'] > $page_size) or ($limit['mailbox_count'] > $page_size))
      {
         $tDisplay_up_show = 1;
      }
      if ((($fDisplay + $page_size) < $limit['alias_count']) or (($fDisplay + $page_size) < $limit['mailbox_count']))
      {
         $tDisplay_next_show = 1;
         $tDisplay_next = $fDisplay + $page_size;
      }
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/overview.tpl");
   include ("../templates/footer.tpl");
}
?>
