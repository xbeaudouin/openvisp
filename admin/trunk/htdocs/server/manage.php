<?php
//
// File: server/manage.php
//
// Template File: server/manage.tpl
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
require_once ("../lib/domain.class.php");
require_once ("../lib/ajax_yui.class.php");
require_once ("../lib/ova.class.php");


$ovadb = new DB();
$user_info = new USER($ovadb);
$ova = new OVA($ovadb); 

$SESSID_USERNAME = $ova->check_session();

$user_info->fetch_info($SESSID_USERNAME);
$user_info->check_domain_admin();
$user_info->fetch_quota_status();

$domain_info = new DOMAIN($ovadb);

$body_class = 'class="yui3-skin-sam"';


if ($_SERVER["REQUEST_METHOD"] == "GET")
{

	$message="";

	include ("../templates/header.tpl");
	include ("../templates/server/menu.tpl");
	include ("../templates/server/manage.tpl");
	include ("../templates/footer.tpl");

}


?>
