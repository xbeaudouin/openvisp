<?php

print load_js("../lib/yui/yahoo-dom-event/yahoo-dom-event.js");
print load_js("../lib/yui/connection/connection-min.js");
print load_js("../lib/yui/json/json-min.js");
print load_js("../lib/yui/element/element-min.js");
print load_js("../lib/yui/paginator/paginator-min.js");
//print load_js("../lib/yui/autocomplete/autocomplete-debug.js");
print load_js("../lib/yui/datasource/datasource-min.js");
print load_js("../lib/yui/datatable/datatable-min.js");

print load_css("../css/datatable.css");


if ( $domain_overquota == 1 ){
	print $PALANG['pAdminList_domain_overquota']."<br>";
	print "(".$total_used['domains']."/".$account_quota['domains'].")";
 }

if ( check_admin($SESSID_USERNAME) == 1 ){
?>

<form name="overview" method="post">
<select name="fUsername" onChange="this.form.submit()";>

<?php
for ($i = 0; $i < sizeof ($list_admins); $i++)
	{
		if ($fUsername == $list_admins[$i])
			{
				print "<option value=\"" . $list_admins[$i] . "\" selected>" . $list_admins[$i] . "</option>\n";
			}
		else
			{
				print "<option value=\"" . $list_admins[$i] . "\">" . $list_admins[$i] . "</option>\n";
			}
	}

?>

</select>
<input type="submit" name="go" value="<?php print $PALANG['pOverview_button']; ?>" />
</form>

<?php
}
?>

<br/>
<br/>

<div id="domain-nav"></div>
<div id="domain"></div>

<br/>
<br/>



<p />
<?php 

$ajax_domain->end();

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
   print "      <td>" . "Backup MX" . "</td>\n";

   print "      <td>" . $PALANG['pAdminList_domain_ftp'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_http'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_sql'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_status'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_modified'] . "</td>\n";
   print "      <td>" . $PALANG['pAdminList_domain_active'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($list_domains); $i++)
   {
      if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
      {


				$classoff="hilightoff";
				$classon="hilighton";
				if ( $domain_properties[$i]['paid'] == 0 ){
					$classon="lockhilighton";
					$classoff="lockhilightoff";
				}

				//         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         print "   <tr class=\"$classoff\" onMouseOver=\"className='$classon';\" onMouseOut=\"className='$classoff';\">\n";

         if($domain_properties[$i]['backupmx'] == 1)
         {
           print "<td>" . $list_domains[$i] . "</td>";
         }
         else
         {
           print "<td>" . $list_domains[$i] . "</a></td>";
					 //print "<td><a href=\"list-virtual.php?domain=" . $list_domains[$i] . "\">" . $list_domains[$i] . "</a></td>";
         }
         print "<td>" . $domain_properties[$i]['description'] . "</td>";
         if($domain_properties[$i]['backupmx'] == 1) {
           print "<td></td>";
           print "<td></td>";
           print "<td><a href=\"edit-active-domain-backupmx.php?domain=" . $list_domains[$i] ."\">" . $PALANG['YES'] . "</a></td>";
         } else {
           print "<td>" . $domain_properties[$i]['alias_count'] . " / " . $domain_properties[$i]['aliases'] . "</td>";
           print "<td>" . $domain_properties[$i]['mailbox_count'] . " / " . $domain_properties[$i]['mailboxes'] . "</td>";
           print "<td>" . $PALANG['NO'] . "</td>";
         }

         print "<td>" . $domain_properties[$i]['ftp_count'] . " / " .  $domain_properties[$i]['ftp_account']  . "</td>";
         print "<td>" . $domain_properties[$i]['web_count'] . "</td>";
         print "<td>" . $domain_properties[$i]['dbused_count'] . " / ". $domain_properties[$i]['db_count'] . "</td>";

         display_domain_status($domain_properties[$i]['status']);
         print "<td>" . $domain_properties[$i]['modified'] . "</td>";
         $active = ($domain_properties[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         if ( $domain_properties[$i]['active'] == 1 ){
          $active = $PALANG['YES'];
          $alink="desactivate";
         }
         else{
          $alink="activate";
          $active = $PALANG['NO'];
         }
         print "<td><a href=\"edit-active-domain.php?domain=" . $list_domains[$i] . "&action=$alink\">" . $active . "</a></td>";
         print "<td><a href=\"edit-domain.php?domain=" . $list_domains[$i] . "\">" . $PALANG['edit'] . "</a></td>";
         print "<td><a href=\"../domain/delete.php?domain=" . $list_domains[$i] . "\" onclick=\"return confirm ('" . $PALANG['confirm_domain'] . "')\">" . $PALANG['del'] . "</a></td>";
         //print "<td><a href=\"../mail/overview.php?domain=" . $list_domains[$i] . "\">" . $PALANG['manage'] . "</a></td>";
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
      print "<td>" . $list_domains_alias[$i]['dalias'] . "</td>";
      print "<td>" . $domain_alias_properties[$i]['domain'] . "</td>";
      print "<td>" . $domain_alias_properties[$i]['modified'] . "</td>";
      $active = ($domain_alias_properties[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
      print "<td><a href=\"edit-active-domain-alias.php?domain=" . $list_domains_alias[$i]['dalias'] . "\">" . $active . "</a></td>";
      print "<td><a href=\"../domain/delete.php?domain_alias=" . $list_domains_alias[$i]['dalias'] . "\" onclick=\"return confirm ('" . $PALANG['confirm_domain'] . "')\">" . $PALANG['del'] . "</a></td>";
      print "</tr>\n";
    }
  }


  print "</table>\n";
  print "<p />\n";

}

?>
