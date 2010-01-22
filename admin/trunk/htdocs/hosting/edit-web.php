<?php
//
// File: hosting/edit-website.php
//
// Template File: hosting/edit-website.tpl
//
// Template Variables:
//
// 
//
// Form POST \ GET Variables:
//
// fDomain
// fVhost
// fOptions
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

if ( $SESSID_USERNAME != "" )
{
  if (check_webhosting_admin($SESSID_USERNAME))
  {
    if ($_SERVER['REQUEST_METHOD'] == "GET")
    {

			$fVhost = get_get('vhost');
			$fDomain = get_get('domain');

			//if ( $fDomain != NULL && $fVhost != NULL ){
				$infovirtual = get_site_info($fDomain,$fVhost);
			//}
    
       include ("../templates/header.tpl");
       include ("../templates/hosting/menu.tpl");
       include ("../templates/hosting/edit-website.tpl");
       include ("../templates/footer.tpl");
    }
    
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
			
			$fDomain = get_post('fDomain');
			$fVhost = get_post('fVhost');
			$fOptions = get_post('fOptions');
			$infovirtual = get_site_info($fDomain,$fVhost);

			if ( $fDomain != NULL && $fVhost != NULL && $fOptions != NULL ){

				$array_options = "";
				foreach( $fOptions as $optionvalue) 
					{ 
						$array_options .= $optionvalue.", ";
					}
				$update_result = update_site_info($fDomain, $fVhost, $array_options);
				if ( $update_result != 1 ) {
					$tMessage = $PALANG['pWhostEdit_website_error'];

				}
				else {
					db_log ($SESSID_USERNAME, $fDomain, "Websites has been modified ($fOptions)", $fUsername);
					header("Location: ./list-webvirtual.php?username=$SESSID_USERNAME");
				}

			}

			include ("../templates/header.tpl");
			include ("../templates/hosting/menu.tpl");
			include ("../templates/hosting/edit-website.tpl");
			include ("../templates/footer.tpl");
    }
  }
}
  
?>
