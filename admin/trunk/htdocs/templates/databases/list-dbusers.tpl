<?php

if ($list_domains == "")
{
  print "<br><div id=\"information\">";
  print $PALANG['pWhostList_admin_no_domain'];
  print "</div><br><br>\n";
}


for ($i = 0; $i < sizeof ($list_domains); $i++)
{
  if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
      { 
         print "<table>\n";
         print "   <tr class=\"header2\">\n";
         print "      <td colspan=\"7\">" . strtoupper($domain_properties[$i]['domain']) . "</td>\n";
         //print "      <td colspan=\"6\">" . $domain_properties[$i]['description'] . "</td>\n";
         print "   </tr><tr class=\"header\">\n";
         print "      <td width=\"15%\">" . $PALANG['pDBList_dbusername'] . "</td>\n";
         print "      <td>" . $PALANG['pDBList_db_access'] . "</td>\n";
         print "      <td>" . $PALANG['pDBList_db_type'] . "</td>\n";
				 print "      <td>" . $PALANG['pDBList_db_created'] . "</td>\n";
				 print "      <td>" . $PALANG['pDBList_db_host'] . "</td>\n";
         print "      <td colspan=\"1\">&nbsp;</td>\n";
         print "      <td witdth=\"10%\">" . $PALANG['pDBList_db_active'] . "</td></tr>\n";


         if ( is_array($domain_dbusers[$i]) and sizeof($domain_dbusers[$i]) > 0)
					 {
						 for($j = 0; $j < sizeof($domain_dbusers[$i]); $j++)
							 {
								 print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";

                  print "<td>" . $domain_dbusers[$i][$j]['username'] . "</td>\n";
                  print "<td>" . list_dbuser_access($domain_dbusers[$i][$j]['username'],$domain_dbusers[$i][$j]['type_id'],$domain_dbusers[$i][$j]['server_id']) . "</td>\n";
                  print "<td>" . $domain_dbusers[$i][$j]['type'] . "</td>\n";
                  print "<td>" . $domain_dbusers[$i][$j]['created'] . "</td>\n";
                  print "<td>" . $domain_dbusers[$i][$j]['server_name'] . "</td>\n";
									print "<td colspan=\"1\">&nbsp;</td>\n";
                  print "<td>&nbsp;</td>\n";

									print "</tr>\n";									
							 }

					 }

         print "</table>\n";
         print "<br />\n";
      }
}
?>
