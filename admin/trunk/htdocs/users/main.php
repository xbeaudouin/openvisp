<?php
//
// File: users/main.php
//
// Template File: users/main.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");



$ovadb = new DB();
$user_info = new USER($ovadb);
$ova_info = new OVA($ovadb); 

$SESSID_USERNAME = $ova_info->check_session();
$ova_info->debug_info("USERNAME $SESSID_USERNAME");

$user_info->fetch_info($SESSID_USERNAME);
$user_info->fetch_active_domains();
$domain_info = new DOMAIN($ovadb);

$user_info->fetch_quota_status();
$user_info->fetch_domains();

$body_class = 'class="yui3-skin-sam"';

if ( $SESSID_USERNAME != "admin@ova.local" ){
  $ajax_domain = new AJAX_YUI($ovadb);
  $ajax_domain->start("domain");
}

if ($_SERVER["REQUEST_METHOD"] == "GET")
{

   include ("../templates/header.tpl");
   include ("../templates/users/main.tpl");
   include ("../templates/footer.tpl");

}

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
   include ("../templates/header.tpl");
   include ("../templates/users/main.tpl");
   include ("../templates/footer.tpl");
}
?>
