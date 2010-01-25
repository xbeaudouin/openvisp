<form method="POST" action="policyd_bl_helo.php">
  <?php print $PALANG['pPolicyd_form_helo'];?> : <input type="text" name="fHelo"> <input type="submit">
</form>

<table>
<tr class="header">
  <td><?php print $PALANG['pPolicyd_table_helo'];?></td>
  <td><?php print $PALANG['pPolicyd_table_action'];?></td>
</tr>

<?php
	if ((is_array ($list_blacklist_helo) and sizeof ($list_blacklist_helo) > 0)){
		for ($i = 0; $i < sizeof ($list_blacklist_helo); $i++){
			print "<tr>";
			print "<td>".$list_blacklist_helo[$i]['_helo']."</td>";
			print "<td><a href=\"delete_pol.php?fsrc=helo&fhelo=".$list_blacklist_helo[$i]['_helo']."\">".$PALANG['pPolicyd_delete']."</a></td>";
			print "</tr>";
		}
	}

?>

</table>