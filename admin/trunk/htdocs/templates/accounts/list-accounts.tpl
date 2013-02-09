<?php 
if (sizeof ($admin_accounts->admin_account_list) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pAccountList_account_username'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_sadmin'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_datacenter'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_datacenter_sadmin'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_mail'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_ftp'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_http'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_domain'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_mysql'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_postgresql'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_modified'] . "</td>\n";
   print "      <td>" . $PALANG['pAccountList_account_enabled'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($admin_accounts->admin_account_list); $i++)
   {
      if ((is_array ($admin_accounts->admin_account_list) and sizeof ($admin_accounts->admin_account_list) > 0))
      {
        print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
      	print "<td><a href=\"details.php?username=" . $admin_accounts->admin_account_list[$i]['username'] . "\">" . $admin_accounts->admin_account_list[$i]['username'] . "</a></td>";
        $sadmin = ($account_properties[$i]['manage'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $sadmin . "</td>\n";
        $datacenter = ($account_properties[$i]['datacenter'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $datacenter . "</td>";
        $datacenter_sadmin = ($account_properties[$i]['datacenter_manage'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
      	print "<td>". $datacenter_sadmin. "</td>";
        $mailadmin = ($account_properties[$i]['mail'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $mailadmin . "</td>\n";
        $ftp = ($account_properties[$i]['ftp'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $ftp . "</td>";
        $http = ($account_properties[$i]['http'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $http . "</td>";
        $domain = ($account_properties[$i]['domain'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $domain . "</td>";
        $mysql = ($account_properties[$i]['mysql'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $mysql . "</td>";
        $postgresql = ($account_properties[$i]['postgresql'] ==1) ? $PALANG['YES'] : $PALANG['NO'];
        print "<td>" . $postgresql . "</td>";

//        print "<td></td>";
       	print "<td>" . $account_properties[$i]['modified'] . "</td>";
        $active = ($account_properties[$i]['enabled'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
       	print "<td><a href=\"edit-active-account.php?username=" . $admin_accounts->admin_account_list[$i]['username'] . "\">" . $active . "</a></td>";
       	print "<td><a href=\"edit-account.php?username=" . $admin_accounts->admin_account_list[$i]['username'] . "\">" . $PALANG['edit'] . "</a></td>";
       	print "<td><a href=\"delete-account.php?username=" . $admin_accounts->admin_account_list[$i]['username'] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>";
       	print "</tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}
?>
