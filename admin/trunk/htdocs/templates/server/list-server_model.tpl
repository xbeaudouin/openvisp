<?php


if (sizeof ($list_server) > 0)
	{
?>
		<table>

			<tr>
			<td>Role</td>
			<td>Description</td>
			<td>Actif</td>
			<td>Action</td>
			</tr>

<?php
			
			for ($i = 0; $i < sizeof ($list_server); $i++)
				{
					if ((is_array ($list_server) and sizeof ($list_server) > 0))
						{
							print "<tr>\n";
							print "<td>".$list_server[$i]['role']."</td>";
							print "<td>".$list_server[$i]['description']."</td>";
							print "<td>".$list_server[$i]['active']."</td>";
							print "<td>".$list_server[$i]['active']."</td>";
							print "</tr>\n";
						}
				}

		
?>
			

			</table>
					
					
<?php
					
		}

?>