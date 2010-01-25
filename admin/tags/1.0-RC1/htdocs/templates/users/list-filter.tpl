<center>

<?php print $tMessage . "\n"; ?>

<table class="auto">

<tr>
<?php

	 print "<tr>\n";

print "<td>".$PALANG['pUsersFilterTable_priority']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_filtername']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_filterfield']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_fieldvalue']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_action']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_destination']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_active']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_comments']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_creationdate']."</td>\n";
print "<td>".$PALANG['pUsersFilterTable_modificationdate']."</td>\n";


print '</tr>';


if ( $table_filter != NULL ){

	for ($i = 0; $i < sizeof ($table_filter); $i++)
		{
			print "<tr>";
			print "<td>".$table_filter[$i]['exec_order']."</td>\n";
			print "<td>".$table_filter[$i]['filtername']."</td>\n";
			print "<td>".$table_filter[$i]['fieldname']."</td>\n";
			print "<td>".$table_filter[$i]['fieldvalue']."</td>\n";
			print "<td>".$table_filter[$i]['actionname']."</td>\n";
			print "<td>".$table_filter[$i]['destination']."</td>\n";
			$active = ($table_filter[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
			
			print "<td><a href=\"active-filter.php?num=".$table_filter[$i]['id']."\">".$active."</a></td>\n";
			print "<td>".$table_filter[$i]['comment']."</td>\n";
			print "<td>".$table_filter[$i]['created']."</td>\n";
			print "<td>".$table_filter[$i]['modified']."</td>\n";
			print '<td><a href="edit-filter.php?num='.$table_filter[$i]['id'].'">'.$PALANG['edit'] .'</a></td>'."\n";
			print '<td><a href="delete-filter.php?num='.$table_filter[$i]['id'].'">'.$PALANG['del'] .'</a></td>'."\n";
			print "</tr>";
		}
 }

?>


</table>