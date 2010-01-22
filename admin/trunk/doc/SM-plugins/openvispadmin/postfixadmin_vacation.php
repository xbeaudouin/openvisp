<?php
/****************************************************************************************

    Author ......... Florian Kimmerl
    Contact ........ info@spacekoeln.de
    Home Site ...... http://www.spacekoeln.de/
    Program ........ postfixadmin
    Version ........ 0.3-1.4
    Purpose ........ Allows you to change your postfixadmin settings within squirrelmail
    File ........... postfixadmin_vacation.php

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

//global $VACCONFMESSAGE;
bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
textdomain('postfixadmin');
$VACCONFTXT = _("I will be away from <date> until <date>. For urgent matters you can contact <contact person>.");
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
$VACCONF = <<<EOM
$VACCONFTXT
EOM;

displayPageHeader($color, 'None');

$USERID_USERNAME = $username;
$tmp = preg_split ('/@/', $USERID_USERNAME);
$USERID_DOMAIN = $tmp[1];

if ($_SERVER['REQUEST_METHOD'] == "GET")
{
   $result = db_query("SELECT * FROM vacation WHERE email='$USERID_USERNAME'");
   if ($result['rows'] == 1)
   {
      $row = mysql_fetch_array($result['result']);
      bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  textdomain('postfixadmin');
      $tMessage = _("You already have an auto response configured!");
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
	  bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  textdomain('postfixadmin');
echo "<table bgcolor=\"#b8cbdc\" align=\"center\" width=\"95%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\"><tr>
    <td align=\"center\"><b>". _("Options") ." - ". _("Auto Response") ."</b>
      <table align=\"center\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
      <tr><td bgcolor=\"$color[4]\" align=\"center\"><br>
            <table align=\"center\" width=\"70%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\"><tr>
                <td bgcolor=\"$color[3]\" align=\"center\"><b>". _("Auto Response") ."\n
                </b></td></tr><tr>
                <td bgcolor=\"$color[0]\" align=\"center\"><form name=\"vacation\" method=\"post\">
                <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
                      <tr>
                        <td> <center>
                            $tMessage<p>
                          </center></td>
                      </tr>
                      <tr>
                        <td> <div align=\"center\">
                            <input type=\"submit\" name=\"fBack\" value=\"" . _("Coming Back"). "\" />
                          </div></td>
                      </tr>
                    </table>
                    <TT></TT></FORM>
                </td>
              </tr><tr><td bgcolor=\"$color[4]\" align=\"left\">&nbsp;</td>
             </tr></table><BR></td></tr></table></td></tr></table>";
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
   }
   else
   {
		bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
   		textdomain('postfixadmin');
echo "<table bgcolor=\"$color[0]\" align=\"center\" width=\"95%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\">
  <tr>
    <td align=\"center\"><b>". _("Options") ." - ". _("Auto Response") ." </b>
      <table align=\"center\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
      <tr><td bgcolor=\"$color[4]\" align=\"center\"><br>
            <table align=\"center\" width=\"70%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\"><tr>
                <td bgcolor=\"$color[3]\" align=\"center\"><b>" . _("Auto Response") ."\n
                </b></td></tr><tr>
                <td bgcolor=\"$color[0]\" align=\"center\"><form name=\"vacation\" method=\"post\">$tMessage
                    <table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\"><tr>
                        <td width=\"23%\">". _("Subject") .":\n</td>
                        <td width=\"2%\">&nbsp;</td>
                        <td width=\"69%\"><input type=\"text\" name=\"fSubject\" value=\"" . _("Out of Office") . "\" /></td>
                        <td width=\"2%\">&nbsp;</td>
                        <td width=\"4%\">&nbsp;</td>
                      </tr><tr>
                        <td>". _("Body") .":\n</td>
                        <td>&nbsp;</td>
                        <td><textarea rows=\"10\" cols=\"80\" name=\"fBody\">$VACCONF\n
                        </textarea></td><td>&nbsp;</td><td>&nbsp;</td></tr><tr><td>&nbsp;</td><td>&nbsp;</td>
                        <td><input type=\"submit\" name=\"fAway\" value=\"" . _("Going Away") . "\" /></td>
                        <td>&nbsp;</td><td>&nbsp;</td></tr>
                    </table><TT></TT></FORM></td>
              </tr><tr><td bgcolor=\"$color[4]\" align=\"left\">&nbsp;</td>
              </tr></table><BR></td></tr></table></td></tr></table>";
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
      textdomain('squirrelmail');
   }
}

if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   $fSubject = $_POST['fSubject'];
   $fBody = $_POST['fBody'];
   $fAway = $_POST['fAway'];
   $fBack = $_POST['fBack'];

   $fBody = str_replace('\'','\\\'', $fBody);
   $fSubject = str_replace ('\'','\\\'', $fSubject);

   if (!empty ($fBack))
   {
      $result = db_query ("DELETE FROM vacation WHERE email='$USERID_USERNAME'");
      if ($result['rows'] != 1)
      {
         $error = 1;
         bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		 textdomain('postfixadmin');
         $tMessage = _("Unable to update your auto response settings!");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
      }
      else
      {
      	bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  	textdomain('postfixadmin');
        echo "<p align=center><b>". _("Your auto response has been removed!") ."</b></p>";
        bindtextdomain('squirrelmail', SM_PATH . 'locale');
		textdomain('squirrelmail');
        echo "<p align=center><a href=\"javascript:history.go(-1)\">". _("Click here to go back") ."</a></p>";

      }
   }

   if (!empty ($fAway))
   {
      $result = db_query ("INSERT INTO vacation (email,subject,body) VALUES ('$USERID_USERNAME','$fSubject','$fBody')");
      if ($result['rows'] != 1)
      {
         $error = 1;
         bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		 textdomain('postfixadmin');
         $tMessage = _("Unable to update your auto response settings!");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
      }
      else
      {
        bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
   		textdomain('postfixadmin');
         echo "<p align=center><b>". _("Your auto response has been set!") ."</b></p>";
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
         echo "<p align=center><a href=\"javascript:history.go(-1)\">". _("Click here to go back") ."</a></p>";
         exit;
      }
   }
}
?>
