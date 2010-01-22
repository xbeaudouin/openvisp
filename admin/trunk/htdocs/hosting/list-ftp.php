<?php
//
// File: list-ftp.php
//
// Template File: admin_list-ftp.tpl
//
// Template Variables:
//
// tFtpaccount
//
// Form POST \ GET Variables:
//
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/server.class.php");


$ovadb = new DB();
$test = new SERVER();


$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

//$list_domains = list_domains_local ();

$tFtpaccount = array();

if ( $SESSID_USERNAME != "" )
{
  if (check_webhosting_admin($SESSID_USERNAME))
  {
  if ($_SERVER['REQUEST_METHOD'] == "GET")
  {
     $page_size = $CONF['page_size'];
     
     $fDisplay = get_get('limit');
     if ($fDisplay == NULL) $fDisplay = 0;

     $fUsername = get_get('username');
  
     if ($fUsername != NULL)
     {
          $list_domains = list_domains_for_admin ($fUsername);
          if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
          {
              for ($i = 0; $i < sizeof ($list_domains); $i++)
              {
               $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
               $domain_ftpaccount[$i] = get_ftpaccount_list ($list_domains[$i]);
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
        if (($limit['ftp_count'] > $page_size) or ($limit['ftp_count'] > $page_size))
        {
           $tDisplay_up_show = 1;
        }      
        if ( ($fDisplay + $page_size) < $limit['ftp_count'] )
        {
           $tDisplay_next_show = 1;
           $tDisplay_next = $fDisplay + $page_size;
        }
     }
  
     include ("../templates/header.tpl");
     include ("../templates/hosting/menu.tpl");
     include ("../templates/hosting/list-ftp.tpl");
     include ("../templates/footer.tpl");
  	}
  }
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $page_size = $CONF['page_size'];

   $fDomain = get_post('fDomain');
   $fDisplay = get_post('limit');
   if ($fDisplay == NULL) $fDisplay = 0;

   $limit = get_domain_properties ($fDomain);


   $query = "select * from ftpaccount where domain = '".$fDomain."'";

   $result = db_query ("$query");
   if ($result['rows'] > 0)
   {
      while ($row = db_array ($result['result']))
      {
         $tFtpaccount[] = $row;
      }
   }

   if (isset ($limit))
   {
      if ($fDisplay >= $page_size)
      {
         $tDisplay_back_show = 1;
         $tDisplay_back = $fDisplay - $page_size;
      }
      if ( $limit['ftp_count'] > $page_size )
      {
         $tDisplay_up_show = 1;
      }      
      if ( ($fDisplay + $page_size) < $limit['ftp_count'] )
      {
         $tDisplay_next_show = 1;
         $tDisplay_next = $fDisplay + $page_size;
      }
   }


   include ("../templates/header.tpl");
   include ("../templates/hosting/menu.tpl");
   include ("../templates/hosting/list-ftp.tpl");
   include ("../templates/footer.tpl");
	 
	}



?>
