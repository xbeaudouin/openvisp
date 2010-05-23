<?php
//
// File: hosting/list-domain.php
//
// Template File: hosting/list-domain.tpl
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


$user_info->check_domain_admin();

// A controler ici .

$list_admins = list_admins();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ( $_SERVER['REQUEST_METHOD'] == "GET"  )
{

	$ajax_domain = new AJAX_YUI($ovadb);

			$item_list = array(
												 "domain" => array(
																						"label" => $PALANG['pAdminList_domain_domain'],
																						"parser" => "text"
																						),
												 "description" => array(
																				 "label" => $PALANG['pAdminList_domain_description'],
																				 "parser" => "text"
																				 ),
												 "aliases" => array(
																						 "label" => $PALANG['pAdminList_domain_aliases'],
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "quota_aliases" => array(
																						 "label" => "",
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "mails" => array(
																						 "label" => $PALANG['pAdminList_domain_mailboxes'],
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "quota_mails" => array(
																						 "label" => "",
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "backupmx" => array(
																					 "label" => "Backup MX",
																					 "parser" => "text"
																					 ),
												 "ftp" => array(
																							"label" => $PALANG['pAdminList_domain_ftp'],
																							"parser" => "text"
																							),
												 "quota_ftp" => array(
																							"label" => "",
																							"parser" => "text"
																							),
												 "web" => array(
																				"label" => $PALANG['pAdminList_domain_http'],
																				"parser" => "text",
																				"sortable" => "false"
																				),
												 "quota_web" => array(
																							"label" => "",
																							"parser" => "text",
																							"sortable" => "false"
																							),
												 "databases" => array(
																							"label" => $PALANG['pAdminList_domain_sql'],
																							"parser" => "text",
																							"sortable" => "false"
																							),
												 "quota_databases" => array(
																							"label" => "",
																							"parser" => "text",
																							"sortable" => "false"
																							),
												 "state" => array(
																						 "label" => $PALANG['pAdminList_domain_status'],
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "active" => array(
																					 "label" => $PALANG['pAdminList_domain_active'],
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),
												 "modified" => array(
																						 "label" => $PALANG['pAdminList_domain_modified'],
																						 "parser" => "text",
																						 "sortable" => "false"
																						 ),

												 "delete" => array(
																					 "label" => "delete",
																					 "sortable" => "false",
																					 "resizeable" => "false",
																					 "link" => "/ajax/domain/manage_domain.php",
																					 "url_param" => "action=delete",
																					 "key_item" => "domain"
																					 ),
												 "edit" => array(
																				 "label" => "",
																				 "sortable" => "false",
																				 "resizeable" => "false"
																				 )
												 );


			$ajax_info = array(
												 "url" => "../ajax/domain/domain_list.php",
												 "method" => "post"
												 );


			$ajax_domain->ajax_info($ajax_info);
			$ajax_domain->attr_add('root','records');
			$ajax_domain->attr_add('sort','domain');
			$ajax_domain->attr_add('sortdir','asc');
			$ajax_domain->attr_add('startindex','0');
			$ajax_domain->attr_add('maxrows','10');
			$ajax_domain->attr_add('data_div','domain');
			$ajax_domain->attr_add('nav_div','domain-nav');
			$ajax_domain->item_add($item_list);

			$ajax_domain->start("domain");
			$ajax_domain->create_listener();



	$fUsername = $SESSID_USERNAME;

   if ($fUsername != NULL)
   {

      $list_domains = list_domains_for_admin ($fUsername);
      if ($list_domains != 0)
      {
         for ($i = 0; $i < sizeof ($list_domains); $i++)
         {
            $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
						//	          $domain_policy[$i] = get_domain_policy ($list_domains[$i]);
         }
      }
      $list_domains_alias = list_domains_alias_for_admin ($fUsername);
      if ((is_array ($list_domains_alias) and sizeof ($list_domains_alias) > 0))
         for ($i = 0; $i < sizeof ($list_domains_alias); $i++)
         {
            $domain_alias_properties[$i] = get_domain_alias_properties ($list_domains_alias[$i]['dalias']);
         }
   }

	 $body_class = 'class=" yui-skin-sam"';

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/list-domain.tpl");
   include ("../templates/footer.tpl");
}

if ( ($_SERVER['REQUEST_METHOD'] == "POST") && (check_domain_admin($SESSID_USERNAME)) )
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
?>
