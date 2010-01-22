<?php
/****************************************************************************************

    Author ......... Florian Kimmerl
    Contact ........ info@spacekoeln.de
    Home Site ...... http://www.spacekoeln.de/
    Program ........ postfixadmin
    Version ........ 0.3-1.4
    Purpose ........ Allows you to change your postfixadmin settings within squirrelmail
    File ........... postfixadmin_forward.php

    *************************************************************************************

    The Original Code is Postfix Admin.
    The Initial Developer of the Original Code is Mischa Peters .
    Portions created by Mischa Peters are Copyright (c) 2002, 2003, 2004.
    All Rights Reserved.
    Contributor(s):
    This project includes work by Mischa Peters and others that is:
    Copyright (c) 2002,2003,2004 Mischa Peters
    All rights reserved.

****************************************************************************************/

     chdir("..");
    if (!defined('SM_PATH'))
       define('SM_PATH','../');


    include_once (SM_PATH . 'plugins/postfixadmin/config.php');
    include_once(SM_PATH . 'plugins/postfixadmin/functions.inc.php');
    if (file_exists(SM_PATH . 'include/validate.php'))
       include_once(SM_PATH . 'include/validate.php');
    else if (file_exists(SM_PATH . 'src/validate.php'))
       include_once(SM_PATH . 'src/validate.php');
    include_once(SM_PATH . 'functions/page_header.php');
    include_once(SM_PATH . 'functions/display_messages.php');
    include_once(SM_PATH . 'functions/imap.php');
    if (file_exists(SM_PATH . 'functions/array.php'))
       include_once(SM_PATH . 'functions/array.php');
    if (file_exists(SM_PATH . 'src/load_prefs.php'))
       include_once(SM_PATH . 'src/load_prefs.php');
    else if (file_exists(SM_PATH . 'include/load_prefs.php'))
       include_once(SM_PATH . 'include/load_prefs.php');


displayPageHeader($color, 'None');

$USERID_USERNAME = $username;
$tmp = preg_split ('/@/', $USERID_USERNAME);
$USERID_DOMAIN = $tmp[1];

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
      $result = db_query ("SELECT * FROM alias WHERE address='$USERID_USERNAME'");
      if ($result['rows'] == 1)
      {
         $row = mysql_fetch_array ($result['result']);
         $tGoto = $row['goto'];
      }
   else
   {
      bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  textdomain('postfixadmin');
      $tMessage = _("Unable to locate alias!");
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
   }
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $pEdit_alias_goto = _("To");

   $fGoto = $_POST['fGoto'];

	$goto = preg_replace ('/\r\n/', ',', $fGoto);
	$goto = preg_replace ('/[\s]+/i', '', $goto);
	$goto = preg_replace ('/\,*$/', '', $goto);
	$array = preg_split ('/,/', $goto);

	for ($i = 0; $i < sizeof ($array); $i++) {
		if (in_array ("$array[$i]", $CONF['default_aliases'])) continue;
		if (empty ($array[$i])) continue;
		if (!check_email ($array[$i]))
		{
   		$error = 1;
   		$tGoto = $goto;
        bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
    	textdomain('postfixadmin');
   		$tMessage = _("The email address that you have entered is not valid:") . " $array[$i]</font>";
		bindtextdomain('squirrelmail', SM_PATH . 'locale');
		textdomain('squirrelmail');
	   }
   }

   if ($error != 1)
   {
      if (empty ($fGoto))
      {
         $goto = $USERID_USERNAME;
      }
      else
      {
         $goto = $USERID_USERNAME . "," . $goto;
      }

      $result = db_query ("UPDATE alias SET goto='$goto',change_date=NOW() WHERE address='$USERID_USERNAME'");
      if ($result['rows'] != 1)
      {
          bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		  textdomain('postfixadmin');
         $tMessage = _("Unable to modify the alias!");
          bindtextdomain('squirrelmail', SM_PATH . 'locale');
		  textdomain('squirrelmail');
      }
      else
      {
         db_log ($USERID_USERNAME, $USERID_DOMAIN, "edit alias", "$USERID_USERNAME -> $goto");

      	 bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  	 textdomain('postfixadmin');
         echo "<p align=center><b>". _("Alias successfully changend!"). "\n</b></p>";
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
         echo "<p align=center><a href=\"javascript:history.go(-1)\">". _("Click here to go back") ."</a></p>";
         exit;
      }
   }
}
bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
textdomain('postfixadmin');
echo "<table bgcolor=\"$color[0]\" align=\"center\" width=\"95%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\">
        <tr>
        <td align=\"center\" bgcolor=\"$color[0]\" colspan=\"2\">
        <b>". _("Options") ." - ". _("Edit Alias"). " </b>
        	  <table align=\"center\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">
        	  <tr>
        	  <td bgcolor=\"$color[4]\" align=\"center\"><table align=\"center\" width=\"100%\">
              <tr>
			  <td align=\"left\">". _("Edit an alias* for your domain.<br />One entry per line."). "\n
       		  </td>
    		  </tr>
    		  <tr>
      		  <td align=\"left\">". _("*Additional forward-aliase always recieve messages BBC!"). "\n
    		  </tr>
    		  <tr>
    		  <td align=\"left\">&nbsp;</td>
    		  </tr>
    		  </table>
<table align=\"center\" width\"95%\" cellpadding=\"5\" cellspacing=\"1\">
<form name=\"mailbox\" method=\"post\">
    <tr>
      <td bgcolor=\"$color[3]\" align=\"center\"><b>". _("Edit Alias"). "</b>
      </td>
      </tr>
      <tr>
      <td bgcolor=\"$color[5]\" align=\"center\">$tMessage
      <table cellpadding=\"5\" cellspacing=\"1\">
      <tr>
      <th align=\"left\">". _("Alias"). ":\n
      </th>
      <td align=\"left\">$USERID_USERNAME</td>
      </tr>
      <tr>
      <th>&nbsp;</th>
      <td>&nbsp;</td>
      </tr>
   		<tr>
      <th align=\"left\" valign=\"top\">". _("To"). ":\n
      </th>
      <td><textarea rows=\"8\" cols=\"50\" name=\"fGoto\">";
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
$array = preg_split ('/,/', $tGoto);
for ($i = 0 ; $i < sizeof ($array) ; $i++)
{
if (empty ($array[$i])) continue;
if ($array[$i] == $USERID_USERNAME) continue;
print "$array[$i]\n";
}
bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
textdomain('postfixadmin');
echo "</textarea>
    </td>
    </tr>
     <tr>
     <th>&nbsp;</th>
      <td>&nbsp;</td>
      </tr>
      <tr>
      <th>&nbsp;</th>
      <td align=\"left\"colspan=\"2\">
      <input type=\"submit\" name=\"submit\" value=\"" . _("Edit Alias") . "\"\n
      </td>
      </tr>
      <tr>
      </tr>
      </table></td>
      </tr>
      </table><p>&nbsp;</p></td>
      </tr>
      </table>
      </td></tr>
      </table>
    </form>";
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
?>
