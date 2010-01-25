<?php
/****************************************************************************************

    Author ......... Florian Kimmerl
    Contact ........ info@spacekoeln.de
    Home Site ...... http://www.spacekoeln.de/
    Program ........ postfixadmin
    Version ........ 0.3-1.4
    Purpose ........ Allows you to change your postfixadmin settings within squirrelmail
    File ........... postfixadmin_changepass.php

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
if (!defined('SM_PATH'))define('SM_PATH','../');

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

global $username;

displayPageHeader($color, 'None');

$USERID_USERNAME = $username;
$tmp = preg_split ('/@/', $USERID_USERNAME);
$USERID_DOMAIN = $tmp[1];

//if ($_SERVER['REQUEST_METHOD'] == "GET")
//{
//}
if ($_SERVER['REQUEST_METHOD'] == "POST")
{
   //$pPassword_password_text = _("pPassword_password_text");
   $fPassword_current = $_POST['fPassword_current'];
   $fPassword = $_POST['fPassword'];
   $fPassword2 = $_POST['fPassword2'];
   // testing - pass in cleartext for sasl-auth on suse linux with plain text
//   $clearpass = $_POST['fPassword'];
   // testing - pass in cleartext for sasl-auth on suse linux with plain text
   $username = $USERID_USERNAME;

  	$result = db_query ("SELECT * FROM mailbox WHERE username='$username'");
   if ($result['rows'] == 1)
   {
      $row = mysql_fetch_array ($result['result']);
      $salt = preg_split ('/\$/', $row['password']);
      $checked_password = pacrypt ($fPassword_current, $salt[2]);

		$result = db_query ("SELECT * FROM mailbox WHERE username='$username' AND password='$checked_password'");
      if ($result['rows'] != 1)
      {
         $error = 1;
         bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		 textdomain('postfixadmin');
 		 $pPassword_password_current_text = _("You didn't supply your current password!");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
      }
   }
   else
   {
      $error = 1;
      bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	  textdomain('postfixadmin');
      $pPassword_email_text = _("The passwords that you supplied don't match!<br />Or are empty!");
      bindtextdomain('squirrelmail', SM_PATH . 'locale');
	  textdomain('squirrelmail');

   }

	if (empty ($fPassword) or ($fPassword != $fPassword2))
	{
	   $error = 1;
       bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
	   textdomain('postfixadmin');
       $pPassword_password_text = _("The passwords that you supplied don't match!<br />Or are empty!");
       bindtextdomain('squirrelmail', SM_PATH . 'locale');
	   textdomain('squirrelmail');

	}

   if ($error != 1)
   {
      $password = pacrypt ($fPassword);
      $result = db_query ("UPDATE mailbox SET password='$password',change_date=NOW() WHERE username='$username'");
      if ($result['rows'] == 1)
      {
         bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		 textdomain('postfixadmin');
         $tMessage = _("Your password has been changed!");
         $stMessage = _("Please sign out and log back again with your new password!");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');
         db_log ($USERID_USERNAME, $USERID_DOMAIN, "change password", "$USERID_USERNAME");

      }
      else
      {
         bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
		 textdomain('postfixadmin');
         $tMessage = _("Unable to change your password!");
         bindtextdomain('squirrelmail', SM_PATH . 'locale');
		 textdomain('squirrelmail');

      }
   }
}
bindtextdomain('postfixadmin', SM_PATH . 'plugins/postfixadmin/locale');
textdomain('postfixadmin');
echo "<table bgcolor=\"$color[0]\" align=\"center\" width=\"95%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\">
  <tr>
    <td align=\"center\"><b>". _("Options") ." - ". _("Change Password")." </b>
      <table align=\"center\" width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
      <tr><td bgcolor=\"$color[4]\" align=\"center\"><br>
            <table align=\"center\" width=\"95%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\"><tr>
                <td bgcolor=\"$color[3]\" align=\"center\"><b>" ._("Change your login password") ."\n
                </b></td>
              </tr>
              <tr>
                <td bgcolor=\"$color[0]\" align=\"center\"><form name=\"mailbox\" method=\"post\">
                <b>$tMessage<b><font color=red><br>
                    <a href=\"../../src/signout.php\" target=\"_top\">$stMessage</a>
                    ".$pPassword_admin_text."\n
                    ".$pPassword_password_current_text."\n
                    ".$pPassword_password_text."\n
                    </b><table width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">
                      <tr>
                        <td width=\"37%\"><b>". _("Alias") . ":\n</td>
                        <td width=\"63%\">$USERID_USERNAME</td>
                      </tr>
                      <tr>
	                    <td><b>". _("Password current"). ":\n</td>
                        <td><input type=\"password\" name=\"fPassword_current\" size=\"30\" /></td>
                      </tr>
                      <tr>
                        <td><b>". _("Password new"). ":\n</td>
                        <td><input type=\"password\" name=\"fPassword\" size=\"30\" /></td>
                      </tr>
                      <tr>
                        <td><b>". _("Password new again"). ":\n</td>
                        <td><input type=\"password\" name=\"fPassword2\" size=\"30\" /></td>
                      </tr>
                      <tr>
                      <td>&nbsp;</td>
                      <td><input type=\"submit\" name=\"submit\" value=\"" ._("Change Password") . "\" /></td>
                      <td>&nbsp;</td>
                      </tr>
                    </table>
                    <TT></TT></FORM></td>
              </tr><tr><td bgcolor=\"$color[4]\" align=\"left\">&nbsp;</td>
              </tr></table><BR>
          </td>
        </tr></table></td></tr></table>";
bindtextdomain('squirrelmail', SM_PATH . 'locale');
textdomain('squirrelmail');
?>
