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

$body_class = 'class="yui3-skin-sam"';

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
  $page_size = $CONF['page_size'];
   
  $fDomain  = get_get('domain');
  $fDisplay = get_get('limit');
  if($fDisplay == NULL) $fDisplay = 0;

  if ( $fDomain != NULL){

    $domain_info->fetch_by_domainname($fDomain);
    $user_info->check_domain_access($domain_info->data_domain['id']);
    $domain_info->fetch_mail_aliases();
      
    $domain_info->fetch_mailboxes();
    $ajax_alias = new AJAX_YUI($ovadb);
    $ajax_mailbox = new AJAX_YUI($ovadb);

    //
    // Create mailboxes table list
    //

    $item_list_alias = array(
      "address" => array(
        "label" => '"'.$PALANG['pOverview_alias_address'].'"',
        "parser" => '"text"'
      ),
      "goto" => array(
        "label" => '"'.$PALANG['pOverview_alias_goto'].'"',
        "parser" => '"text"'
      ),
      "modified" => array(
        "label" => '"'.$PALANG['pOverview_alias_modified'].'"',
        "parser" => '"text"',
        "sortable" => "false"
      ),
      "active" => array(
        "label" => '"active"',
        "parser" => '"text"',
        "radioOptions" => array (
          "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]',
          "url" => '"/ajax/mail/manage_alias.php"',
          "url_param" => '"action=mod_status&domainName='.$fDomain.'"'
        )
      ),
      "policy_id" => array(
        "label" => '"'.$PALANG['pOverview_alias_amavisd'].'"',
        "parser" => '"text"',
        "radioOptions" => array (
          "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]',
          "url" => '"/ajax/mail/manage_alias.php"',
          "url_param" => '"action=mod_antispam&domainName='.$fDomain.'"'
        )
      ),
      "delete" => array(
        "label" => '"delete"',
        "sortable" => "false",
        "resizeable" => "false",
        "link" => '"/ajax/mail/manage_alias.php"',
        "url_param" => '"action=delete&domainName='.$fDomain.'"',
        "key_item" => '"address"',
        "allowHTML" => "true"
      ),
      "edit" => array(
        "label" => '""',
        "sortable" => "false",
        "resizeable" => "false"
      ),
      "edit_url" => array(
        "label" => '""',
        "display" => FALSE
      ),
    );


    $ajax_info_alias = array(
      //"url" => "../ajax/mail/domain_alias_detail.php?domainName=$fDomain",
      "url" => "../ajax/mail/domain_alias_detail.php",
      "method" => "post",

      "table_summary" => "List of mailboxes aliases",
      "table_caption" => "List of mailboxes aliases",
      "name" => "aliases",
      "delete_msg" => "delete domain name : ",
      "table_pkey" => "domain",
      "action_key" => "domain",
      "action_target" => "domain",
      "params" => array (
        "startIndex" => "0",
        "results" => "10",
        "sort" => "address",
        "sortdir" => "asc",
        "domainName" => $fDomain,
        "action" => "list"
      )  ,
      "item_list" => $item_list_alias
    );


    $ajax_alias->ajax_info($ajax_info_alias);
    $ajax_alias->attr_add('root','records');
    $ajax_alias->attr_add('sort','address');
    $ajax_alias->attr_add('sortdir','asc');
    $ajax_alias->attr_add('startindex','0');
    $ajax_alias->attr_add('maxrows','10');
    $ajax_alias->attr_add('data_div','aliases');
    $ajax_alias->attr_add('nav_div','aliases-nav');
    $ajax_alias->item_add($item_list_alias);

    $ajax_alias->start("aliases");


    //$ajax_alias->create_listener();

    $ajax_alias->create_datasource($ajax_info_alias);
    $ajax_alias->create_datatable($ajax_info_alias);

    //
    // Create mailboxes table list
    //

    $item_list_mailbox = array(
      "username" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_username'].'"',
        "parser" => '"text"'
      ),
      "name" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_name'].'"',
        "parser" => '"text"'
      ),
      "quota_used" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_size'].'"',
        "parser" => '"number"',
        "sortable" => "false",
        "allowHTML" => "true"
      ),
      "quota" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_quota'].'"',
        "parser" => '"text"',
        "sortable" => "true",
        "allowHTML" => "true"

      ),
      "paid" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_paid'].'"',
        "parser" => '"text"',
        "sortable" => "true",
        "radioOptions" => array (
          "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]',
          "url" => "/ajax/mail/manage_mailbox.php",
          "url_param" => "action=mod_paid&domainName=$fDomain"
        ),
      ),
      "vacation" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_responder'].'"',
        "parser" => '"text"',
        "sortable" => "true",
        "allowHTML" => "true"
      ),
      "forward" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_forward'].'"',
        "parser" => '"text"',
        "sortable" => "false",
        "allowHTML" => "true"
      ),
      "policy_id" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_amavisd'].'"',
        "parser" => '"text"',
        "sortable" => "true",
        "radioOptions" => array (
          "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]'.'"',
          "url" => "/ajax/mail/manage_mailbox.php",
          "url_param" => "action=mod_antispam&domainName=$fDomain"
        ),
      ),
      "active" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_active'].'"',
        "parser" => '"text"',
        "sortable" => "true",
        "radioOptions" => array (
          "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]'.'"',
          "url" => "/ajax/mail/manage_mailbox.php",
          "url_param" => "action=mod_status&domainName=$fDomain"
        ),
      ),
      "quarantine" => array(
        "label" => '"'.$PALANG['pOverview_mailbox_quarantine'].'"',
        "parser" => '"text"',
      ),
      "quarantine_url" => array (
        "label" => '""',
        "display" => FALSE
      ),
      "modified" => array(
        "label" => '"'.$PALANG['pOverview_alias_modified'].'"',
        "parser" => '"text"'
      ),
      "pdf" => array(
        "label" => '""',
        "parser" => '"text"'
      ),
      "pdf_url" => array (
        "label" => '""',
        "display" => FALSE
      ),
      "delete" => array(
        "label" => '"delete"',
        "sortable" => "false",
        "resizeable" => "false",
        "link" => '"/ajax/mail/manage_mailbox.php"',
        "url_param" => '"action=delete&domainName='.$fDomain.'"',
        "key_item" => '"username"',
        "allowHTML" => "true"
      ),
      "edit" => array(
        "label" => '""',
        "sortable" => "false",
        "resizeable" => "false",
        "allowHTML" => "true"
      ),
      "edit_url" => array (
        "label" => '""',
        "display" => FALSE
      ),
    );


    $ajax_info_mailbox = array(
      "url" => "../ajax/mail/domain_mailbox_detail.php",
      "method" => "post",

      "table_summary" => "List of mailboxes",
      "table_caption" => "List of mailboxes",
      "name" => "mailboxes",
      "delete_msg" => "delete domain name : ",
      "table_pkey" => "domain",
      "action_key" => "domain",
      "action_target" => "domain",
      "params" => array (
        "startIndex" => "0",
        "results" => "10",
        "sort" => "username",
        "sortdir" => "asc",
        "domainName" => $fDomain,
        "action" => "list"
      )  ,
      "item_list" => $item_list_mailbox

    );


    $ajax_mailbox->ajax_info($ajax_info_mailbox);
    //$ajax_yui->attr_add('domain_name',$fDomain);
    $ajax_mailbox->attr_add('root','records');
    $ajax_mailbox->attr_add('sort','username');
    $ajax_mailbox->attr_add('sortdir','asc');
    $ajax_mailbox->attr_add('startindex','0');
    $ajax_mailbox->attr_add('maxrows','10');
    $ajax_mailbox->attr_add('data_div','mailboxes');
    $ajax_mailbox->attr_add('nav_div','mailboxes-nav');
    $ajax_mailbox->item_add($item_list_mailbox);

    $ajax_mailbox->start("mailboxes");

    $ajax_mailbox->create_datasource($ajax_info_mailbox);
    $ajax_mailbox->create_datatable($ajax_info_mailbox);
    
    $template = "overview.tpl";

  }
  else{

    $ajax_domain = new AJAX_YUI($ovadb);

    if ( $CONF['quota'] == 'YES') {
      $item_list_domain = array(
        "domain" => array(
          "label" => '"'.$PALANG['pOverview_get_domain'].'"',
          "sortable" => "true",
          "parser" => '"text"',
          "allowHTML" => "true"
        ),
        "domain_url" => array (
          "label" => "",
          "display" => FALSE
        ),
        "children" => array (
          "label" => '"'.$PALANG['pOverview_get_aliases'].'"',
          "aliases" => array (
             "key" => "aliases",
             "label" => '"'.$PALANG['pGeneric_used'].'"',
             "sortable" => "false",
             "parser" => '"number"'
          ),
          "quota_aliases" => array (
             "key" => "quota_aliases",
             "label" => '"'.$PALANG['pGeneric_left'].'"',
             "sortable" => "false",
             "allowHTML" => "true",
             "parser" => '"number"',
             "editor" => '"textarea"'
           ),
        ),
        "children2" => array (
          "label" => '"'.$PALANG['pOverview_get_mailboxes'].'"',
          "mailboxes" => array (
            "key" => "mailboxes",
            "label" => '"'.$PALANG['pGeneric_used'].'"',
            "sortable" => "false",
            "parser" => '"number"'
          ),
          "quota_mailboxes" => array (
            "key" => "quota_mailboxes",
            "label" => '"'.$PALANG['pGeneric_left'].'"',
            "sortable" => "false",
            "allowHTML" => "true",
            "parser" => '"number"',
            "editor" => '"textarea"'
          ),
        ),
        "maxquota" => array ( 
          "label" => '"'.$PALANG['pOverview_get_quota'].'"',
          "sortable" => "false",
          "parser" => '"number"',
          "allowHTML" => "true",
          "editor" => '"textarea"'
        ),
        "diskspace_mailboxes" => array(
          "label" => '"'.$PALANG['pOverview_get_total_mailbox_size'].'"',
          "sortable" => "false",
          "parser" =>  '"number"'
        ),
        "security" => array (
          "label" => '"'.$PALANG['pOverview_get_security'].'"',
          "sortable" => "false",
          "parser" => '"text"',
          "allowHTML" => "true"
        ),
        "security_url" => array (
          "label" => "",
          "display" => FALSE
        ),
      );

    }
    else{
      $item_list_domain = array(
        "domain"  => array(
          "label" => '\"'.$PALANG['pOverview_get_domain'].'\"',
          "sortable" => "true",
          "parser" => "text"
        ),
        "domain_url" => array (
          "label" => "",
          "display" => FALSE
        ),
        "aliases" => array (
          "label" => '"'.$PALANG['pOverview_get_aliases'].'"',
          "sortable" => "false",
          "parser" => "number"
        ),
        "mailboxes" => array (
          "label"             => '"'.$PALANG['pOverview_get_mailboxes'].'"',
          "sortable"          => "false",
          "parser"            => "number"
        ),
        "maxquota"            => array ( 
          "label"             => '"'.$PALANG['pOverview_get_quota'].'"',
          "sortable"          => "false",
          "parser"            => '"number"',
          "editor"            => '"textarea"'
        ),
        "diskspace_mailboxes" => array(
          "label"             => '"'.$PALANG['pOverview_get_total_mailbox_size'].'"',
          "sortable"          => "false",
          "parser"            =>  '"number"'
        ),
        "security"            => array (
          "label"             => '"'.$PALANG['pOverview_get_security'].'"',
          "sortable"          => "false",
          "parser"            => '"text"',
        ),
        "security_url"        => array (
          "label"             => "",
          "display"           => FALSE
        )
      );
    }
     


    $ajax_info_domain = array(
      "url" => "../ajax/mail/domain_mail_overview.php",
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
      "item_list" => $item_list_domain
    );


    $ajax_domain->ajax_info($ajax_info_domain);
    $ajax_domain->attr_add('root','records');
    $ajax_domain->attr_add('sort','domain');
    $ajax_domain->attr_add('sortdir','asc');
    $ajax_domain->attr_add('startindex','0');
    $ajax_domain->attr_add('maxrows','10');
    $ajax_domain->attr_add('data_div','domain');
    $ajax_domain->attr_add('nav_div','domain-nav');


    //$ajax_domain->item_add($item_list);

    $ajax_domain->start("domain");

    $ajax_domain->create_datasource($ajax_info_domain);
    $ajax_domain->create_datatable($ajax_info_domain);
//    $ajax_domain->

    $template = "overview-get.tpl";
  }

  $tDomain = $fDomain;
   
  include ("../templates/header.tpl");
  include ("../templates/mail/menu.tpl");
  include ("../templates/$template");
  include ("../templates/footer.tpl");

}

