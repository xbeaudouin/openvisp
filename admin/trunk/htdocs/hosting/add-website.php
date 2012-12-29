<?php
//
// File: hosting/add-website.php
//
// Template File: hosting/add-website.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fDomain
// fVhost
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");

require_once ("../lib/domain.class.php");
require_once ("../lib/whost.class.php");


$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


$ovadb = new DB();

$userinfo = new USER($ovadb);

try {
	$userinfo->fetch_info ($SESSID_USERNAME);
	$userinfo->check_access("http");

	$domain = new DOMAIN($ovadb);
	$userinfo->check_quota("http");


		// $userinfo->data['http'] >=  $total_used['http'] >= $account_quota['http']  
    if ($_SERVER['REQUEST_METHOD'] == "GET")
    {
       if (isset ($_GET['username']))
       {
         $fUsername = get_get('username');
         
         $list_domains = list_domains_for_admin ($fUsername);
         if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
          {
              for ($i = 0; $i < sizeof ($list_domains); $i++)
              {
                $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
                $domain_vhosts[$i] = get_website_list ($list_domains[$i]);
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
       include ("../templates/hosting/add-website.tpl");
       include ("../templates/footer.tpl");
    }
    
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {


			$fUsername = escape_string($_GET['username']);

			$list_domains = list_domains_for_admin ($fUsername);

       if ( $fUsername )
       {

				 $fDomain = get_post('fDomain');
				 $fVhost = get_post('fVhost');


         if( $fDomain )
         {

					 $ovadb = new DB();

					 $userinfo = new USER($ovadb);
					 $userinfo->fetch_info ($SESSID_USERNAME);

					 $whost = new WHOST($ovadb,$fDomain);
					 $whost->fetch_by_whost($fVhost);

					 if ( $whost->data_whost_row > 0 )
            {
              $error = 1;
              $tMessage = $PALANG['pWhostCreate_website_exist'];
            }

            else
            {

							//							$new_whost = new WHOST($ovadb);

              if($fVhost=='')
              {
								
								//$new_whost->add($userinfo,$fVhost,$domain);
								
                //$result = db_query ("INSERT INTO whost (vhost,domain,DocumentRoot,isalias,created,modified,active) VALUES ('$fVhost','$fDomain','$storage','0',NOW(),NOW(),'1')");
								//$result = db_query ("SELECT id FROM whost WHERE domain='$fDomain' AND isalias='0'");
								//$row = db_array ($result['result']);
                // $result = db_query ("INSERT INTO whost (vhost,domain,DocumentRoot,isalias,created,modified,active) VALUES ('www','$fDomain','$storage',$row[id],NOW(),NOW(),'1')");
                // db_log ($SESSID_USERNAME, $fDomain, "Defaults Websites have been added", $fUsername);
                // header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
              }
              else
              {
                $vhost = $fVhost;
								$whost->add_whost($userinfo,$fVhost);
								//header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
								
								//								db_log ($SESSID_USERNAME, $fDomain, "Website $fVhost.$fDomain has been added", $fUsername);



								//$storage = $CONF['storage'] . "/" . get_dir_hash($fDomain) . "/" . $fDomain . "/" . $vhost;

								// $result = db_query ("INSERT INTO whost (vhost,domain,DocumentRoot,created,modified,active) VALUES ('$fVhost','$fDomain','$storage',NOW(),NOW(),'1')");
								// 
                // header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
              }
            }

         }


			 }




       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       include ("../templates/hosting/add-website.tpl");
       include ("../templates/footer.tpl");
    }
  

}

catch ( Exception $error)
{
	//	print "Error : ".$error->getMessage()."<br/>\n";
	header ("Location: ".OVA_getabsoluteuri()."../logout.php");

}

  
?>
