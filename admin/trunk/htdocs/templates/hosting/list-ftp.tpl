<?php 

if ($list_domains == "")
{
  print "<br><div id=\"information\">";
  print $PALANG['pWhostList_admin_no_website'];
  print "</div><br><br>\n";
}

for ($i = 0; $i < sizeof ($list_domains); $i++)
{
  if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
  { 
      print "<table>\n";
      print "   <tr class=\"header2\">\n";
			$colspan = "13";
			if ( $CONF['showpassword'] == 'YES' ){
				$colspan = "14";
			}
			$basedir = $CONF['storage'] . "/" . get_dir_hash($domain_properties[$i]['domain']) . "/" . $domain_properties[$i]['domain'];
      print "      <td colspan=\"$colspan\">" . strtoupper($domain_properties[$i]['domain']) . " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $basedir </td></tr>\n";
      if (sizeof ($domain_ftpaccount[$i]['counter']) > 0)
      {
         print "   <tr class=\"header\">\n";
         print "      <td>" . $PALANG['pAdminList_virtual_ftp_login'] . "</td>\n";
				 if ( $CONF['showpassword'] == 'YES' ){
					 print "      <td>" . $PALANG['pAdminList_virtual_ftp_password'] . "</td>\n";					 
				 }

         print "      <td>" . $PALANG['pAdminList_virtual_ftp_dir'] . "</td>\n";
      	 if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $PALANG['pAdminList_virtual_ftp_uprt'] . "</td>\n";
         if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $PALANG['pAdminList_virtual_ftp_dlrt'] . "</td>\n";
         print "      <td>" . $PALANG['pAdminList_virtual_ftp_quotadk'] . "</td>\n";
         print "      <td>" . $PALANG['pAdminList_virtual_ftp_quotamfs'] . "</td>\n";
         print "      <td>" . $PALANG['pAdminList_virtual_ftp_bwul'] . "</td>\n";
         print "      <td>" . $PALANG['pAdminList_virtual_ftp_bwdl'] . "</td>\n";
      	 print "      <td>" . $PALANG['pAdminList_virtual_ftp_used'] . "</td>\n";
      	 print "      <td>" . $PALANG['pAdminList_virtual_ftp_transfert'] . " (upload/download)</td>\n";
      	 print "      <td>" . $PALANG['pAdminList_virtual_ftp_active'] . "</td>\n";
      	 print "      <td colspan=\"2\">&nbsp;</td>\n";
         print "   </tr>\n";
      
         if ($domain_ftpaccount[$i]['counter'] > 0)
         {
           for ($j = 0; $j < $domain_ftpaccount[$i]['counter']; $j++)
           {

						 $classoff="hilightoff";
						 $classon="hilighton";
						 if ( $domain_ftpaccount[$i][$j]['paid'] == 0 ){
							 $classon="lockhilighton";
							 $classoff="lockhilightoff";
						 }
						 
						 print "   <tr class=\"$classoff\" onMouseOver=\"className='$classon';\" onMouseOut=\"className='$classoff';\">\n";
#    print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
                 print "      <td>" . $domain_ftpaccount[$i][$j]['login'] . "</td>\n";
								 if ( $CONF['showpassword'] == 'YES' ){
									 print "      <td>" . $domain_ftpaccount[$i][$j]['password'] . "</td>\n";
								 }
								 $subdir = explode($domain_properties[$i]['domain']."/", $domain_ftpaccount[$i][$j]['dir']);
                 print "      <td>" . $subdir[1] . "</td>\n";
                 if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $domain_ftpaccount[$i][$j]['ratioul'] . "</td>\n";
                 if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $domain_ftpaccount[$i][$j]['ratiodl'] . "</td>\n";
                 print "      <td>" . $domain_ftpaccount[$i][$j]['quotafs'] . "</td>\n";
                 print "      <td>" . $domain_ftpaccount[$i][$j]['quotasz'] . "</td>\n";
        				 print "      <td>" . $domain_ftpaccount[$i][$j]['bandwidthul'] . "</td>\n";
        				 print "      <td>" . $domain_ftpaccount[$i][$j]['bandwidthdl'] . "</td>\n";
        				 print "      <td>" . number_format($domain_ftpaccount[$i][$j]['disk_used'],0, ',', ' ') . "</td>\n";
								 $ftp_transfert_ul = convert_number_size($domain_ftpaccount[$i][$j]['ftp_transfert_ul']);
								 $ftp_transfert_dl = convert_number_size($domain_ftpaccount[$i][$j]['ftp_transfert_dl']);
        				 print "      <td> <a href=\"histo-ftp.php?account=".$domain_ftpaccount[$i][$j]['login']."\">" . number_format($ftp_transfert_ul['num'],0, ',', ' ') . " " . $ftp_transfert_ul['unit']. " / " . number_format($ftp_transfert_dl['num'],0, ',', ' ') . " " . $ftp_transfert_dl['unit'] . "</a></td>\n";
        				 $active = ($domain_ftpaccount[$i][$j]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
        				 print "      <td><a href=\"edit-active-ftp.php?account=" . urlencode($domain_ftpaccount[$i][$j]['login']) . "\">". $active ."</a></td>\n";       
        				 print "      <td><a href=\"edit-ftp.php?ftpaccount=" . urlencode ($domain_ftpaccount[$i][$j]['login']) . "&domain=" . $domain_properties[$i]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
        				 print "      <td><a href=\"delete.php?account=" . urlencode ($domain_ftpaccount[$i][$j]['login']) . "&domain=" . $domain_properties[$i]['domain'] . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
                 print "   </tr>\n";
          }
         }
      }
      else
      {
        print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
        print "<td colspan=\"9\"><center>- " . $PALANG['pWhostList_admin_ftp_empty'] . " -</center></td>\n";
        print "</tr>\n";
      }
      print "</table>\n";
      print "<p />\n";
  }
}

?>
