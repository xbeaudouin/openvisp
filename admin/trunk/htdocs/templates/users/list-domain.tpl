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
	print "(".$user_info->data_managed['domains']."/".$user_info->data_quota['domains'].")";
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

  print "<p />\n";

?>
