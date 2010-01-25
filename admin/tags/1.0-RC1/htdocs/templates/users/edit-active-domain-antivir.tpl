<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAdminEdit_active_domain_antivir_welcome'] ." <b> $domain</b>." ; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
	 <?php print $PALANG['pAdminEdit_active_domain_antivir_active'] . ":"; ?>
      </td>
      <td>
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
      <td align="center" colspan="3">
         <input type="hidden" name="domain" value="<?php print $domain; ?>" >
         <input type="submit" name="submit" value="<?php print $PALANG['pAdminEdit_active_domain_antivir_button']; ?>" />
      </form>
      </td>
   </tr>
</table>
<p />
