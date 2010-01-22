<form name="overview" method="post">
<select name="fDomain" onChange="this.form.submit()";>
<?php
for ($i = 0; $i < sizeof ($list_domains); $i++)
{
   if ($fDomain == $list_domains[$i])
   {
      print "<option value=\"$list_domains[$i]\" selected>$list_domains[$i]</option>\n";
   }
   else
   {
      print "<option value=\"$list_domains[$i]\">$list_domains[$i]</option>\n";
   }
}
?>
</select>
<input type="hidden" name="limit" value="0">
<input type="submit" name="go" value="<?php print $PALANG['pAdminList_virtual_button']; ?>" />
</form>
<p />
<?php 

print "<b>". $PALANG['pAdminList_virtual_ftp_welcome'] . $fDomain . "</b><br />\n";
print $PALANG['pAdminList_virtual_ftp_count'] . ": " . $limit['ftp_count'] . " / " . $limit['ftp_account'] . " &nbsp; ";

if ($tDisplay_back_show == 1) print "<a href=\"list-ftp.php?domain=$fDomain&limit=$tDisplay_back\"><img src=\"../images/back.gif\"></a>\n";
if ($tDisplay_up_show == 1) print "<a href=\"list-ftp.php?domain=$fDomain&limit=0\"><img  src=\"../images/up.gif\"></a>\n";
if ($tDisplay_next_show == 1) print "<a href=\"list-ftp.php?domain=$fDomain&limit=$tDisplay_next\"><img src=\"../images/next.gif\"></a>\n";

if (sizeof ($tFtpaccount) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_login'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_dir'] . "</td>\n";
	 if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $PALANG['pAdminList_virtual_ftp_uprt'] . "</td>\n";
   if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $PALANG['pAdminList_virtual_ftp_dlrt'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_quotadk'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_quotamfs'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_bwul'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_virtual_ftp_bwdl'] . "</td>\n";
	 print "      <td>" . $PALANG['pAdminList_virtual_ftp_active'] . "</td>\n";
	 print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($tFtpaccount); $i++)
   {
      if ((is_array ($tFtpaccount) and sizeof ($tFtpaccount) > 0))
      {
         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         print "      <td>" . $tFtpaccount[$i]['login'] . "</td>\n";
         print "      <td>" . $tFtpaccount[$i]['dir'] . "</td>\n";
         if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $tFtpaccount[$i]['ratioul'] . "</td>\n";
         if ( $CONF['ftp_ratio'] == 'YES' ) print "      <td>" . $tFtpaccount[$i]['ratiodl'] . "</td>\n";
         print "      <td>" . $tFtpaccount[$i]['quotafs'] . "</td>\n";
         print "      <td>" . $tFtpaccount[$i]['quotasz'] . "</td>\n";
				 print "      <td>" . $tFtpaccount[$i]['bandwidthul'] . "</td>\n";
				 print "      <td>" . $tFtpaccount[$i]['bandwidthdl'] . "</td>\n";
				 print "      <td>" . $tFtpaccount[$i]['active'] . "</td>\n";

//          print "      <td>" . $tFtpaccount[$i]['modified'] . "</td>\n";
// 				 $policy_id = ($tFtpaccount[$i]['policy_id'] == 0) ? $PALANG['NO'] : $PALANG['YES'];
// 				 print " <td><a href=\"edit-security.php?address=" . urlencode ($tFtpaccount[$i]['address']) . "&domain=$fDomain" . "\">" . $policy_id . "</a></td>\n";
				 print "      <td><a href=\"edit-ftp.php?ftpaccount=" . urlencode ($tFtpaccount[$i]['login']) . "&domain=$fDomain" . "\">" . $PALANG['edit'] . "</a></td>\n";
				 print "      <td><a href=\"delete.php?table=alias" . "&delete=" . urlencode ($tFtpaccount[$i]['address']) . "&domain=$fDomain" . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
         print "   </tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}

?>
