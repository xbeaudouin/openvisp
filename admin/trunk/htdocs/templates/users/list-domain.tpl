<?php

/*
print load_js("../lib/yui/yahoo-dom-event/yahoo-dom-event.js");
print load_js("../lib/yui/connection/connection-min.js");
print load_js("../lib/yui/json/json-min.js");
print load_js("../lib/yui/element/element-min.js");
print load_js("../lib/yui/paginator/paginator-min.js");
//print load_js("../lib/yui/autocomplete/autocomplete-debug.js");
print load_js("../lib/yui/datasource/datasource-min.js");
print load_js("../lib/yui/datatable/datatable-min.js");

print load_css("../css/datatable.css");

*/

if ( $domain_overquota == 1 ){
	print $PALANG['pAdminList_domain_overquota']."<br>";
	print "(".$total_used['domains']."/".$account_quota['domains'].")";
 }

?>


<br/>
<br/>

<div id="domain_list-nav"></div>
<div id="domain_list"></div>

<br/>
<br/>

<div id="domain_alias_list-nav"></div>
<div id="domain_alias_list"></div>


<p />
<?php 

$ajax_domain->end();

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
