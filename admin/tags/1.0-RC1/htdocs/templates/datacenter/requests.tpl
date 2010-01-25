<center>
<?php print $tMessage . "\n"; ?>
<table class="auto">
   <tr>
      <td align="center" colspan="2">
         <?php print $PALANG['pDataCenter_requests_welcome'] . "\n"; ?>
         <br />
         <br />
      </td>
   </tr>
         <form name="datacenter1" method="post">
   <tr>
      <td>
         <input type="radio" name="fWhat" value="tech">
      </td>
      <td>
         <?php print $PALANG['pDataCenter_requests_tech'] ;?>
      </td>
   </tr>
   <tr>
      <td>
         <input type="radio" name="fWhat" value="admin">
      </td>
      <td>
         <?php print $PALANG['pDataCenter_requests_admin']; ?>
      </td>
   </tr>
   <tr>
      <td align="center" colspan="2">
         <input type="submit" name="submit" value="<?php print $PALANG['pDataCenter_requests_continue']; ?>" />
         </form>
      </td>
   </tr>
</table>
