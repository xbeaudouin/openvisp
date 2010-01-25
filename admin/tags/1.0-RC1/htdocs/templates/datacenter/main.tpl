<p>
<center>

<table class="auto">
   <tr>
      <td colspan="2" nowrap>
         <?php print $PALANG['pDataCenter_welcome'] . "\n"; ?>
         <p />
      </td>
   </tr>
   <tr>
      <td colspan="2" nowrap>
         <?php print $PALANG['pDataCenter_welcome_text'] . "\n"; ?>
         <p />
      </td>
   </tr>
   <tr>
      <td nowrap>
         <a target="_top" href="requests.php"><?php print $PALANG['pDataCenter_requests']; ?></a>
      </td>
      <td>
         <?php print $PALANG['pDataCenter_requests_text'] . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td nowrap>
         <a target="_top" href="apc.php"><?php print $PALANG['pDataCenter_apc']; ?></a>
      </td>
      <td>
         <?php print $PALANG['pDataCenter_apc_text'] . "\n"; ?>
      </td>
   </tr>
   <tr>
      <td nowrap>
         <a target="_top" href="stats.php"><?php print $PALANG['pDataCenter_stats']; ?></a>
      </td>
      <td>
         <?php print $PALANG['pDataCenter_stats_text'] . "\n"; ?>
      </td> 
   </tr>
<?php
 if (is_datacenter_full($SESSID_USERNAME)) {
   print"<tr><td colspan=\"2\"  nowrap><p/>".$PALANG['pDataCenter_su_welcome_text']."<p/></td></tr>";
   print"<tr><td><a target=\"_top\" href=\"admin_apc.php\">".$PALANG['pDataCenter_su_admin_apc']."</a></td><td nowrap>".$PALANG['pDataCenter_su_admin_apc_text']."</td></tr>";
   print"<tr><td><a target=\"_top\" href=\"admin_stats.php\">".$PALANG['pDataCenter_su_admin_stats']."</a></td><td nowrap>".$PALANG['pDataCenter_su_admin_stats_text']."</td></tr>";
   print"<tr><td><a target=\"_top\" href=\"admin_list.php\">".$PALANG['pDataCenter_su_admin_users']."</a></td><td nowrap>".$PALANG['pDataCenter_su_admin_users_text']."</td></tr>";
}
?>
   <tr><td colspan="2" nowrap>&nbsp;</td></tr>
   <tr>
      <td nowrap>
         <a target="_top" href="../main.php"><?php print $PALANG['pDataCenter_return_to_main']; ?></a>
      </td>
      <td>
         <?php print $PALANG['pDataCenter_return_to_main_text'] . "\n"; ?>
      </td>
   </tr>
</table>
