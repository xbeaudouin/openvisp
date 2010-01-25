<p />
<?php 
if (sizeof ($list_domains) > 0)
{
   print "<table>";
   print "<tr class=\"header\">";
   print "<td></td>";
   print "<td>". $PALANG['pAdminList_domain_legend'] . "</td></tr>";
   print "<tr><td><b>". $PALANG['pAdminList_domain_antivirus'] . "</b></td><td>". $PALANG['pAdminList_domain_antivirus_legend'] . "</td></tr>";
   print "<tr><td><b>". $PALANG['pAdminList_domain_greylisting'] . "</b></td><td>". $PALANG['pAdminList_domain_greylisting_legend'] . "</td></tr>";
   print "<tr><td><b>". $PALANG['pAdminList_domain_verify_mail'] . "</b></td><td>". $PALANG['pAdminList_domain_verify_mail_legend'] . "</td></tr>";
   print "<tr><td><b>". $PALANG['pAdminList_domain_verify_domain'] . "</b></td><td>". $PALANG['pAdminList_domain_verify_domain_legend'] . "</td></tr>";
   print "<tr><td><b>". $PALANG['pAdminList_domain_verify_spf'] . "</b></td><td>". $PALANG['pAdminList_domain_verify_spf_legend'] . "</td></tr>";
   print "</table>";
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pAdminList_domain_domain'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_description'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_aliases'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_mailboxes'] . "</td>\n";
   if ($CONF['quota'] == 'YES') print "      <td>" . $PALANG['pAdminList_domain_maxquota'] . "</td>\n";
   print "      <td>" . "Backup MX" . "</td>\n";
   print "      <td colspan=\"3\" align=\"center\">" . $PALANG['pAdminList_domain_spamass'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_antivirus'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_verify_mail'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_verify_domain'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_greylisting'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_verify_spf'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_modified'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_active'] . "</td>\n";
   print "      <td colspan=\"3\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($list_domains); $i++)
   {
      if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
      {
         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         if($domain_properties[$i]['backupmx'] == 1)
         {
           print "<td>" . $list_domains[$i] . "</td>";
         }
         else
         {
           print "<td><a href=\"list-virtual.php?domain=" . $list_domains[$i] . "\">" . $list_domains[$i] . "</a></td>";
         }
         print "<td>" . $domain_properties[$i]['description'] . "</td>";
         if($domain_properties[$i]['backupmx'] == 1) {
           print "<td></td>";
           print "<td></td>";
           if ($CONF['quota'] == 'YES') print "<td></td>";
           print "<td><a href=\"edit-active-domain-backupmx.php?domain=" . $list_domains[$i] ."\">" . $PALANG['YES'] . "</a></td>";
         } else {
           print "<td>" . $domain_properties[$i]['alias_count'] . " / " . $domain_properties[$i]['aliases'] . "</td>";
           print "<td>" . $domain_properties[$i]['mailbox_count'] . " / " . $domain_properties[$i]['mailboxes'] . "</td>";
           if ($CONF['quota'] == 'YES') print "<td>" . $domain_properties[$i]['maxquota'] . "</td>";
           print "<td>" . $PALANG['NO'] . "</td>";
         }
         if ($domain_policy[$i]['bypass_spam_checks'] == 'N') {
           print "<td><a href=\"edit-active-domain-spamass.php?domain=" . $list_domains[$i] . "\">" . $PALANG['YES'] . "</a></td>";
           print "<td>" . $domain_policy[$i]['spam_subject_tag'] . "</td>";
           print "<td>" . $domain_policy[$i]['spam_tag_level'] . "</td>";
         } else {
           print "<td><a href=\"edit-active-domain-spamass.php?domain=" . $list_domains[$i] . "\">" . $PALANG['NO'] . "</a></td>";
           print "<td></td><td></td>";
         }
         
	 if ($domain_policy[$i]['bypass_virus_checks'] == 'N') {
           print "<td><a href=\"edit-active-domain-antivir.php?domain=" . $list_domains[$i] . "\">" . $PALANG['YES'] . "</a></td>";
	 } else {
	   print "<td><a href=\"edit-active-domain-antivir.php?domain=" . $list_domains[$i] . "\">" . $PALANG['NO'] . "</a></td>";
	 }
         $vrfysender = ($domain_properties[$i]['vrfysender'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         $vrfydomain = ($domain_properties[$i]['vrfydomain'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "<td>" . $vrfysender . "</td><td>" . $vrfydomain . "</td>";
         $greylisting = ($domain_properties[$i]['greylist'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "<td>$greylisting</td>";
         $spf = ($domain_properties[$i]['spf'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "<td>$spf</td>";
         print "<td>" . $domain_properties[$i]['modified'] . "</td>";
         $active = ($domain_properties[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "<td><a href=\"edit-active-domain.php?domain=" . $list_domains[$i] . "\">" . $active . "</a></td>";
         print "<td><a href=\"edit-domain.php?domain=" . $list_domains[$i] . "\">" . $PALANG['edit'] . "</a></td>";
         print "<td><a href=\"delete.php?table=domain&where=domain&delete=" . $list_domains[$i] . "\" onclick=\"return confirm ('" . $PALANG['confirm_domain'] . "')\">" . $PALANG['del'] . "</a></td>";
         //print "<td><a href=\"../overview.php?domain=" . $list_domains[$i] . "\">" . $PALANG['manage'] . "</a></td>";
         print "</tr>\n";
		}
   }

   print "</table>\n";
   print "<p />\n";
}

if (sizeof($list_domains_alias) > 0)
{
  print "<table>\n";
  print "  <tr class=\"header\">\n";
  print "    <td>" . $PALANG['pAdminList_domain_alias_domain'] . "</td>\n";
  print "    <td>" . $PALANG['pAdminList_domain_alias_aliasto'] . "</td>\n";
  print "    <td>" . $PALANG['pAdminList_domain_alias_modified'] . "</td>\n";
  print "    <td>" . $PALANG['pAdminList_domain_alias_active'] . "</td>\n";
  print "    <td>&nbsp;</td>\n";
  print "  </tr>\n";

  for ($i = 0; $i < sizeof ($list_domains_alias); $i++)
  {
    if ((is_array($list_domains_alias) and sizeof ($list_domains_alias) > 0))
    {
      print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
      print "<td>" . $list_domains_alias[$i] . "</td>";
      print "<td>" . $domain_alias_properties[$i]['domain'] . "</td>";
      print "<td>" . $domain_alias_properties[$i]['modified'] . "</td>";
      $active = ($domain_alias_properties[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
      print "<td><a href=\"edit-active-domain-alias.php?domain=" . $list_domains_alias[$i] . "\">" . $active . "</a></td>";
      print "<td><a href=\"delete.php?table=domain_alias&where=dalias&delete=" . $list_domains_alias[$i] . "\" onclick=\"return confirm ('" . $PALANG['confirm_domain'] . "')\">" . $PALANG['del'] . "</a></td>";
      print "</tr>\n";
    }
  }


  print "</table>\n";
  print "<p />\n";

}

?>
