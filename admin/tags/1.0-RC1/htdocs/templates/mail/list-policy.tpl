<?php 

$sizepolicy = sizeof ($tPolicy);   
if ($sizepolicy > 0) {
  print "<table>";
  print "<tr class=\"header\">";
  print "<td></td>";
  print "<td>". $PALANG['pAdminList_domain_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_bypass_virus'] . "</b></td><td>". $PALANG['pAdminList_policy_bypass_virus_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_bypass_spam'] . "</b></td><td>". $PALANG['pAdminList_policy_bypass_spam_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_bypass_banned_check'] . "</b></td><td>". $PALANG['pAdminList_policy_bypass_banned_check_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_bypass_header'] . "</b></td><td>". $PALANG['pAdminList_policy_bypass_header_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_spam_mod_subj'] . "</b></td><td>". $PALANG['pAdminList_policy_spam_mod_subj_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_spam_subj_tag'] . "</b></td><td>". $PALANG['pAdminList_policy_spam_subj_tag_legend'] . "</td></tr>"; 
  print "<tr><td><b>". $PALANG['pAdminList_policy_warnvirusrecip'] . "</b></td><td>". $PALANG['pAdminList_policy_warnvirusrecip_legend'] . "</td></tr>"; 
  print "<tr><td><b>". $PALANG['pAdminList_policy_spam_tag_level'] . "</b></td><td>". $PALANG['pAdminList_policy_spam_tag_level_legend'] . "</td></tr>"; 
  print "<tr><td><b>". $PALANG['pAdminList_policy_spam_tag2_level'] . "</b></td><td>". $PALANG['pAdminList_policy_spam_tag2_level_legend'] . "</td></tr>"; 
  print "<tr><td><b>". $PALANG['pAdminList_policy_spam_kill_level'] . "</b></td><td>". $PALANG['pAdminList_policy_spam_kill_level_legend'] . "</td></tr>"; 
  print "<tr><td><b>". $PALANG['pAdminList_policy_warnvirusrecip'] . "</b></td><td>". $PALANG['pAdminList_policy_warnvirusrecip_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_warnbannedrecip'] . "</b></td><td>". $PALANG['pAdminList_policy_warnbannedrecip_legend'] . "</td></tr>";
  print "<tr><td><b>". $PALANG['pAdminList_policy_warnbadhrecip'] . "</b></td><td>". $PALANG['pAdminList_policy_warnbadhrecip_legend'] . "</td></tr>";
  print "</table>";

  print "<br>";

  print "<table>\n";
  print "   <tr class=\"header\">\n";
#  print "      <td>" . $PALANG['pAdminList_policy_id'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_name'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_bypass_virus'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_bypass_spam'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_bypass_banned_check'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_bypass_header'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_spam_mod_subj'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_spam_subj_tag'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_warnvirusrecip'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_spam_tag_level'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_spam_tag2_level'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_spam_kill_level'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_warnvirusrecip'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_warnbannedrecip'] . "</td>\n";
  print "      <td>" . $PALANG['pAdminList_policy_warnbadhrecip'] . "</td>\n";
  print "      <td>&nbsp;</td>\n";
  print "   </tr>\n";
}
else
{ 
  echo "Hmm: ". $sizepolicy;
}

for ($i = 0 ; $i < sizeof($tPolicy) ; $i++) {
  if ((is_array ($tPolicy) and sizeof ($tPolicy) > 0)) {
    print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
#    print "<td>" . $tPolicy[$i]['id'] . "</td>";
    print "<td>" . $tPolicy[$i]['domain'] . "</td>";
    print "<td>" . $tPolicy[$i]['bypass_virus_checks'] . "</td>";
    print "<td>" . $tPolicy[$i]['bypass_spam_checks'] . "</td>";
    print "<td>" . $tPolicy[$i]['bypass_banned_checks'] . "</td>";
    print "<td>" . $tPolicy[$i]['bypass_header_checks'] . "</td>";
    print "<td>" . $tPolicy[$i]['spam_modifies_subj'] . "</td>";
    print "<td>" . $tPolicy[$i]['spam_subject_tag'] . "</td>";
    print "<td>" . $tPolicy[$i]['warnvirusrecip'] . "</td>";
    print "<td>" . $tPolicy[$i]['spam_tag_level'] . "</td>";
    print "<td>" . $tPolicy[$i]['spam_tag2_level'] . "</td>";
    print "<td>" . $tPolicy[$i]['spam_kill_level'] . "</td>";
    print "<td>" . $tPolicy[$i]['warnvirusrecip'] . "</td>";
    print "<td>" . $tPolicy[$i]['warnbannedrecip'] . "</td>";
    print "<td>" . $tPolicy[$i]['warnbadhrecip'] . "</td>";
    print "<td><a href=\"edit-active-domain-policy.php?domain=" . $tPolicy[$i]['domain'] . "\">" . $PALANG['edit'] . "</a></td>\n";
    print "</tr>\n";
  }
}

print "</table>\n";
print "<p />\n";

?>
