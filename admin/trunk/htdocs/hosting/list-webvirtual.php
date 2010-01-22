<?php
//
// File: web-admin.php
//
// Template File: web-webvirtual.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

if ( $SESSID_USERNAME != "")
{
  if (check_http_admin($SESSID_USERNAME) or check_ftp_admin($SESSID_USERNAME))
  {
    if ($_SERVER['REQUEST_METHOD'] == "GET")
    {
       $page_size = $CONF['page_size'];
       
       //XB: Not used ? 
       //if (isset ($_GET['domain'])) $fDomain  = escape_string($_GET['domain']);
       $fDisplay = get_get('limit');
       if($fDisplay == NULL) $fDisplay = 0;

       $fUsername = get_get('username');
       
       if ($fUsername != NULL)
       {
          $list_domains = list_domains_for_admin ($fUsername);
          if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
          {
              for ($i = 0; $i < sizeof ($list_domains); $i++)
              {
                $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
		$domain_websites[$i] = get_website_list ($list_domains[$i]);
              }
          }
          $list_domains_alias = list_domains_alias_for_admin ($fUsername);
          if ((is_array ($list_domains_alias) and sizeof ($list_domains_alias) > 0))
          {
             for ($i = 0; $i < sizeof ($list_domains_alias); $i++)
             {
                $domain_alias_properties[$i] = get_domain_alias_properties ($list_domains_alias[$i]);
             }
          }
       }
    
       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       include ("../templates/hosting/list-webvirtual.tpl");
       include ("../templates/footer.tpl");
    }
    
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
        
       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       //include ("../templates/hosting/main.tpl");
       include ("../templates/hosting/list-webvirtual.tpl");
       include ("../templates/footer.tpl");
    }
  }


}

?>
