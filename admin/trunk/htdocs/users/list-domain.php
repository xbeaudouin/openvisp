<?php
//
// File: users/list-domain.php
//
// Template File: users/list-domain.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// fUsername
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

$domain_info = new DOMAIN($ovadb);

$user_info->check_domain_admin();
$user_info->fetch_quota_status();

// A controler ici .

//$list_admins = list_admins();

//$account_information = get_account_info($SESSID_USERNAME);
//$account_quota = get_account_quota($account_information['id']);
//$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ( $_SERVER['REQUEST_METHOD'] == "GET"  ){

  $ajax_domain = new AJAX_YUI($ovadb);

  $item_list_domain = array(
    "domain" => array(
      "label" => "'".$PALANG['pAdminList_domain_domain']."'",
      "parser" => "'"."text"."'"
    ),
    "description" => array(
      "label" => "'".$PALANG['pAdminList_domain_description']."'",
      "parser" => "'"."text"."'"
    ),
    "aliases_w_quota" => array(
      "label" => "'".$PALANG['pAdminList_domain_aliases']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false",
      "allowHTML" => "true"
    ),
    "mails_w_quota" => array(
      "label" => "'".$PALANG['pAdminList_domain_mailboxes']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false",
      "allowHTML" => "true"
    ),
    "backupmx" => array(
      "label" => "'"."Backup MX"."'",
      "parser" => "'"."text"."'"
    ),
    "ftp_w_quota" => array(
      "label" => "'".$PALANG['pAdminList_domain_ftp']."'",
      "parser" => "'"."text"."'",
      "allowHTML" => "true"
    ),
    "web_w_quota" => array(
      "label" => "'".$PALANG['pAdminList_domain_http']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false",
      "allowHTML" => "true"
    ),
    "databases_w_quota" => array(
      "label" => "'".$PALANG['pAdminList_domain_sql']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false",
      "allowHTML" => "true"
    ),
    "state" => array(
      "label" => "'".$PALANG['pAdminList_domain_status']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false"
    ),
    "active" => array(
      "label" => "'".$PALANG['pAdminList_domain_active']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false"
    ),
    "paid" => array(
      "label" => "'".$PALANG['pGeneric_paid']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false",
      "name" => "'paid'"
    ),
    "modified" => array(
      "label" => "'".$PALANG['pAdminList_domain_modified']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false"
    ),
    "delete" => array(
      "label" => "'"."delete"."'",
      // "sortable" => "false",
      // "resizeable" => "false",
      // "link" => "'"."/ajax/domain/manage_domain.php"."'",
      // "url_param" => "'"."action=delete"."'",
      // "class_name" => "'cell-delete'",
      "key" => "'delete'",
      "className" => "'cell-delete'",
      "name" => "'delete'",
      // "key_item" => "'"."domain"."'"
    ),
    "edit" => array(
      "label" => "''",
      "sortable" => "false",
      "resizeable" => "false",
      "allowHTML" => "true"
    ),
  );

  $item_list_domain_alias = array(
    "domain_alias" => array(
      "label" => "'".$PALANG['pAdminList_domain_domain']."'",
      "parser" => "'"."text"."'"
    ),
    "domain" => array(
      "label" => "'".$PALANG['pAdminList_domain_alias_aliasto']."'",
      "parser" => "'"."text"."'"
    ),
    "modified" => array(
      "label" => "'".$PALANG['pAdminList_domain_modified']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false"
    ),
    "active" => array(
      "label" => "'".$PALANG['pAdminList_domain_active']."'",
      "parser" => "'"."text"."'",
      "sortable" => "false"
    ),
    "delete" => array(
      "label" => "'"."delete"."'",
      "sortable" => "false",
      "resizeable" => "false",
      "link" => "'"."/ajax/domain/manage_domain.php"."'",
      "url_param" => "'"."action=delete"."'",
      "key_item" => "'"."domain"."'",
      "name" => "'delete'"
    ),

  );
  
  $ajax_info = array(
    //"url" => "../ajax/domain/domain_list.php",
    "url" => "../ajax/domain/manage_domain.php",
    "method" => "post",
    "table_summary" => "List of domains",
    "table_caption" => "List of domains",
    "name" => "domain_list",
    "delete_msg" => "delete domain name : ",
    "table_pkey" => "domain",
    "action_key" => "domain",
    "action_target" => "domain",
    "params" => array (
      "startIndex" => "0",
      "results" => "10",
      "sort" => "domain",
      "sortdir" => "asc",
      "domain_alias" => "0",
      "action" => "list"
    )  ,
    "item_list" => $item_list_domain,
  );

  $ajax_info2 = array(
    //"url" => "../ajax/domain/domain_list.php",
    "url" => "../ajax/domain/manage_domain.php",
    "method" => "post",
    "table_summary" => "List of domains aliased",
    "table_caption" => "List of domains aliased",
    "name" => "domain_alias_list",
    "table_pkey" => "domain_alias",
    "action_key" => "domain",
    "action_target" => "domain_alias",
    "delete_msg" => "delete domain alias name : ",
    "params" => array (
      "startIndex" => "0",
      "results" => "10",
      "sort" => "dalias",
      "sortdir" => "asc",
      "domain_alias" => "1",
      "action" => "list"
    ),
    "item_list" => $item_list_domain_alias,
  );

/*
  $ajax_domain->ajax_info($ajax_info);
  $ajax_domain->attr_add('root','records');
  $ajax_domain->attr_add('sort','domain');
  $ajax_domain->attr_add('sortdir','asc');
  $ajax_domain->attr_add('startindex','0');
  $ajax_domain->attr_add('maxrows','10');
  $ajax_domain->attr_add('data_div','domain');
  $ajax_domain->attr_add('nav_div','domain-nav');
  $ajax_domain->item_add($item_list);
*/

  $ajax_domain->start("domain");
  //$ajax_domain->create_listener();
  $ajax_domain->create_datasource($ajax_info);
  $ajax_domain->create_datatable($ajax_info);


  $ajax_domain->create_datasource($ajax_info2);
  $ajax_domain->create_datatable($ajax_info2);


  $body_class = 'class="yui3-skin-sam"';

  include ("../templates/header.tpl");
  //include ("../templates/users/menu.tpl");
  include ("../templates/users/list-domain.tpl");
  include ("../templates/footer.tpl");
}

/*
if ( ($_SERVER['REQUEST_METHOD'] == "POST") && ($user_info->check_domain_admin()) )
{
   $fUsername = get_post('fUsername');

   if ($fUsername != NULL) 
   {
     $list_domains = list_domains_for_admin ($fUsername);
     if (!empty ($list_domains))
     {
        for ($i = 0; $i < sizeof ($list_domains); $i++)
        {
           $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
   $domain_policy[$i] = get_domain_policy ($list_domains[$i]);
        }
     }
     $list_domains_alias = list_domains_alias_for_admin ($fUsername);
     if ((is_array ($list_domains_alias) and sizeof ($list_domains_alias) > 0))
       for ($i = 0; $i < sizeof ($list_domains_alias); $i++)
       {
         $domain_alias_properties[$i] = get_domain_alias_properties ($list_domains_alias[$i]);
       }
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/list-domain.tpl");
   include ("../templates/footer.tpl");
}
*/

?>
