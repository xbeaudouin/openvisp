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
print $PALANG['pOverview_alias_alias_count'] . ": " . $domain_info->used_quota['mail_alias'] . " / "; 

switch ($domain_info->quota['mail_aliases']) {
  case "0" : print $PALANG['pOverview_limit_none']; break;
  case "-1": print "&infin;"; break;
  default  : print $domain_info->quota['mail_aliases']; break;
}

print " &nbsp; ";
print $PALANG['pOverview_alias_mailbox_count'] . ": " . $domain_info->used_quota['mailbox'] . " / ";

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



$ajax_yui->start("alias");

?>


<div id="aliases-nav"></div>
<div id="aliases"></div>


<?php

  $item_list= array(
										"alias" => array(
																		 "label" => $PALANG['pOverview_alias_address'],
																		 "parser" => "text"
																		 ),
										"goto" => array(
																		"label" => $PALANG['pOverview_alias_goto'],
																		"parser" => "text"
																		),
										"modified" => array(
																				"label" => $PALANG['pOverview_alias_modified'],
																				"parser" => "text",
																				"sortable" => "false"
																				),
										"active" => array(
																			"label" => "active",
																			"parser" => "text",
																			"radioOptions" => array (
																															 "items" => '["'.$PALANG['YES'].'", "'.$PALANG['NO'].'"]',
																															 "url" => "/ajax/mail/manage_alias.php",
																															 "url_param" => "action=mod_status&domainName=$fDomain"
																															 )
																			),
										"delete" => array(
																			"label" => "delete",
																			"sortable" => "false",
																			"resizeable" => "false",
																			"link" => "/ajax/mail/manage_alias.php",
																			"url_param" => "action=delete&domainName=$fDomain",
																			"key_item" => "alias"
																			),
										"edit" => array(
																			"label" => "",
																			"sortable" => "false",
																			"resizeable" => "false"
																			)
										);

/*
																			"link" => "/ajax/mail/alias_activation.php?domainName=$fDomain",

										"amavis" => array(
																			"label" => $PALANG['pOverview_alias_amavisd'],
																			"parser" => "text",
																			"link" => "/mail/edit-security.php?domainName=$fDomain"
																			),

										"children" => array (
																				 "label" => "",
																				 array (
																								"key" => "delete",
																								"label" => "",
																								"sortable" => "false",
																								"resizeable" => "false",
																								"parser" => "myBuildUrl"
																								),
																				 array ( 
																								"key" => "edit",
																								"label" => "",
																								"sortable" => "false",
																								"resizeable" => "false",
																								"parser" => "myBuildUrl"
																								 )
																				 )

 */

$ajax_info = array(
  "url" => "../ajax/mail/domain_alias_detail.php?domainName=$fDomain",
  "method" => "post",
  "params" => array ( "domain_name" => $fDomain )
  );


$ajax_yui->ajax_info($ajax_info);
//$ajax_yui->attr_add('domain_name',$fDomain);
$ajax_yui->attr_add('root','records');
$ajax_yui->attr_add('sort','alias');
$ajax_yui->attr_add('sortdir','asc');
$ajax_yui->attr_add('startindex','0');
$ajax_yui->attr_add('maxrows','10');
$ajax_yui->attr_add('data_div','aliases');


$ajax_yui->item_add($item_list);
//$ajax_yui->create_celleditor();
$ajax_yui->create_listener();
//$ajax_yui->create_search();
$ajax_yui->end();


