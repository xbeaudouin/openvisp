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
         print "      <td width=\"15%\">" . $PALANG['pWhostList_Admin_vhost'] . "</td>\n";
         print "      <td>" . $PALANG['pWhostList_Admin_DocumentRoot'] . "</td>\n";
         print "      <td>" . $PALANG['pWhostList_Admin_Alias'] . "</td>\n";
         print "      <td>" . $PALANG['pWhostList_Admin_CustomLog'] . "</td>\n";
         print "      <td>" . $PALANG['pWhostList_Admin_active'] . "</td>\n";
         print "      <td colspan=\"2\">&nbsp;</td></tr>\n";

         if ( is_array($domain_websites[$i]) and sizeof($domain_websites[$i]) > 0)
         {
	    for($j = 0; $j < sizeof($domain_websites[$i]); $j++)
            {

							$classoff="hilightoff";
							$classon="hilighton";
							if ( $domain_websites[$i][$j]['paid'] == 0 ){
								$classon="lockhilighton";
								$classoff="lockhilightoff";
							}

#               print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
							 print "   <tr class=\"$classoff\" onMouseOver=\"className='$classon';\" onMouseOut=\"className='$classoff';\">\n";
               if ($domain_websites[$i][$j]['vhost'] != '')
               {
                  print "<td><a target=\"_blank\" href=\"http://" . $domain_websites[$i][$j]['vhost'] . "." .$domain_properties[$i]['domain']. "\">" . $domain_websites[$i][$j]['vhost'] . "." . $domain_properties[$i]['domain'] . "</a></td>\n";
		  $DocumentRoot = substr($domain_websites[$i][$j]['DocumentRoot'],-(strlen($domain_websites[$i][$j]['vhost'])+1));
               }
               else
               {
                  print "<td><a target=\"_top\" href=\"http://" . $domain_properties[$i]['domain']. "\">" . $domain_properties[$i]['domain'] . "</a></td>\n";
		  $DocumentRoot = substr($domain_websites[$i][$j]['DocumentRoot'],-4);
               }
               print "<td>" . $DocumentRoot . "</td>\n";
		 if ( check_http_admin($SESSID_USERNAME) ){
			 print "<td>" . $domain_websites[$i][$j]['SetEnv'] . "</td>\n";
			 print "<td>" . $domain_websites[$i][$j]['CustomLog'] . "</td>\n";

			 $active = ($domain_websites[$i][$j]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
			 print "<td><a href=\"edit-active-whost.php?vhost=" . $domain_websites[$i][$j]['vhost'] . "&domain=" . $domain_properties[$i]['domain'] . "\">". $active ."</a></td>\n";
			 print "<td><a href=\"edit-web.php?vhost=" . $domain_websites[$i][$j]['vhost'] . "&domain=" . $domain_properties[$i]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
			 print "<td><a href=\"delete.php?vhost=" . $domain_websites[$i][$j]['vhost'] . "&domain=" . $domain_properties[$i]['domain'] . "\" onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
		 }
		 else{
			 print '<td colspan="5"></td>';
		 }
               print "</tr>\n";
	       $domain_vhosts = get_website_alias_list ($domain_properties[$i]['domain'],$domain_websites[$i][$j]['vhost']);
	       for ($k = 0; $k < sizeof($domain_vhosts); $k++)
	       {
		if ( is_array($domain_vhosts) && sizeof($domain_vhosts) > 0 )
		{
		  print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
		  print "<td colspan=\"2\"></td>\n";
		  print "<td><a target=\"_blank\" href=\"http://" . $domain_vhosts[$k]['vhost'] . "." . $domain_vhosts[$k]['domain'] . "\">" . $domain_vhosts[$k]['vhost']. "." . $domain_vhosts[$k]['domain'] . "</a></td>";
		  print "<td>" . $domain_vhosts[$k]['CustomLog'] . "</td>\n";
		  $active = ($domain_vhosts[$k]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
	          print "<td><a href=\"edit-active-whost.php?vhost=" . $domain_vhosts[$k]['vhost'] . "&domain=" . $domain_vhosts[$k]['domain'] . "\">". $active ."</a></td>\n";
		  print "<td><a href=\"edit-web.php?vhost=" . $domain_vhosts[$k]['vhost'] . "&domain=" . $domain_vhosts[$k]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
		  print "<td><a href=\"delete.php?vhost=" . $domain_vhosts[$k]['vhost'] . "&domain=" . $domain_vhosts[$k]['domain'] . "\" onclick=\"return confirm('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
		  print "</tr>\n";
		}
	       }
	      }
         }
         else
         {
            print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
            print "<td colspan=\"7\"><center>- " . $PALANG['pWhostList_admin_empty'] . " -</center></td>\n";
            print "</tr>\n";
         } 

         print "</table>\n";
         print "<br />\n";
      }
}
?>
