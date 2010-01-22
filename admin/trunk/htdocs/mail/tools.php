<?php
//
// File: tools.php
//
// Template File: tools.tpl
//
// Template Variables:
//
// tDomain
//
// Form POST \ GET Variables:
//
// fDomain
//

require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();
$list_domains = list_local_domains_for_admin ($SESSID_USERNAME);
$overview2 = "YES";

if ($_SERVER['REQUEST_METHOD'] == "GET")
{

	 $tDomain = get_get('domain');

   if ( $tDomain != NULL ) {
     if (!check_owner($SESSID_USERNAME, $tDomain)) {
       // Be paranoid. If someone is trying to get acces to a
       // a domain that is not in charge, then logout the user
       // directly.
       header ("Location: logout.php");
     }

		 $mailbox_list = get_domain_mailbox($tDomain);
		 //		 $mailbox_list += get_domain_aliases($tDomain);

   } else {
     header ("Location: overview.php");
   }

   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/tools-get.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{

	$fForm_name = get_post('fForm_name');

	switch ($fForm_name) {
	case "reset_quar_status" : $fUser = get_post('fUser');
		$fBeginDate = get_post('fBeginDate');
		$fEndDate = get_post('fEndDate');
		
		$update_status = reset_quarantine_status($fUser, $fBeginDate, $fEndDate);

		if ( $update_status['rows'] > 0 ){	$tMessage = $PALANG['pTools_reset_quarantine_status_successfull'];
		}else{		$tMessage = $PALANG['pTools_reset_quarantine_status_unsuccessfull'];	}

		$template = "tools-get.tpl";

		$tDomain = get_post('fDomain');
		$mailbox_list = get_domain_mailbox($tDomain);
		
		break;

	case "clean_quarantine" : $fUser = get_post('fUser');
		$update_status = clean_quarantine($fUser);
		if ( $update_status['rows'] > 0 ){	$tMessage = $update_status." ".$PALANG['pTools_reset_quarantine_status_successfull'];
		}else{		$tMessage = $update_status." ".$PALANG['pTools_reset_quarantine_status_unsuccessfull'];	}

		$template = "tools-get.tpl";

		$tDomain = get_post('fDomain');
		$mailbox_list = get_domain_mailbox($tDomain);

		break;

	case "search_mail" :
		$fEmailFrom = "%".get_post('fEmailFrom')."%";
		$fEmailTo = "%".get_post('fEmailTo')."%";
		$fBeginDate = get_post('fBeginDate');
		$fEndDate = get_post('fEndDate');

		if ( ereg ("([0-9]{8})", $fBeginDate ) ){	$begin = "1";	}
		else{	$begin = "0";	}

		if ( ereg ("([0-9]{8})", $fEndDate ) ){	$end = "1";	}
		else{	$end = "0";	}

		if ( $end == 1 && $begin == 1 ){
			$result = search_email($fEmailFrom, $fEmailTo, $fBeginDate, $fEndDate);
			$template = "tools-searchmail.tpl";
		}
		else{
			$tMessage = $PALANG['pTools_find_mail_error_on_date'];
			$template = "tools-get.tpl";
		}


		break;



	case "greylisting_status" : $fUser = get_post('fUser');
		$tDomain = get_post('fDomain');
		$tButton = get_post('fButton');
		print "$tButton<br/>";
		$mailbox_list = get_domain_mailbox($tDomain);
		$template = "tools-get.tpl";
		break;

	default  : print "Default";
		break;
	}

	include ("../templates/header.tpl");
	include ("../templates/mail/menu.tpl");
	include ("../templates/mail/$template");
	include ("../templates/footer.tpl");
  

}



?>
