<?php
//
// File: edit-active-domain-antivir.php
//
// Template File: admin_edit-active-domain-antivir.tpl
//
// Template Variables:
//
// tAVactive
// tAVheader
//
// Form POST \ GET Variables:
//
// fAVactive
// fAVheader
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $domain = get_get('domain');
   $domain_policy = get_domain_policy ($domain);
   
   $tAVactive = $domain_policy['bypass_virus_checks'];
   $tAVheader = $domain_policy['bypass_header_checks'];
   
   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-active-domain-antivir.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $domain = get_post('domain');
	 $fAVactive = get_post('fAVactive');
   $fAVheader = get_post('fAVheader');


   if ( $fAVactive != NULL ) { $fAVactive = 'N'; } else { $fAVactive = 'Y'; }
   if ( $fAVheader != NULL ) { $fAVheader = 'N'; } else { $fAVheader = 'Y'; }

   $tAVactive = $fAVactive;
   $tAVheader = $fAVheader;

   
   $result = db_query ("UPDATE policy SET bypass_virus_checks='$fAVactive',bypass_header_checks='$fAVheader' WHERE domain='$domain'");
   if ($result['rows'] == 1)
   {
	header ("Location: list-domain.php");
   }
   else
   {
	$tMessage = $PALANG['pAdminEdit_active_domain_antivir_result_error'];
   }

   include ("../templates/header.tpl");
   include ("../templates/users/menu.tpl");
   include ("../templates/users/edit-active-domain-antivir.tpl");
   include ("../templates/footer.tpl");
}
?>
