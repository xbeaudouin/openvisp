<form name="overview" method="post">
<?php
  print $PALANG['pOverview_welcome_text'];
?>
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
<input type="submit" name="go" value="<?php print $PALANG['pOverview_button']; ?>" />
</form>
<p />
<?php
print "<table>\n";
print "   <tr class=\"header\">\n";
print "      <td>" . $PALANG['pOverview_get_domain'] . "</td>\n";
print "      <td>" . $PALANG['pOverview_get_aliases'] . "</td>\n";
print "      <td>" . $PALANG['pOverview_get_mailboxes'] . "</td>\n";
if ($CONF['quota'] == 'YES') print "      <td>" . $PALANG['pOverview_get_quota'] . "</td>\n";
print "      <td>" . $PALANG['pOverview_get_total_mailbox_size'] . "</td>\n";
print "      <td>" . $PALANG['pOverview_get_security'] . "</td>\n";
print "   </tr>\n";

$total_alias_count = 0;
$total_mailbox_count = 0;
$total_mbdisk_count = 0;
$total_maxalias_count = 0;
$total_maxmailbox_count = 0;

for ($i = 0; $i < sizeof ($list_domains); $i++)
{
   if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
   {
      $limit = get_domain_properties ($list_domains[$i]);
			$domain_policy = get_domain_policy ($list_domains[$i]);

			$total_alias_count += $limit['alias_count'];
			$total_mailbox_count += $limit['mailbox_count'];
			$total_maxalias_count += $limit['aliases'];
			$total_maxmailbox_count += $limit['mailboxes'];

      print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">";
      print "<td><a href=\"overview.php?domain=" . $list_domains[$i] . "\">" . $list_domains[$i] . "</a></td>";
      print "<td>" . $limit['alias_count'] . " / ";
      switch ($limit['aliases']) {
        case "0" : print $PALANG['pOverview_limit_none']; break;
        case "-1": print "&infin;"; break;
        default  : print $limit['aliases']; break;
      }
      print "</td><td>" . $limit['mailbox_count'] . " / "; //. $limit['mailboxes'] . "</td>";
      switch ($limit['mailboxes']) {
        case "0" : print $PALANG['pOverview_limit_none']; break;
        case "-1": print "&infin;"; break;
        default  : print $limit['mailboxes']; break;
      }
      print "</td>";
      if ($CONF['quota'] == 'YES') {
      print " <td>";
      switch($limit['maxquota']) {
        case "-1" : print "&infin;"; break;
        default   : print $limit['maxquota']; break;
      }
      print "</td>";
      }
			$total_domain_mbox = total_quota_mailbox_domain($list_domains[$i]);
      print " <td> ". number_format($total_domain_mbox,0, ',', ' ')."</td>";
			$total_mbdisk_count += $total_domain_mbox;
			print "<td><a href=\"edit-active-domain-policy.php?domain=" . $list_domains[$i] . "\">" . $PALANG['pOverview_get_security_edit'] . "</a></td>";
      print "</tr>";
   }
}

print "   <tr>\n";
print "      <td>Total</td>\n";
print "      <td>$total_alias_count/".$account_quota['emails_alias']."</td>\n";
print "      <td>$total_mailbox_count/".$account_quota['emails']."</td>\n";
// print "      <td>$total_alias_count/$total_maxalias_count</td>\n";
// print "      <td>$total_mailbox_count/$total_maxmailbox_count</td>\n";
if ($CONF['quota'] == 'YES') print "      <td></td>\n";
print "      <td>".number_format($total_mbdisk_count,0, ',', ' ')."</td>\n";
print "      <td></td>\n";
print "   </tr>\n";


print "</table>\n";
print "<p />\n";
?>
