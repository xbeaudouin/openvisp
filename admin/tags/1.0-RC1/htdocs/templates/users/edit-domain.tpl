<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pAdminEdit_domain_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
      <td align="center">
	       <form name="block" method="post">
           <?php
	 $lock_action = "unlock";
   $button_text = $PALANG['pAdminEdit_domain_unlock'];
	 if ( $tPaid == 1 ){
		 $lock_action = "lock";
		 $button_text = $PALANG['pAdminEdit_domain_lock'];
	 }
           ?>
           <input type="hidden" name="action" value="<?php print $lock_action;?>">
           <input type="hidden" name="domain" value="<?php print $domain; ?>">
           <input class="button" type="submit" value="<?php print $button_text;?>">
				 </form>
         <br />
	    </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
         <?php print $PALANG['pAdminEdit_domain_domain'] . ":\n"; ?>
      </td>
      <td>
         <?php print $domain . "\n"; ?>
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_description'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDescription" value="<?php print htmlspecialchars ($tDescription, ENT_QUOTES); ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_aliases'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fAliases" value="<?php print $tAliases; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_domain_aliases_text'] . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_mailboxes'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fMailboxes" value="<?php print $tMailboxes; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_domain_mailboxes_text'] . "\n"; ?>
      </td>
   </tr>
<?php
if ($CONF['quota'] == 'YES')
{
   print "   <tr>\n";
   print "      <td>\n";
   print "         " . $PALANG['pAdminEdit_domain_maxquota'] . ":\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         <input type=\"text\" name=\"fMaxquota\" value=\"$tMaxquota\" />\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         " . $PALANG['pAdminEdit_domain_maxquota_text'] . "\n";
   print "      </td>\n";
   print "   </tr>\n";
}

?>

   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_pop_allowed'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tPop3_enabled)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fPop3_enabled" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>


   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_imap_allowed'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tImap_enabled)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fImap_enabled" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>


   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_smtp_allowed'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tSmtp_enabled)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fSmtp_enabled" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>


<?php
if ( check_webhosting() ){

?>

   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_ftpaccount'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fFtpaccount" value="<?php print $tFtpaccount; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

<?php
}
			if ( check_dbhosting() ){
?>


   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_maxdb'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDbcount" value="<?php print $tDbcount; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>

   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_maxdbquota'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDbquota" value="<?php print $tDbquota; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>


   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_maxdbaccount'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDbusers" value="<?php print $tDbusers; ?>" />
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
         <?php print $PALANG['pAdminEdit_domain_vrfysender'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tVrfySender)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fVrfySender" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_vrfydomain'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tVrfyDomain)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fVrfyDomain" <?php print $checked; ?> />
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
         <?php print $PALANG['pAdminEdit_domain_greylisting'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tGreyListing)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fGreyListing" <?php print $checked; ?> />
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
         <?php print $PALANG['pAdminEdit_domain_vrfyspf'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tSPF)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fSPF" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_backupmx'] . ":\n"; ?>
      </td>
      <td>
         <?php $checked = (!empty ($tBackupmx)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fBackupmx" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_transport'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fTransport" value="<?php print $tTransport; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_active'] . ":\n"; ?>
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
         <?php print $PALANG['pAdminEdit_domain_pop'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fPdf_Pop" value="<?php print $tPdf_Pop; ?>" />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_imap'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fPdf_Imap" value="<?php print $tPdf_Imap; ?>" />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_smtp'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fPdf_Smtp" value="<?php print $tPdf_Smtp; ?>" />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_domain_webmail'] . ":\n"; ?>
       </td>
      <td>
         <input type="text" name="fPdf_Webmail" value="<?php print $tPdf_Webmail; ?>" />
      </td>
   </tr>
   <tr>
      <td valign="top">
         <?php print $PALANG['pAdminEdit_domain_address'] . ":\n"; ?>
       </td>
      <td>
			 <textarea name="fPdf_Address" rows="5"/><?php print $tPdf_Custadd; ?></textarea>
      </td>
   </tr>


   <tr>
      <td align="center" colspan="3">
         <input type="hidden" name="domain" value="<?php print $domain; ?>" >
         <input type="submit" name="submit" value="<?php print $PALANG['pAdminEdit_domain_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
<p />