if (sizeof ($domain_info->list_mail_aliases) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pOverview_alias_address'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_alias_goto'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_alias_modified'] . "</td>\n";
   print "	<td>" . $PALANG['pOverview_alias_amavisd'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($domain_info->list_mail_aliases); $i++)
   {
      if ((is_array ($domain_info->list_mail_aliases) and sizeof ($domain_info->list_mail_aliases) > 0))
      {
         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         print "      <td>" . $domain_info->list_mail_aliases[$i]['address'] . "</td>\n";
         print "      <td>" . ereg_replace (",", "<br>", $domain_info->list_mail_aliases[$i]['goto']) . "</td>\n";
         print "      <td>" . $domain_info->list_mail_aliases[$i]['modified'] . "</td>\n";
	 #print " <td> " . $tAlias[$i]['policy_id'] . "</td>\n";
	 $policy_id = ($domain_info->list_mail_aliases[$i]['policy_id'] == 1) ? $PALANG['NO'] : $PALANG['YES'];
	 print " <td><a href=\"edit-security.php?address=" . urlencode ($domain_info->list_mail_aliases[$i]['address']) . "&domain=$fDomain" . "\">" . $policy_id . "</a></td>\n";
         print "      <td><a href=\"edit-alias.php?address=" . urlencode ($domain_info->list_mail_aliases[$i]['address']) . "&domain=$fDomain" . "\">" . $PALANG['edit'] . "</a></td>\n";
         print "      <td><a href=\"delete.php?delete=" . urlencode ($domain_info->list_mail_aliases[$i]['address']) . "&domain=$fDomain" . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
         print "   </tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}

if (sizeof ($domain_info->list_mailboxes) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pOverview_mailbox_username'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_mailbox_name'] . "</td>\n";
   if ($CONF['quota'] == 'YES') print "      <td>" . $PALANG['pOverview_mailbox_quota'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_mailbox_modified'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_mailbox_active'] . "</td>\n";
   print "	    <td>" . $PALANG['pOverview_mailbox_amavisd'] . "</td>\n";
   print "	    <td>" . $PALANG['pOverview_mailbox_responder'] . "</td>\n";
   print "	    <td>" . $PALANG['pOverview_mailbox_forward'] . "</td>\n";
   print "	    <td>" . $PALANG['pOverview_mailbox_quarantine'] . "</td>\n";
   print "	    <td>" . $PALANG['pOverview_mailbox_size'] . "</td>\n";
   print "      <td colspan=\"3\">&nbsp;</td>\n";
   print "   </tr>\n";
      
   for ($i = 0; $i < sizeof ($domain_info->list_mailboxes); $i++)
   {
      if ((is_array ($domain_info->list_mailboxes) and sizeof ($domain_info->list_mailboxes) > 0))
      {

				 $classoff="hilightoff";
				 $classon="hilighton";
				 if ( $domain_info->list_mailboxes[$i]['paid'] == 0 ){
					 $classon="lockhilighton";
					 $classoff="lockhilightoff";
				 }

         print "   <tr class=\"$classoff\" onMouseOver=\"className='$classon';\" onMouseOut=\"className='$classoff';\">\n";
         print "      <td>" . $domain_info->list_mailboxes[$i]['username'] . "</td>\n";
         print "      <td>" . $domain_info->list_mailboxes[$i]['name'] . "</td>\n";
         if ($CONF['quota'] == 'YES') {
            print "<td>";
						//						check_quota_user ($domain_info->list_mailboxes[$i]['username']);
						//print "/";
            if($domain_info->list_mailboxes[$i]['quota'] <= "0") {
              print "&infin;";
            } else {
              print $domain_info->list_mailboxes[$i]['quota'] / $CONF['quota_multiplier'];
            }
            print "</td>";
         }
         print "      <td>" . $domain_info->list_mailboxes[$i]['modified'] . "</td>\n";
         $active = ($domain_info->list_mailboxes[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "      <td><a href=\"edit-active.php?username=" . urlencode ($domain_info->list_mailboxes[$i]['username']) . "&domain=$fDomain" . "\">" . $active . "</a></td>\n";
				 $policy_id = ($domain_info->list_mailboxes[$i]['policy_id'] == 1) ? $PALANG['NO'] : $PALANG['YES'];
				 print " <td><a href=\"edit-security.php?username=" . urlencode ($domain_info->list_mailboxes[$i]['username']) . "&domain=$fDomain" . "\">" . $policy_id . "</a></td>\n";

				 //				 $responder_status = is_in_vacation();

				 if ($domain_info->list_mailboxes[$i]['vacation_active'] == 1){
					 $responder_status = $PALANG['pOverview_mailbox_responder_active'];
				 }
				 else{
					 $responder_status = $PALANG['pOverview_mailbox_responder_inactive'];
				 }

				// XXX: Fix this !!!
				 print '      <td>'.$responder_status.'&nbsp;&nbsp;&nbsp;<a href="edit-vacation.php?username='. urlencode ($domain_info->list_mailboxes[$i]['username']) .'&domain='.$fDomain.'">'.$PALANG['edit'].'</a></td>';

				 $result = db_query("SELECT * FROM alias WHERE address='".$domain_info->list_mailboxes[$i]['username']."' AND goto='".$domain_info->list_mailboxes[$i]['username']."' AND active='1'");
				 if ($result['rows'] == 1){
					 $forward_status = $PALANG['pOverview_mailbox_forward_inactive'];
				 }
				 else{
					 $forward_status = $PALANG['pOverview_mailbox_forward_active'];
				 }

				 print '      <td>'.$forward_status.'&nbsp;&nbsp;&nbsp;<a href="edit-alias.php?address='. urlencode ($domain_info->list_mailboxes[$i]['username']) .'&domain='.$fDomain.'">'.$PALANG['edit'].'</a></td>';
				 print '      <td>';
				 if ( $domain_info->list_mailboxes[$i]['id'] != '' ){
					 print '        <a href="'.$CONF['release_url'].'?key='.$domain_info->list_mailboxes[$i]['id'].'&key2='.$domain_info->list_mailboxes[$i]['key2'].'">Quarantine</a>';
				 }
				 print '      </td>';
				 print '      <td>'.check_quota_user($domain_info->list_mailboxes[$i]['username']);
				 $date_overquota = check_overquota_user($domain_info->list_mailboxes[$i]['username']);
				 if ( $date_overquota != NULL ){
					 print ' | Overquota : '.$date_overquota;
				 }
				 print '</td>';

         if ($CONF['encrypt'] == "cleartext")
         {
				    print '      <td><a href="../gen-pdf.php?username='. urlencode ($domain_info->list_mailboxes[$i]['username']) .'&domain='.$fDomain.'&type=email">PDF</a></td>';
         }
         print "      <td><a href=\"edit-mailbox.php?username=" . urlencode ($domain_info->list_mailboxes[$i]['username']) . "&domain=$fDomain" . "\">" . $PALANG['edit'] . "</a></td>\n";

         print "      <td><a href=\"delete.php?delete=" . urlencode ($domain_info->list_mailboxes[$i]['username']) . "&domain=$fDomain" . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
         print "   </tr>\n";
      }
   }
   print "</table>\n";
   print "<p />\n";
}
?>
