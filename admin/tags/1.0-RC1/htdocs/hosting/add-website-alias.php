<?php
//
// File: hosting/add-website-alias.php
//
// Template File: hosting/add-website-alias.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fAlias
// fWebsite
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ( $SESSID_USERNAME != "" )
{
  if (check_webhosting_admin($SESSID_USERNAME))
  {
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
       include ("../templates/hosting/add-website-alias.tpl");
       include ("../templates/footer.tpl");
    }
    
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
       if (isset ($_GET['username']))
       {
         $fUsername = escape_string($_GET['username']);
         
         if (isset ($_POST['fWebsite'])) $fWebsite = get_post('fWebsite');
         if (isset ($_POST['fAlias'])) $fAlias = get_post('fAlias');
         
         if(isset ($fWebsite))
         {
          if($fWebsite != '')
          {
	    $domainParts = split('[.]',$fWebsite);
	    if(sizeof($domainParts) == 3)
	    {
	      $vhost = $domainParts[0];
	      $domain = $domainParts[1] . "." . $domainParts[2];
	    }
	    else {
	      $vhost = '';
	      $domain = $domainParts[0] . "." . $domainParts[1];
            }
            $result = db_query ("SELECT id,DocumentRoot FROM whost WHERE domain = '$domain' AND vhost = '$vhost' AND isalias='0'");
            if ($result['rows'] > 0)
            {
		$row = db_array($result['result']);
		$websiteID = $row['id'];

		if(isset($fAlias) && $fAlias != '')
		{
		  $aliasParts = split('[.]',$fAlias);
		  if(sizeof($aliasParts) == 3)
		  {
		    $vhost = $aliasParts[0];
		    $domain = $aliasParts[1] . "." . $aliasParts[2];
		  }
		  else {
		    $vhost = '';
		    $domain = $aliasParts[0] . "." . $aliasParts[1];
		  }
		}
		$result = db_query ("INSERT INTO whost (vhost,domain,DocumentRoot,isalias,created,modified,active) VALUES ('$vhost','$domain','$row[DocumentRoot]','$websiteID',NOW(),NOW(),'1')");
		db_log ($SESSID_USERNAME, $fWebsite, "Website alias $fAlias has been added", $fUsername);
                header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
            }
	    else {
		//header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
		$tMessage = "id non trouve: $vhost / $domain";
	    }
          }
         }
       }
       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       include ("../templates/hosting/add-website-alias.tpl");
       include ("../templates/footer.tpl");
    }
  }
}
  
?>
