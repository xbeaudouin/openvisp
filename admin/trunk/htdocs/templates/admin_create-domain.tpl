<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAdminCreate_domain_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="create_domain" method="post">
         <?php print $PALANG['pAdminCreate_domain_domain'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDomain" value="<?php print $tDomain; ?>" />
      </td>
      <td>
         <?php print $pAdminCreate_domain_domain_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminCreate_domain_description'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fDescription" value="<?php print $tDescription; ?>" />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminCreate_domain_aliases'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fAliases" value="<?php print $tAliases; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminCreate_domain_aliases_text'] . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminCreate_domain_mailboxes'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fMailboxes" value="<?php print $tMailboxes; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminCreate_domain_mailboxes_text'] . "\n"; ?>
      </td>
   </tr>
<?php
if ($CONF['quota'] == 'YES')
{
   print "   <tr>\n";
   print "      <td>\n";
   print "         " . $PALANG['pAdminCreate_domain_maxquota'] . ":\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         <input type=\"text\" name=\"fMaxquota\" value=\"$tMaxquota\" />\n";
   print "      </td>\n";
   print "      <td>\n";
   print "         " . $PALANG['pAdminCreate_domain_maxquota_text'] . "\n";
   print "      </td>\n";
   print "   </tr>\n";
}
?>
   <tr>
      <td>
         <?php print $PALANG['pAdminCreate_domain_defaultaliases'] . ":\n"; ?>
       </td>
      <td>
         <?php $checked = (!empty ($tDefaultaliases)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fDefaultaliases" <?php print $checked; ?> />
      </td>
      <td>
         <?php print $pAdminCreate_domain_defaultaliases_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_antivirus'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tAntivirus)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fAntivirus" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_spamass'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tSpamass)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fSpamass" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_vrfysender'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tVrfySender)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fVrfySender" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_vrfydomain'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tVrfyDomain)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fVrfyDomain" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_greylisting'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tGreyListing)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fGreyListing" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pAdminCreate_domain_spf'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tSPF)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fSPF" <?php print $checked; ?> />
      </td>
      <td>
         &nbsp;
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminCreate_domain_backupmx'] . ":\n"; ?>
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
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pAdminCreate_domain_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
<p />
