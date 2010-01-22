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
//  fFtp
//  fWebsite
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_admin_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
	header ("list-account.php");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $username    = get_post('username');
	 $action = get_post('action');
	 $result_text = "";

   if ($username != NULL)
   {
		 if ( $action == "block" ){
			 $result = db_query ("UPDATE accounts SET enabled='0', paid='0',modified=NOW() WHERE username='$username'");
			 if ($result['rows'] == 1){
				 $result_text .= $PALANG['lock_user_account_successfull']."<br />";
			 }
			 else{
				 $result_text .= $PALANG['lock_user_account_unsuccessfull']."<br />";
			 }

			 $domain_list = list_domains_for_admin ($username);
			 for ( $i=0; $i < sizeof($domain_list); $i++){
				 $result_text .= $domain_list[$i]."<br />";
				 $result_text .= modify_domain_ftp($domain_list[$i],"0")."<br/>";
				 $result_text .= modify_domain_mailbox($domain_list[$i],"0")."<br/>";
				 $result_text .= "<hr>";
			 }
		 }

		 if ( $action == "unblock" ){
			 $result = db_query ("UPDATE accounts SET enabled='1', paid='1',modified=NOW() WHERE username='$username'");

			 if ($result['rows'] == 1){
				 $result_text .= $PALANG['unlock_user_account_successfull']."<br />";
			 }
			 else{
				 $result_text .= $PALANG['unlock_user_account_unsuccessfull']."<br />";
			 }

			 $domain_list = list_domains_for_admin ($username);
			 for ( $i=0; $i < sizeof($domain_list); $i++){
				 $result_text .= $domain_list[$i]."<br />";
				 $result_text .= modify_domain_ftp($domain_list[$i],"1")."<br/>";
				 $result_text .= modify_domain_mailbox($domain_list[$i],"1")."<br/>";
				 $result_text .= "<hr>";
			 }

		 }

		 //		 header("Location: list-accounts.php");
        
	 }



   include ("../templates/header.tpl");
   include ("../templates/accounts/menu.tpl");
	 //   include ("../templates/accounts/list-account.tpl");

	 print $result_text;

   include ("../templates/footer.tpl");
}

?>
