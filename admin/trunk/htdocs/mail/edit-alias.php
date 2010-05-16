<?php
//
// File: edit-alias.php
//
// Template File: mail/edit-alias.tpl
//
// Template Variables:
//
// tMessage
// tGoto
//
// Form POST \ GET Variables:
//
// fAddress
// fDomain
// fGoto
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
include ("../languages/" . check_language () . ".lang");

require_once ("MDB2.php");
require_once ("../lib/db.class.php");
require_once ("../lib/user.class.php");
require_once ("../lib/domain.class.php");
require_once ("../lib/mail.class.php");


$SESSID_USERNAME = check_user_session ();

$ovadb = new DB();
$user_info = new USER($ovadb);
$user_info->fetch_info($SESSID_USERNAME);

$domain_info = new DOMAIN($ovadb);


if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fAddress = get_get('address');
   $fDomain  = get_get('domain');
   $tDomain  = $fDomain;
	 $overview2 = "YES";

   if ( $fDomain != NULL)
   {
		 
      $domain_info->fetch_by_domainname($fDomain);
      $user_info->check_domain_access($domain_info->data_domain['id']);
      $domain_info->fetch_mail_aliases();


			$overview2 = 1;
			$mail_info = new MAIL($ovadb);
			$mail_info->fetch_alias_info($fAddress);
			$mail_info->data_alias['goto'] = ereg_replace(',,',',', $mail_info->data_alias['goto']);
			$mail_info->data_alias['goto'] = ereg_replace(',',' ', $mail_info->data_alias['goto']);
			$domain_info->fetch_mailboxes();
			
	 }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/edit-alias.tpl");
   include ("../templates/footer.tpl");
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $pEdit_alias_goto = $PALANG['pEdit_alias_goto'];
   
   $fAddress = get_post('fAddress');
   $fAddress = strtolower ($fAddress);
   $fDomain  = get_post('fDomain');
   $tDomain  = $fDomain;
   $fGoto    = get_post('fGoto');
	 $fCheckalias = get_post('check_alias');

	 if ( $fDomain != NULL ){

      $domain_info->fetch_by_domainname($fDomain);
      $user_info->check_domain_access($domain_info->data_domain['id']);
      $domain_info->fetch_mail_aliases();

	 }



   if ( $fCheckalias != NULL )
   {
		 $fGoto = preg_replace('/^,/', '', $fGoto ."," .implode(",", $fCheckalias) );
   }

   $fGoto    = strtolower ($fGoto);

   
   if (empty ($fGoto))
   {
      $error = 1;
      $tGoto = $fGoto;
      $tMessage = $PALANG['pEdit_alias_goto_text_error1'];
   }

   $goto = preg_replace ('/\r\n/', ',', $fGoto);
   $goto = preg_replace ('/[\s]+/i', '', $goto);
   $goto = preg_replace ('/\,*$/', '', $goto);
   $goto = ereg_replace (',,', ',', $goto);
   $goto = ereg_replace ('/^,/', '', $goto);
   $array = preg_split ('/,/', $goto);


   for ($i = 0; $i < sizeof ($array); $i++)
   {
      if (in_array ("$array[$i]", $CONF['default_aliases'])) continue;
      if (empty ($array[$i])) continue;
      if (!check_email ($array[$i]))
      {
        $error = 1;
				$tGoto = $goto;
				$tMessage = $PALANG['pEdit_alias_goto_text_error2'] . "$array[$i]</div>";
      }
   }
   
   if ($error != 1)
   {
      $result = db_query ("UPDATE alias SET goto='$goto' WHERE address='$fAddress'");
      if ($result['rows'] != 1)
      {
         $tMessage = $PALANG['pEdit_alias_result_error'];
      }
      else
      {
         db_log ($SESSID_USERNAME, $fDomain, "edit alias", "$fAddress -> $goto");
               
         header ("Location: overview.php?domain=$fDomain");
         exit;
      }
   }
   
   include ("../templates/header.tpl");
   include ("../templates/mail/menu.tpl");
   include ("../templates/mail/edit-alias.tpl");
   include ("../templates/footer.tpl");
}
?>
