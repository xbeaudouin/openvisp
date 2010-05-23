<style type="text/css">

.yui-dt-editor {
position:absolute;
z-index:9000;
}

</style>

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

print "<b>". $PALANG['pOverview_welcome'] . "<div id='domain_name'>".$fDomain . "</div></b><br />\n";
print $PALANG['pOverview_alias_alias_count'] . ": " . $domain_info->used_quota['aliases'] . " / "; 

switch ($domain_info->quota['aliases']) {
  case "0" : print $PALANG['pOverview_limit_none']; break;
  case "-1": print "&infin;"; break;
  default  : print $domain_info->quota['aliases']; break;
}

print " &nbsp; ";
print $PALANG['pOverview_alias_mailbox_count'] . ": " . $domain_info->used_quota['mailboxes'] . " / ";

switch ($domain_info->quota['mailboxes']) {
 case "0" : print $PALANG['pOverview_limit_none']; break;
 case "-1": print "&infin;"; break;
 default  : print $domain_info->quota['mailboxes']; break;
}

print "<br />\n";
print "<div id=\"submenu\">\n";
print $PALANG['pOverview_tasks'] . "&nbsp;:";

if( $domain_info->can_add_mail_alias() ){
  print "<a target=\"_top\" href=\"create-alias.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_create_alias'] . "</a>";
	print "&middot;";
	print "<a target=\"_top\" href=\"import-alias.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_import_alias'] . "</a>";
} else {
	print $PALANG['pOverview_no_add_aliases'];
 }

print "&middot;";

if( $domain_info->can_add_mailbox() ){
   print "<a target=\"_top\" href=\"create-mailbox.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_create_mailbox'] . "</a>";
	 print "&middot;";
	 print "<a target=\"_top\" href=\"import-mailbox.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_import_mailbox'] . "</a>";
 } else {
   print $PALANG['pOverview_no_add_mailboxes'];
 }

// Remove statistics link temporary.
print "&middot;";
print "<a target=\"_top\" href=\"stats-domain.php?domain=" . $fDomain . "\">" . $PALANG['pOverview_statistics'] . "</a>";
print "&middot;";

print "<a target=\"_top\" href=\"tools.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_tools_domain'] . "</a>";
print_dot();
//print "<a target=\"_top\" href=\".php?domain=" . $fDomain . "\">" . $PALANG['pMenu_tools_domain'] . "</a>";
print_menu("gen-pdf.php?domain=".$fDomain."&type=domainemail",$PALANG['pMenu_pdf_domainemail']);

print "</div>";
if ($tDisplay_back_show == 1) print "<a href=\"overview.php?domain=$fDomain&limit=$tDisplay_back\"><img src=\"../images/back.gif\"></a>\n";
if ($tDisplay_up_show == 1) print "<a href=\"overview.php?domain=$fDomain&limit=0\"><img src=\"../images/up.gif\"></a>\n";
if ($tDisplay_next_show == 1) print "<a href=\"overview.php?domain=$fDomain&limit=$tDisplay_next\"><img src=\"../images/next.gif\"></a>\n";

print "<form action=\"\" method=\"post\"> ";
print "<input type=\"hidden\" name=\"fDomain\" value=\"".$fDomain."\">";
print "Email : <input type=\"text\" name=\"fMail_Search\" value=\"".$fMail_Search."\"> <input type=\"submit\"> ";
print "</form>";

?>


<div id="aliases-nav"></div>
<div id="aliases"></div>

<br/>
<br/>


<div id="mailboxes-nav"></div>
<div id="mailboxes"></div>


<?php

$ajax_alias->end();

$ajax_mailbox->end();

?>


<br/>
<br/>
