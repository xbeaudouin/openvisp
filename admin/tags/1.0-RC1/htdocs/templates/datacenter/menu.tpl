<div id="menu">
<a target="_top" href="requests.php"><?php print $PALANG['pDataCenter_requests']; ?></a>&middot;
<a target="_top" href="apc.php"><?php print $PALANG['pDataCenter_apc']; ?></a>&middot;
<a target="_top" href="stats.php"><?php print $PALANG['pDataCenter_stats']; ?></a>&middot;
<?php
 if (is_datacenter_full($SESSID_USERNAME)) {
   print"<a target=\"_top\" href=\"admin_apc.php\">".$PALANG['pDataCenter_su_admin_apc']."</a>&middot;";
   print"<a target=\"_top\" href=\"admin_stats.php\">".$PALANG['pDataCenter_su_admin_stats']."</a>&middot;";
   print"<a target=\"_top\" href=\"admin_list.php\">".$PALANG['pDataCenter_su_admin_users']."</a>&middot;";
}
?>
<a target="_top" href="../users/main.php"><?php print $PALANG['pDataCenter_return_to_main']; ?></a>
</div>
<p>
