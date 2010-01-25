<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pDataCenter_requests_welcome'] . " : " . $PALANG['pDataCenter_requests_admin']; ?>
         <br />
         <br />
      </td>
   </tr>
   <form name="datacenter2" method="post">
   <input type="hidden" name="fWhat" value="admin_doit">
   <tr><td align="center" colspan="2">
       <?php print $PALANG['pDataCenter_requests_warning'] ."<br />" . $PALANG['pDataCenter_requests_warning_text']; ?>
       <br />
       <br />
       </td>
   </tr>
   <tr><td><?php print $PALANG['pDataCenter_requests_admin_type'] ;?></td>
       <td><textarea name="fType" rows="3" cols="40"></textarea></td>
   </tr>
   <tr><td><?php print $PALANG['pDataCenter_requests_admin_datetime'] ;?></td>
       <td><input type="text" name="fDatetime" size="50"></td>
   </tr>
   <tr><td><?php print $PALANG['pDataCenter_requests_admin_names'] ;?></td>
       <td><input type="text" name="fNames" size="50"></td>
   </tr>
   <tr><td><?php print $PALANG['pDataCenter_requests_admin_comment'] ;?></td>
       <td><textarea name="fComment" rows="3" cols="40"></textarea></td>
   </tr>
   <tr>
      <td align="center" colspan="2">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_requests_continue']; ?>" />
         </form>
      </td>
   </tr>
</table>
