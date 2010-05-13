<form name="overview" method="post">
<?php
  print $PALANG['pOverview_welcome_text'];
?>

<script type="text/javascript">
	var myBuildUrl = function(record) {
		var url = '';
		var cols = this.getColumnSet().keys;
		for (var i = 0; i < cols.length; i++) {
			url += '&' + cols[i].key + '=' + escape(record.getData(cols[i].key));
		}
		return url;
	};
</script>

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

$ajax_yui->start();

/* $search_form = array(); */
/* $search_form[] = array( */
/*   "name" => "domain_name", */
/*   "minQueryLength" => "3", */
/*   "form_inputname" => "Domain: "  */
/*   ); */
/* $ajax_yui->add_search_form($search_form); */


?>
<style type="text/css">

</style>

<div id="dt-pag-nav"></div> 
<div id="data"></div>



<?php

	// 																			"link" => "/mail/overview.php?domain=" 

if ( $CONF['quota'] == 'YES') {
  $item_list= array(
										"domain" => array(
																			"sortable" => "true",
																			"parser" => "text"
																			),
										"aliases" => array ( "sortable" => "false", "parser" => "number" ),
										"quota_aliases" => array (
																							"sortable" => "false",
																							"parser" => "number",
																							"editor" => "textarea"
																							),
										"mailboxes" => array ( "sortable" => "false", "parser" => "number"),
										"quota_mailboxes" => array (
																								"sortable" => "false",
																								"parser" => "number",
																								"editor" => "textarea"
																								),
										"maxquota" => array ( 
																				 "sortable" => "false",
																				 "parser" => "number",
																				 "editor" => "textarea"
																					),
										"diskspace_mailboxes" => array("sortable" => "false", "parser" =>  "number")

    );
  //    "delete" => "../images/ico-exit.png|delete row|Are you sure to delete ?|./manage-app.php|action=delete"
}
else{
  $item_list = array(
										 "name" => array( "sortable" => "true", "link" => "/mail/overview.php?domain="),
										 "aliases" => array ( "sortable" => "false"),
										 "mailboxes" => array ("sortable" => "false"),
										 "maxquota" => array ("sortable" => "false"),
										 "diskspace_mailboxes" => array("sortable" => "false", "editor" =>  "textarea")
										 );
}

$ajax_info = array(
  "url" => "../ajax/mail/domain_mail_overview.php?",
  "method" => "post"
  );


$ajax_yui->ajax_info($ajax_info);
$ajax_yui->attr_add('root','records');
$ajax_yui->attr_add('sort','domain');
$ajax_yui->attr_add('sortdir','asc');
$ajax_yui->attr_add('startindex','0');
$ajax_yui->attr_add('maxrows','10');
$ajax_yui->attr_add('data_div','data');


$ajax_yui->item_add($item_list);
$ajax_yui->create_celleditor();
$ajax_yui->create_listener();
//$ajax_yui->create_search();
$ajax_yui->end();
?>




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

  
for ($i = 0; $i < sizeof ($user_info->data_managed_active_domain); $i++)
{

  if ((is_array ($user_info->data_managed_active_domain) and sizeof ($user_info->data_managed_active_domain) > 0))
  {

    $domain_info->fetch_by_domainid($user_info->data_managed_active_domain[$i]['id']);
    
      if ( $domain_info->quota['mail_aliases'] != 0 && $domain_info->quota['mailboxes'] != 0 ){
				$domain_policy = get_domain_policy ($user_info->data_managed_active_domain[$i]['domain']);

				$total_alias_count += $domain_info->used_quota['mail_alias'];
				$total_mailbox_count += $domain_info->used_quota['mailbox'];

				if ( $total_maxalias_count != -1 ){
				  if ( $domain_info->quota['mail_aliases'] == "-1" ){ $total_maxalias_count = -1; }
				  else { $total_maxalias_count += $domain_info->quota['mail_aliases']; }
				}
				if ( $total_maxmailbox_count != -1 ){
				  if ( $domain_info->quota['mailboxes'] == "-1" ){ $total_maxmailbox_count = -1; }
				  else { $total_maxmailbox_count += $domain_info->quota['mailboxes']; }
				}

				print "<tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">";
				print "<td><a href=\"overview.php?domain=" . $user_info->data_managed_active_domain[$i]['domain'] . "\">" . $user_info->data_managed_active_domain[$i]['domain'] . "</a></td>";
				print "<td>" . $domain_info->used_quota['mail_alias'] . " / ";

				switch ($domain_info->quota['mail_aliases']) {
        	case "0" : print $PALANG['pOverview_limit_none']; break;
        	case "-1": print "&infin;"; break;
        	default  : print $domain_info->quota['mail_aliases']; break;
        }
        
        print "</td><td>" . $domain_info->used_quota['mailbox'] . " / ";
        switch ($domain_info->quota['mailboxes']) {
        	case "0" : print $PALANG['pOverview_limit_none']; break;
        	case "-1": print "&infin;"; break;
        	default  : print $domain_info->quota['mailboxes']; break;
        }
        print "</td>";
        if ($CONF['quota'] == 'YES') {
      	  print " <td>";
      	  switch($domain_info->quota['maxquota']) {
      	  	case "-1" : print "&infin;"; break;
      	  	default   : print $domain_info->quota['maxquota']; break;
      	  }
      	  print "</td>";
      	}

      	
      	$domain_info->total_diskspace_used_mailboxes();
      	print " <td> " .  number_format($domain_info->data['total_diskspace_used_mailboxes'],0, ',', ' ')."</td>";
      	$total_mbdisk_count += $domain_info->data['total_diskspace_used_mailboxes'];
      	print "<td><a href=\"edit-active-domain-policy.php?domain=" . $user_info->data_managed_active_domain[$i]['domain'] . "\">" . $PALANG['pOverview_get_security_edit'] . "</a></td>";
      	print "</tr>";
      }
    }
}

if ( $total_maxmailbox_count > 0 ){
  $total_maxmailbox_count = "/".$total_maxmailbox_count;
}
else{ $total_maxmailbox_count = "/NA"; }

if ( $total_maxalias_count > 0 ){
  $total_maxalias_count = "/".$total_maxalias_count;
}
else{ $total_maxalias_count = "/NA"; }

print "   <tr>\n";
print "      <td>Total</td>\n";
print "      <td>".$total_alias_count.$total_maxalias_count."</td>\n";
print "      <td>".$total_mailbox_count.$total_maxmailbox_count."</td>\n";

if ($CONF['quota'] == 'YES') print "      <td></td>\n";
print "      <td>".number_format($total_mbdisk_count,0, ',', ' ')."</td>\n";
print "      <td></td>\n";
print "   </tr>\n";


print "</table>\n";
print "<p />\n";
?>
