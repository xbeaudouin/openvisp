<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="3">
         <?php print $PALANG['pDataCenter_requests_welcome'] . " : " . $PALANG['pDataCenter_requests_tech']; ?>
         <br />
         <br />
      </td>
   </tr>
   <form name="datacenter2" method="post">
   <input type="hidden" name="fWhat" value="tech_doit">
   <tr><td align="center" colspan="3">
       <?php print $PALANG['pDataCenter_requests_warning'] ."<br />" . $PALANG['pDataCenter_requests_warning_text']; ?>
       <br />
       <br />
       </td>
   </tr>
   <tr><td><input type="checkbox" name="fReboot"></td>
       <td colspan="2"><?php print $PALANG['pDataCenter_requests_tech_reboot'] ;?></td>
   </tr>
   <tr><td><input type="checkbox" name="fLed"></td>
       <td colspan="2"><?php print $PALANG['pDataCenter_requests_tech_led'] ;?></td>
   </tr>
   <tr><td><input type="checkbox" name="fScreen"></td>
       <td colspan="2"><?php print $PALANG['pDataCenter_requests_tech_screen'] ;?></td>
   </tr>
   <tr><td><input type="checkbox" name="fPing"></td>
       <td colspan="2"><?php print $PALANG['pDataCenter_requests_tech_ping'] ;?></td>
   </tr>
   <tr><td><input type="checkbox" name="fOthers"></td>
       <td><?php print $PALANG['pDataCenter_requests_tech_others'] ;?></td>
       <td><input type="text" name="fOthers_text" size="50"></td>
   </tr>
   <tr><td>&nbsp;</td>
       <td><?php print $PALANG['pDataCenter_requests_tech_comment'] ;?></td>
       <td><textarea name="fComment" rows="3" cols="40"></textarea></td>
   </tr>
   <tr>
      <td align="center" colspan="3">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_requests_continue']; ?>" />
         </form>
      </td>
   </tr>
</table>
