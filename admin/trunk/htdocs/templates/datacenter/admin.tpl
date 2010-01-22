<?php 
if (sizeof ($list_admins) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pDataCenter_adminuser_username'] . "</td>\n";
   print "      <td>" . $PALANG['pDataCenter_adminuser_apc_ports'] . "</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($list_admins); $i++)
   {
      if ((is_array ($list_admins) and sizeof ($list_admins) > 0))
      {
        print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
      	print "<td>" . $list_admins[$i] . "</td>";
        if (!($admin_properties[$i]['full'])) {
          print "<td><a href=\"admin_user-apc.php?name=" . $list_admins[$i] . "\">" . $admin_properties[$i]['apc_port_count'] . "</a></td>";
        } else {
          print "<td>" . $admin_properties[$i]['apc_port_count'] . "</td>";
        }
   	print "</tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}
?>
