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

$SESSID_USERNAME = check_user_session();
$list_admins = list_admins();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ( ($_SERVER['REQUEST_METHOD'] == "GET") && (check_domain_admin($SESSID_USERNAME)) )
{
	//   $fUsername = get_get('username');
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
