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
require ("./variables.inc.php");
require ("./config.inc.php");
require ("./lib/functions.inc.php");
include ("./languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_session();

$list_domains = list_domains_for_admin ($SESSID_USERNAME);

$tAlias = array();
$tMailbox = array();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fDisplay = 0;
   $page_size = $CONF['page_size'];
   
   if (isset ($_GET['domain'])) $fDomain  = get_get('domain');

   if (isset ($_GET['domain']) && check_owner ($SESSID_USERNAME, $fDomain))
   {
      $limit = get_domain_properties ($fDomain);
   }

	 $template = "stats-domain.tpl";
   $tDomain = $fDomain;


// "limit 300,20";
	 


   
   include ("./templates/header.tpl");
   include ("./templates/mail_menu.tpl");
   include ("./templates/$template");
   include ("./templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fDisplay = 0;
   $page_size = $CONF['page_size'];

   $fMail_Search = get_post('fMail_Search');
   
   if (isset ($_POST['limit'])) $fDisplay = get_post('limit');
   
   if (check_owner ($SESSID_USERNAME, get_post('fDomain')))
   {
      $fDomain = get_post('fDomain');   

      $limit = get_domain_properties ($fDomain);

      if ( isset ($_POST['fMail_Search']) ) $pSearch_Data="and alias.address like '%$fMail_Search%'";
   
      if ($CONF['alias_control'] == "YES")
      {
         $query_alias = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias WHERE alias.domain='$fDomain' $pSearch_Data ORDER BY alias.address LIMIT $fDisplay, $page_size";
      }
      else
      {
         $query_alias = "SELECT alias.address,alias.goto,alias.modified,alias.policy_id FROM alias LEFT JOIN mailbox ON alias.address=mailbox.username WHERE alias.domain='$fDomain' AND mailbox.maildir IS NULL $pSearch_Data ORDER BY alias.address LIMIT $fDisplay, $page_size";
      }

      $result = db_query ("$query");
      if ($result['rows'] > 0)
      {
         while ($row = db_array ($result['result']))
         {
            $tAlias[] = $row;
         }
      }

      if ( isset ($_POST['fMail_Search']) ) $pSearch_Data="and mailbox.username like '%$fMail_Search%'";

      $result = db_query ("SELECT * FROM mailbox WHERE domain='$fDomain' ".$pSearch_Data." ORDER BY ".$CONF['order_display']." LIMIT $fDisplay, $page_size");
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

   include ("./templates/header.tpl");
   include ("./templates/mail_menu.tpl");
   include ("./templates/stats-domain.tpl");
   include ("./templates/footer.tpl");
}
?>
