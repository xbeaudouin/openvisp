<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pEdit_mailbox_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="mailbox" method="post">
         <?php print $PALANG['pEdit_mailbox_username'] . ":\n"; ?>
      </td>
      <td>
			   <input type="hidden" name="fUsername" value="<?php print $fUsername; ?>">
			   <input type="hidden" name="fDomain" value="<?php print $fDomain; ?>">
         <?php print $fUsername . "\n"; ?>
      </td>
      <td>
         <?php print $pEdit_mailbox_username_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_mailbox_password'] . ":\n"; ?>
      </td>
      <td>
         <input type="password" name="fPassword" />
      </td>
      <td>
         <?php print $pEdit_mailbox_password_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pEdit_mailbox_password2'] . ":\n"; ?>
      </td>
      <td>
         <input type="password" name="fPassword2" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
  <?php
	  if ( $CONF['showpassword'] == 'YES' ){
	?>
	   <tr>
       <td><?php print $PALANG['pEdit_show_mailbox_password']."\n";?></td>
				<td><?php print $tPassword; ?></td>
     </tr>
  <?php
		}
  ?>
   <tr>
      <td>
         <?php print $PALANG['pEdit_mailbox_name'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fName" value="<?php print htmlspecialchars ($tName, ENT_QUOTES); ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
<?php
if ($CONF['quota'] == 'YES')
{
   $limit = get_domain_properties ($fDomain);
   if ($limit['maxquota'] != "0") {
   print "   <tr>\n";
   print "      <td>\n";
   print "         " . $PALANG['pEdit_mailbox_quota'] . " (";
   print $PALANG['pEdit_mailbox_quota_max'] . " 1-";
   switch ($limit['maxquota']) {
     case "-1": print "&infin;"; break;
     default  : print $limit['maxquota']; break;
   }
   print " " . $PALANG['pEdit_mailbox_quota_text'] . ") :";
   print "      </td>\n";
   print "      <td>\n";
   print "         <input type=\"text\" name=\"fQuota\" value=\"$tQuota\" />\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         $pEdit_mailbox_quota_text\n";
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
         <?php $checked = (!empty ($tActive)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fActive" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pSmtp_auth_active'] . ":\n"; ?>
       </td>
      <td>
         <?php $checked = (!empty ($tSmtpauth)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fSmtpauth" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pEdit_mailbox_button']; ?>" />
         </form>
      </td>
   </tr>
   <tr>
     <td align="center" colspan="3">
     <a href="./gen-pdf.php?username=<?php echo $fUsername;?>&domain=<?php echo $fDomain;?>&type=email">PDF</a>
     <a href="<?php print $_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."&gpass=true";  ?>"> <?php print $PALANG['pGenerate_new_password'] ?></a>

     </td>
   </tr> 
</table>
