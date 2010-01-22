<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pAdminEdit_active_domain_backupmx_welcome'] ." <b> $domain</b>." ; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
         <?php print $PALANG['pAdminEdit_active_domain_backupmx_transport'] . ":"; ?>
      </td>
      <td>
         <input type="text" name="fTransport" value="<?php print $tTransport; ?>" />
      </td>
      <td>
        <?php print $PALANG['pAdminEdit_active_domain_backupmx_transport_text'] ; ?>
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="hidden" name="domain" value="<?php print $fDomain; ?>" />
         <input type="submit" name="submit" value="<?php print $PALANG['pAdminEdit_active_domain_backupmx_button']; ?>" />
      </form>
      </td>
   </tr>
</table>
<p />
