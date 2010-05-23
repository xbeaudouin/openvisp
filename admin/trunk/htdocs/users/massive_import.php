<?php
//
// File: massive_import.php
//
// Template File: massive_import_get.tpl massive_import_post.tpl
//
// Template Variables:
//
//
// Form POST \ GET Variables:
//
// 
// 
// 
//
require ("../variables.inc.php");


// Required Libs
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/ova.class.php");
require_once ("../lib/server.class.php");
require_once ("../lib/policyd.class.php");

$SESSID_USERNAME = check_user_session ();

$ovadb =& new DB();
$user_info =& new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);

include ("../languages/" . check_language() . ".lang");

require_once ("../lib/domain.class.php");
//require_once ("../lib/ajax_yui.class.php");

//$SESSID_USERNAME = check_user_session ();



$user_info->fetch_quota_status();
//$user_info->fetch_quota("mailboxes");

if ($_SERVER['REQUEST_METHOD'] == "GET"){
	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	include ("../templates/users/massive_import.tpl");
	include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST"){

	$fMassiveType = get_post("massive_type");

	$domain_info =& new DOMAIN($ovadb);
	$server_info =& new SERVER($ovadb);
	
	switch ($fMassiveType){

	case "mail_alias":
		$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['alias_file']['name']);
		move_uploaded_file($_FILES['alias_file']['tmp_name'], $uploadfile);
		$new_aliases=clean_empty_line($uploadfile);
		break;
		
	case "domains":
		$uploadfile = $CONF['uploaddir'] ."/". basename($_FILES['domain_file']['name']);                                                                                                
		move_uploaded_file($_FILES['domain_file']['tmp_name'], $uploadfile);

		$new_domains=clean_empty_line($uploadfile);
		debug_info("IMP DOM : ".sizeof($new_domains));
		$result = $domain_info->import_domains_list($new_domains);
		break;


	}

	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	print "Import RESULT :".$result['result']."<br/>";
	print "Import MESSAGE :".$result['message']."<br/>";
	print "<hr>";
	include ("../templates/users/massive_import.tpl");
	include ("../templates/footer.tpl");
}

?>