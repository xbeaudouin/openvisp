<?php
//
// File: edit-security.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fUsername
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
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

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fUsername = get_get('username');
   $fDomain   = get_get('domain');
   $fAddress  = get_get('address');


	 $domain_info->fetch_by_domainname($fDomain);
	 $user_info->check_domain_access($domain_info->data_domain['id']);
	 $domain_info->fetch_policy();


      $resultDom = db_query ("SELECT policy.id FROM policy, domain WHERE domain.domain='$fDomain' AND domain.id=policy.domain_id");
      $rowDom = db_array ($resultDom['result']);

      $result = db_query ("SELECT policy_id FROM alias WHERE address='$fUsername'");

      if ($result['rows'] == 1) { 
				$row = db_array ($result['result']);
				if ( $row['policy_id'] == $domain_policy['id'] ) 
					{

						$result = db_query ("UPDATE alias SET policy_id='1' WHERE address='$fUsername' AND goto='$fUsername'");
						if ($result['rows'] == 0)
							{
								$error = 1;
								$tMessage = $PALANG['pEdit_security_mailbox_error2'];
							}
						if ($error != 1)
							{
								db_log ($SESSID_USERNAME, $fDomain, "security inactive", $fUsername);
							}
					}
				else
					{

						$result = db_query ("UPDATE alias SET policy_id='".$domain_policy['id']."' WHERE address='$fUsername' AND goto='$fUsername'");
						if ($result['rows'] == 0)
							{
								$error = 1;
								$tMessage = $PALANG['pEdit_security_alias_error'];
							}
						if ($error != 1)
							{
								db_log ($SESSID_USERNAME, $fDomain, "security active", $fUsername);
							}
					}
      }


			
			if ($error != 1)
				{
					header ("Location: overview.php?domain=$fDomain");
					exit;
				}


   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/message.tpl");
   include ("../templates/footer.tpl");
}
?>
