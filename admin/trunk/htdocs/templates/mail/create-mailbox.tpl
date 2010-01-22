<script type="text/javascript" src="../lib/ajax_mailbox.php"></script> 
<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pCreate_mailbox_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="mailbox" method="post">
         <?php print $PALANG['pCreate_mailbox_username'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fUsername" id="fUsername" value="<?php print $tUsername; ?>" onkeyup="check_mailaccount()" />
      </td>
      <td>
         <?php
	   print "<b>@" . $tDomain . "</b>";
	   print "<input name=\"fDomain\" id=\"fDomain\" type=\"hidden\" value=\"" . $tDomain . "\">";
         ?>
	      <div id='login_status' style='display:inline'></div>
      </td>
   </tr>

<?php
			if ( $CONF['force_password'] == "NO" ){
?>
   <tr>
      <td>
         <?php print $PALANG['pCreate_mailbox_password'] . ":\n"; ?>
      </td>
      <td>
         <input type="password" name="fPassword" />
      </td>
      <td>
         <?php print $pCreate_mailbox_password_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pCreate_mailbox_password2'] . ":\n"; ?>
      </td>
      <td>
         <input type="password" name="fPassword2" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

<?php
			}
?>

   <tr>
      <td>
         <?php print $PALANG['pCreate_mailbox_name'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fName" value="<?php print $tName; ?>" />
      </td>
      <td>
         <?php print $pCreate_mailbox_name_text . "\n"; ?>
      </td>
   </tr>
<?php
if ($CONF['quota'] == 'YES')
{
   $limit = get_domain_properties ($tDomain);
   if ($limit['maxquota'] != "0") {
   print "   <tr>\n";
   print "      <td>\n";
   print "         " . $PALANG['pCreate_mailbox_quota'] . " (";
   print $PALANG['pCreate_mailbox_quota_max'] ." 1-"; 
   switch ($limit['maxquota']) {
     case "-1": print "&infin;"; break;
     default  : print $limit['maxquota']; break;
   }
   print " " . $PALANG['pCreate_mailbox_quota_text'] . ") :";
   print "      </td>\n";
   print "      <td>\n";
   print "         <input type=\"text\" name=\"fQuota\" value=\"$tQuota\" />\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         $pCreate_mailbox_quota_text\n";
   print "      </td>\n";
   print "   </tr>\n";
   } 
}
?>
   <tr>
      <td>
         <?php print $PALANG['pCreate_mailbox_active'] . ":\n"; ?>
       </td>
      <td>
         <input type="checkbox" name="fActive" checked />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pCreate_mailbox_mail'] . ":\n"; ?>
       </td>
      <td>
         <input type="checkbox" name="fMail" checked />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
<?php
			if ( check_policyhosting() ){
?>
   <tr>
      <td>
         <?php print $PALANG['pGreylisting_active'] . ":\n"; ?>
       </td>
      <td>
         <input type="checkbox" name="fGreyListing" checked />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
<?php
					}
?>
   <tr>
      <td>
         <?php print $PALANG['pSmtp_auth_active'] . ":\n"; ?>
       </td>
      <td>
			 <?php
         $checked = "";
         if ( $limit['smtp_enabled'] ) $checked = "checked";
         print '<input type="checkbox" name="fSmtp_enabled" '.$checked.' />';
      ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
      <td>
         <?php print $PALANG['pPop3_active'] . ":\n"; ?>
       </td>
      <td>
			 <?php
         $checked = "";
         if ( $limit['pop3_enabled'] ) $checked = "checked";
         print '<input type="checkbox" name="fPop_enabled" '.$checked.' />';
      ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
      <td>
         <?php print $PALANG['pImap_active'] . ":\n"; ?>
       </td>
      <td>
			 <?php
         $checked = "";
         if ( $limit['imap_enabled'] ) $checked = "checked";
         print '<input type="checkbox" name="fImap_enabled" '.$checked.' />';
      ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
      <td>
         <?php print $PALANG['pSpam_report_active'] . ":\n"; ?>
       </td>
      <td>
			 <?php
         $checked = "";
         if ($CONF['spamreport'] == "YES") $checked = "checked";
         print '<input type="checkbox" name="fSpamreport" '.$checked.' />';
      ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input class="button_inactive" type="submit" id="submit" name="submit" value="<?php print $PALANG['pCreate_mailbox_button']; ?>" disabled/>
         </form>
      </td>
   </tr>
</table>
