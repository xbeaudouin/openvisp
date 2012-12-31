<?php
//
// File: main.php
//
// Template File: main.tpl
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
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");


require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
//require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/admin.class.php");

$SESSID_USERNAME = check_user_session ();
$SESSID_USERNAME = check_admin_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_quota_status();


$admin_accounts = new ADMIN($ovadb);
$admin_accounts->list_admin_accounts();


$body_class = 'class="yui3-skin-sam"';


if ((is_array ($admin_accounts->admin_account_list) and sizeof ($admin_accounts->admin_account_list) > 0)){
  for ($i = 0; $i < sizeof ($admin_accounts->admin_account_list); $i++){
    $admin_accounts->fetch_admin_rights($admin_accounts->admin_account_list[$i]['username']);
    $account_properties[$i] = $admin_accounts->account_rights;
  }

}



if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}
?>
