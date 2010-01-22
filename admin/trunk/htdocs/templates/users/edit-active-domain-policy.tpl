<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAdminEdit_active_domain_policy_welcome'] ." <b> $domain</b>." ; ?>
         <br />
         <br />
      </td>
   </tr>
</table>
<fieldset>
 <legend>AntiSpam</legend>
<form name="alias" method="post">
 <table>
   <tr>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_active'] . ":"; ?>
      </td>
      <td>
	 <?php $checked = ($tSaActive == 'N' ) ? 'checked' : ''; ?>
	 <input type="checkbox" name="fSaActive" <?php print $checked; ?> />
      </td>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_active_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_modsubj'] . ":"; ?>
      </td>
      <td>
	 <?php $checked = ($tSaModSubj == 'Y') ? 'checked' : ''; ?>
         <input type="checkbox" name="fSaModSubj" <?php print $checked; ?> />
      </td>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_modsubj_text'] ; ?>	
      </td>
   </tr>
   <tr>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_savalue'] . ":"; ?>
      </td>
      <td>
         <input type="text" name="fSavalue" value="<?php print $tSavalue; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_spamass_savalue_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
	<?php print $PALANG['pAdminEdit_active_domain_spamass_satag'] . ":"; ?>
      </td>
      <td>
         <input type="text" name="fSatag" value="<?php print $tSatag; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_spamass_satag_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_savalue2'] . ":"; ?>
      </td>
      <td>
	 <input type="text" name="fSavalue2" value="<?php print $tSavalue2; ?>" />
      </td>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_spamass_savalue2_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_spamass_savalueK'] . ":"; ?>
      </td>
      <td>
         <input type="text" name="fSavalueK" value="<?php print $tSavalueK; ?>" />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_spamass_savalueK_text'] ; ?>
      </td>
   </tr>
 </table>
</fieldset>

<br />
<fieldset>
 <legend>Antivirus</legend>
 <table>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_antivir_active'] . ":"; ?>
      </td>
      <td align="left">
         <?php $checked = ($tAVactive == 'N' ) ? 'checked' : ''; ?>
         <input type="checkbox" name="fAVactive" <?php print $checked; ?> />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_antivir_active_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_antivir_header'] . ":"; ?>
      </td>
      <td>
         <?php $checked = ($tAVheader == 'N') ? 'checked' : ''; ?>
         <input type="checkbox" name="fAVheader" <?php print $checked; ?> />
      </td>
      <td>
         <?php print $PALANG['pAdminEdit_active_domain_antivir_header_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_antivir_banned'] . ":"; ?>
      </td>
      <td>
	 <?php $checked = ($tAVbanned == 'N') ? 'checked' : ''; ?>
	 <input type="checkbox" name="fAVbanned" <?php print $checked; ?> />
      </td>
      <td>
	 <?php print $PALANG['pAdminEdit_active_domain_antivir_banned_text'] ; ?>
      </td>
   </tr>
 </table>
</fieldset>
<br />
<fieldset>
 <legend><?php print $PALANG['pAdminEdit_active_domain_warn']; ?></legend>
 <table>
   <tr>
     <td>
	 <?php print $PALANG['pAdminEdit_active_domain_warnvirusrecip'] . ":"; ?>
     </td>
     <td>
	 <?php $checked = ($tWarnVRcp == 'Y') ? 'checked' : ''; ?>
	 <input type="checkbox" name="fWarnVRcp" <?php print $checked; ?> />
     </td>
     <td>
	 <?php print $PALANG['pAdminEdit_active_domain_warnvirusrecip_text'] ; ?>
     </td>
   </tr>
   <tr>
     <td>
         <?php print $PALANG['pAdminEdit_active_domain_warnbannedrecip'] . ":"; ?>
     </td>
     <td>
         <?php $checked = ($tWarnBRcp == 'Y') ? 'checked' : ''; ?>
         <input type="checkbox" name="fWarnBRcp" <?php print $checked; ?> />
     </td>
     <td>
         <?php print $PALANG['pAdminEdit_active_domain_warnbannedrecip_text'] ; ?>
     </td>
   </tr>
   <tr>
     <td>
         <?php print $PALANG['pAdminEdit_active_domain_warnbadhrecip'] . ":"; ?>
     </td>
     <td>
         <?php $checked = ($tWarnBHRcp == 'Y') ? 'checked' : ''; ?>
         <input type="checkbox" name="fWarnBHRcp" <?php print $checked; ?> />
     </td>
     <td>
         <?php print $PALANG['pAdminEdit_active_domain_warnbadhrecip_text'] ; ?>
     </td>
   </tr>
 </table>
</fieldset>
 <table>
   <tr>
      <td align="center" colspan="3">
         <input class="button" type="submit" name="submit" value="<?php print $PALANG['pAdminEdit_active_domain_policy_button']; ?>" />
      </form>
      </td>
   </tr>
</table>
<p />
