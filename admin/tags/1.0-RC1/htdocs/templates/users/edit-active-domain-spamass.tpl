<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAdminEdit_active_domain_spamass_welcome'] ." <b> $domain</b>." ; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
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
   <tr>
      <td align="center" colspan="3">
         <input type="hidden" name="domain" value="<?php print $domain; ?>" >
         <input type="submit" name="submit" value="<?php print $PALANG['pAdminEdit_active_domain_spamass_button']; ?>" />
      </form>
      </td>
   </tr>
</table>
<p />
