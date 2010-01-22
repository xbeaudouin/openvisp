<?php 
print "<br/><div id=\"submenu\">&middot;<a target=\"_top\" href=\"admin_apc-add.php\">".$PALANG['pDataCenter_adminapc_apc_add']."</a>&middot;</div>";
if (sizeof ($list_apc) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pDataCenter_adminapc_apc_name'] . "</td>\n";
   print "      <td>" . $PALANG['pDataCenter_adminapc_apc_ip'] . "</td>\n";
   print "      <td>" . $PALANG['pDataCenter_adminapc_apc_modified'] . "</td>\n";
   print "      <td>" . $PALANG['pDataCenter_adminapc_apc_active'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($list_apc); $i++)
   {
      if ((is_array ($list_apc) and sizeof ($list_apc) > 0))
      {
        print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
      	print "<td><a href=\"admin_apc-edit.php?name=" . $list_apc[$i] . "\">" . $list_apc[$i] . "</a></td>";
      	print "<td>" . $apc_properties[$i]['ip'] . "</td>";
   	print "<td>" . $apc_properties[$i]['modified'] . "</td>";
        $active = ($apc_properties[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
   	print "<td><a href=\"admin_edit-active-apc.php?name=" . $list_apc[$i] . "\">" . $active . "</a></td>";
   	print "<td><a href=\"admin_apc-edit.php?name=" . $list_apc[$i] . "\">" . $PALANG['edit'] . "</a></td>";
   	print "<td><a href=\"admin_apc-delete.php?name=" . $list_apc[$i] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>";
   	print "</tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}
?>
