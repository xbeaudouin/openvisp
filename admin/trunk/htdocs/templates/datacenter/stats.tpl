<center>
<?php print $tMessage . "\n"; ?>

<table class="auto">
   <tr>
      <td align="center">
         <?php print $PALANG['pDataCenter_stats_welcome'] ; ?>
      </td>
   </tr>
<?php
  foreach($stats_list as $stat) {
    print "<tr><td><a href=\"stats.php?graph=$stat\"><img src=\"".stats2img($stat, 1)."\" alt=\"\" /></a></td></tr>"; 
  }
?>
</table>
<p />
