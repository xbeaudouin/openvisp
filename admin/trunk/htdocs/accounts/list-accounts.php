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



$admin_accounts = new ADMIN($ovadb);
//$admin_accounts->list();


$body_class = 'class="yui3-skin-sam"';

/*

$list_accounts = list_accounts ();
if ((is_array ($list_accounts) and sizeof ($list_accounts) > 0))
{
   for ($i = 0; $i < sizeof ($list_accounts); $i++)
   {
      $account_properties[$i] = get_account_properties ($list_accounts[$i]);
   }
}

*/

//if ( $admin_accounts->admin_l)

if ($_SERVER["REQUEST_METHOD"] == "GET")
{
   include ("../templates/header.tpl");
//   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
//   include ("../templates/accounts/menu.tpl");
   include ("../templates/accounts/list-accounts.tpl");
   include ("../templates/footer.tpl");
}
?>
