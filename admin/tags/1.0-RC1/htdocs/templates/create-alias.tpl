<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pCreate_alias_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
   <tr>
      <td>
         <form name="alias" method="post">
         <?php print $PALANG['pCreate_alias_address'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fAddress" value="<?php print $tAddress; ?>" />
      </td>
      <td>
         <?php 
           print "<b>@" . $tDomain . "</b>";
           print "<input name=\"fDomain\" type=hidden value=\"" . $tDomain . "\">";
           print $pCreate_alias_address_text . "\n";
         ?>
      </td>
   </tr>
   <tr>
      <td>
         <?php print $PALANG['pCreate_alias_goto'] . ":\n"; ?>
      </td>
      <td>
         <input type="text" name="fGoto" value="<?php print $tGoto; ?>" />
      </td>
      <td>
         <?php print $pCreate_alias_goto_text . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pCreate_alias_button']; ?>" />
         </form>
      </td>
   </tr>
</table>
<p />
<?php print $PALANG['pCreate_alias_catchall_text'] . "\n"; ?>