if ($_SERVER['REQUEST_METHOD'] == "POST"){
  $page_size = $CONF['page_size'];

  $fMail_Search = get_post('fMail_Search');
  $fDisplay     = get_post('limit');
  if($fDisplay == NULL) $fDisplay = 0;
  $fDomain = get_post('fDomain');   
   
  if ($fDomain != NULL){
    $domain_info->fetch_by_domainname($fDomain);
    $user_info->check_domain_access($domain_info->data_domain['id']);
    $domain_info->fetch_mail_aliases();
    $domain_info->fetch_mailboxes();
    
    if ( $fMail_Search != NULL ) $pSearch_Data="AND alias.address LIKE '%$fMail_Search%'";
    

  }

  $tDomain = $fDomain;
  
  if ($fDisplay >= $page_size){
    $tDisplay_back_show = 1;
    $tDisplay_back = $fDisplay - $page_size;
  }
  if (($domain_info->used_quota['mail_alias'] > $page_size) or ($domain_info->used_quota['mailbox'] > $page_size)){
    $tDisplay_up_show = 1;
  }      
  if ((($fDisplay + $page_size) < $domain_info->used_quota['mail_alias']) or (($fDisplay + $page_size) < $domain_info->used_quota['mailbox'])){
    $tDisplay_next_show = 1;
    $tDisplay_next = $fDisplay + $page_size;
  }

   

  include ("../templates/header.tpl");
  include ("../templates/mail/menu.tpl");
  include ("../templates/overview.tpl");
  include ("../templates/footer.tpl");
  
}
?>
