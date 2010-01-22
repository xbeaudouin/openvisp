<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pDataCenter_adminapc_apc_add'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <form name="add_apc" method="post" onSubmit="please_wait();">
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminapc_apc_name'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fName" value="<?php print $tName; ?>" />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminapc_apc_ip'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fIp" value="<?php print $tIp; ?>" />
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pDataCenter_adminapc_apc_print'] . ":\n"; ?>
       </td>
      <td>
         <?php $checked = (!empty ($tPrint)) ? 'checked' : ''; ?>
         <input type="checkbox" name="fPrint" <?php print $checked; ?> />
      </td>
   </tr>
   <tr>
      <td>
        <?php print $PALANG['pDataCenter_adminapc_apc_import_names'] . ":\n"; ?>
      </td>
      <td>
        <?php $checked = (!empty($tImportNames)) ? 'checked' : ''; ?>
        <input type="checkbox" name="fImportNames" <?php print $checked; ?> />
      </td>
   </tr>
   <tr>
      <td align="center" colspan="2">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_adminapc_apc_add_submit']; ?>" />
         </form>
      </td>
   </tr>
</table>
<p />
