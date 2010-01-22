<?php
//
// File: delete.php
//
// Template File: message.tpl
//
// Template Variables:
//
// tMessage
//
// Form POST \ GET Variables:
//
// fDelete
// fDomain
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

	$fDomain = get_get('domain');
	$fDomain_alias = get_get('domain_alias');

	if ( ($fDomain_alias) && !($fDomain) ){
		$fDomain = $fDomain_alias;
	}

   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
      $error = 1;
      $tMessage = $PALANG['pDelete_domain_error'] . "<b>$fDomain</b>!</div>";
   }
   else
   { 
		 if ( $fDomain_alias == NULL )
			 { $delete_status = delete_domain($fDomain); }
		 else
			 { $delete_status = delete_domain_alias ($fDomain_alias); }

		 $tMessage = $delete_status['message'];
		 $error = $delete_status['error'];

   }



   if ($error != 1)
   {
      header ("Location: ../users/list-domain.php");
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
