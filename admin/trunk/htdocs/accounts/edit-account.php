<?php
//
// File: accounts/edit-account.php
//
// Template File: accounts/edit-account.tpl
//
// Template Variables:
//
//  tMail
//  tDatacenter
//  tFTP
//  tHTTP
//
// Form POST \ GET Variables:
//
//  fPassword1
//  fPassword2
//  fMail
//  fDatacenter
//  fDCManage
//  fFtp
//  fWebsite
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $username = get_get('username');
   $list_domains = list_domains_local ();
   $tDomains = list_domains_for_users($username);

   $account_information = get_account_info($username);
   $account_quota = get_account_quota($account_information['id']);
   $account_rights = get_account_right($account_information['id']);
   $total_used = get_account_used($username,check_admin($username));


   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/edit-account.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $username           = get_post('username');
   $account_information= get_account_info($username);
   $fPassword1         = get_post('fPassword1');
   $fPassword2         = get_post('fPassword2');
   $fMail              = get_post('fMail'); 
   $fDatacenter        = get_post('fDatacenter');
   $fFTP               = get_post('fFtp');
   $fHTTP              = get_post('fWebsite');
   $fActive            = get_post('fActive');
   $fDomains           = get_post('fDomains');
   $fDomainslist       = get_post('fDomainslist');
   $fCompany           = get_post('fCompany');
   $fAddress           = get_post('fAddress');
   $fCity              = get_post('fCity');
   $fPostalCode        = get_post('fPostalCode');
   $fWeburl            = get_post('fWeburl');
   $fEmail             = get_post('fEmail');
   $fPhone             = get_post('fPhone');
   $fFax               = get_post('fFax');
   $fEmailsupport      = get_post('fEmailsupport');
   $fPhonesupport      = get_post('fPhonesupport');
   $fWebsupport        = get_post('fWebsupport');
   $fWebfaq            = get_post('fWebfaq');
   $fManage            = get_post('fManage');
   $fDCManage          = get_post('fDCManage');
   $fMysql             = get_post('fMysql');
   $fPostgresql        = get_post('fPostgresql');
   $fNmysqlusers       = get_post('fNmysqlusers');
   $fNmysqldb          = get_post('fNmysqldb');
   $fNpostgresqlusers  = get_post('fNpostgresqlusers');
   $fNpostgresqldb     = get_post('fNpostgresqldb');
   $fNdomains          = get_post('fNdomains');
   $fNwebsite          = get_post('fNwebsite');
   $fNwebsitealias     = get_post('fNwebsitealias');
   $fNftpaccount       = get_post('fNftpaccount');
   $fNbemail           = get_post('fNbmail');
   $fNbemailalias      = get_post('fNbmailalias');

   if($fDatacenter == NULL) $fDatacenter = "off";
   if($fFTP == NULL)        $fFTP        = "off";
   if($fHTTP == NULL)       $fHTTP       = "off";
   if($fMail == NULL)       $fMail       = "off";
   if($fDomains == NULL)    $fDomains    = "off";
   if($fPostgresql == NULL) $fPostgresql = "off";

   if ($username == NULL)
   {
      $error = 1;
   }

   if ($fPassword1 != $fPassword2)
   {
      $error = 1;
   }

   if ($error != 1)
    {
      if (($fPassword1 == $fPassword2) && ($fPassword1 != NULL))
      {
        $password = pacrypt ($fPassword1);

        $result = db_query ("UPDATE accounts SET password='$password',modified=NOW() WHERE username='$username'");
      }

			if ($fActive == "on") $fActive = 1;

			$result = db_query ("UPDATE accounts
SET company='$fCompany',address='$fAddress',city='$fCity',postal_code='$fPostalCode', weburl='$fWeburl', email='$fEmail', phone='$fPhone',
fax='$fFax', emailsupport='$fEmailsupport', phonesupport='$fPhonesupport', websupport='$fWebsupport', webfaq='$fWebfaq',
modified=NOW(),enabled='$fActive'
WHERE username='$username'");

      if ($fMail == "on")       { $fMail = 1; }       else { $fMail = 0; }
      if ($fDatacenter == "on") { $fDatacenter = 1; } else { $fDatacenter = 0; }
      if ($fFTP == "on")        { $fFTP = 1; }        else { $fFTP = 0; }
      if ($fHTTP == "on")       { $fHTTP = 1; }       else { $fHTTP = 0; }
      if ($fDomains == "on")       { $fDomains = 1; }       else { $fDomains = 0; }
      if ($fMysql == "on")       { $fMysql = 1; }       else { $fMysql = 0; }
      if ($fPostgresql == "on")       { $fPostgresql = 1; }       else { $fPostgresql = 0; }


      if ( $fManage == "on" ){
	transform_sadmin($account_information['id'], $account_information['id']);
      } else {
	update_right_admin($account_information['id'],	$fMail,$fDomains,$fDatacenter,$fFTP,$fHTTP,$fMysql,$fPostgresql);
	update_quota_admin($account_information['id'],	$fNmysqlusers, $fNmysqldb, $fNpostgresqlusers, $fNpostgresqldb, $fNdomains,
			 				$fNwebsite, $fNwebsitealias, $fNftpaccount, $fNbemail, $fNbemailalias);
      }
      if ( $fDCManage == "on" ) {
	transform_datacenter_sadmin($account_information['id'],1);
      } else {
	transform_datacenter_sadmin($account_information['id'],0);
      }

      if (isset ($fDomainslist[0]))
      {
	$result = db_query ("DELETE FROM domain_admins WHERE accounts_id='".$account_information['id']."'");
	for ($i = 0; $i < sizeof ($fDomainslist); $i++)
	{
		$domain = $fDomainslist[$i];
		$result = db_query ("INSERT INTO domain_admins (accounts_id,domain_id,created) VALUES ('".$account_information['id']."','$domain',NOW())");
	}
      }

      header("Location: list-accounts.php");
    }

   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/edit-account.tpl");
   include ("../templates/footer.tpl");
}

?>
