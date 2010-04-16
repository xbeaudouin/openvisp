<form name="overview" method="post">
<select name="fDomain" onChange="this.form.submit()";>

<?php
for ($i = 0; $i < sizeof ($user_info->data_managed_active_domain); $i++)
{
   if ($fDomain == $user_info->data_managed_active_domain[$i]['domain'])
   {
      print "<option value=\"".$user_info->data_managed_active_domain[$i]['domain']."\" selected>".$user_info->data_managed_active_domain[$i]['domain']."</option>\n";
   }
   else
   {
      print "<option value=\"".$user_info->data_managed_active_domain[$i]['domain']."\">".$user_info->data_managed_active_domain[$i]['domain']."</option>\n";
   }
}
?>

</select>
<input type="hidden" name="limit" value="0">
<input type="submit" name="go" value="<?php print $PALANG['pOverview_button']; ?>" />
</form>
<p />

<?php 

print "<b>". $PALANG['pOverview_welcome'] . $fDomain . "</b><br />\n";
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
