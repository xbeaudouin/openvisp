<form action="policyd_bl_sender.php" method="POST">
	 <?php print $PALANG['pPolicyd_form_sender'];?> : <input type="text" name="fSender"> 
	 <?php print $PALANG['pPolicyd_form_desc'];?> : <input type="text" name="fDesc" size="40">
	 <?php print $PALANG['pPolicyd_form_exp'];?> : <input type="text" name="fExp" size="4">
  <input type="submit">
</form>

<table>
<tr class="header">
  <td><?php print $PALANG['pPolicyd_table_sender'];?></td>
  <td><?php print $PALANG['pPolicyd_table_desc'];?></td>
  <td><?php print $PALANG['pPolicyd_table_exp'];?></td>
  <td><?php print $PALANG['pPolicyd_table_action'];?></td>
</tr>

<?php

	if ((is_array ($list_blacklist_sender) and sizeof ($list_blacklist_sender) > 0)){
		for ($i = 0; $i < sizeof ($list_blacklist_sender); $i++){
			print "<tr>";
			print "<td>".$list_blacklist_sender[$i]['_blacklist']."</td>";
			print "<td>".$list_blacklist_sender[$i]['_description']."</td>";
			if ( $list_blacklist_sender[$i]['_expire'] == "0" ){
				print "<td>".$PALANG['pPolicyd_never']."</td>";
			}
			else{
				print "<td>".date('Y-m-d H:i:s', $list_blacklist_sender[$i]['_expire'])."</td>";
			}
			print "<td>";
			print "  <a href=\"delete_pol.php?fsrc=email&femail=".$list_blacklist_sender[$i]['_blacklist']."\">".$PALANG['pPolicyd_delete']."</a>";
			print "</td>";
			print "</tr>";
		}
	}

?>

</table>