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

$SESSID_USERNAME = check_user_session ();

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $fAddress = get_get('address');
   $fDomain  = get_get('domain');
   $tDomain  = $fDomain;
	 $overview2 = "YES";

   if (check_owner ($SESSID_USERNAME, $fDomain))
   {
		 $overview2 = 1;
      $result = db_query ("SELECT alias.* FROM alias WHERE address='$fAddress'");
      if ($result['rows'] == 1)
      {
         $row = db_array ($result['result']);
         $tGoto = $row['goto'];
      }
   }
   else
   {
      $tMessage = $PALANG['pEdit_alias_address_error'];
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

   if ( isset($_POST['check_alias'] ) )
   {
		 $fGoto = preg_replace('/^,/', '', $fGoto ."," .implode(",", get_post('check_alias')) );
   }

	 

   $fGoto    = strtolower ($fGoto);

   if (!check_owner ($SESSID_USERNAME, $fDomain))
   {
		  $overview2 = 1;
      $error = 1;
      $tGoto = $fGoto;
      $tMessage = $PALANG['pEdit_alias_domain_error'] . "$fDomain</font>";
   }   
   
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
