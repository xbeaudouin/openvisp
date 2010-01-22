<form method="POST" action="policyd_bl.php">
   <?php print $PALANG['pPolicyd_form_ip'];?> : <input type="text" name="fIp"> 
	 <?php print $PALANG['pPolicyd_form_desc'];?> : <input type="text" name="fDesc" size="40">
	 <?php print $PALANG['pPolicyd_form_exp'];?>: <input type="text" name="fExp" size="4">
  <input type="submit">
</form>


<table>
<tr class="header">
  <td><?php print $PALANG['pPolicyd_table_ip'];?></td>
  <td><?php print $PALANG['pPolicyd_table_desc'];?></td>
  <td><?php print $PALANG['pPolicyd_table_exp'];?></td>
  <td><?php print $PALANG['pPolicyd_table_insert'];?></td>
  <td><?php print $PALANG['pPolicyd_table_action'];?></td>
</tr>

<?php

	if ((is_array ($list_blacklisted_host) and sizeof ($list_blacklisted_host) > 0)){
		for ($i = 0; $i < sizeof ($list_blacklisted_host); $i++){
			print "<tr>";
			print "<td>".$list_blacklisted_host[$i]['_blacklist']."</td>";
			print "<td>".$list_blacklisted_host[$i]['_description']."</td>";
			if ( $list_blacklisted_host[$i]['_expire'] == "0" ){
				print "<td>".$PALANG['pPolicyd_never']."</td>";
			}
			else{
				print "<td>".date('Y-m-d H:i:s', $list_blacklisted_host[$i]['_expire'])."</td>";
			}
			print "<td>".$list_blacklisted_host[$i]['createdate']."</td>";
			print "<td>";
			print "<a href=\"http://whois.domaintools.com/".$list_blacklisted_host[$i]['_blacklist']."\" target=\"new\">whois</a> ";
			print "<a href=\"delete_pol.php?fsrc=blacklist&fip=".$list_blacklisted_host[$i]['_blacklist']."\">".$PALANG['pPolicyd_delete']."</a>";
			print "</td>";
			print "</tr>";
		}
	}

?>

</table>