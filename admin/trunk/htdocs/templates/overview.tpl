<form name="overview" method="post">
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
<input type="hidden" name="limit" value="0">
<input type="submit" name="go" value="<?php print $PALANG['pOverview_button']; ?>" />
</form>
<p />
<?php 

print "<b>". $PALANG['pOverview_welcome'] . $fDomain . "</b><br />\n";
print $PALANG['pOverview_alias_alias_count'] . ": " . $limit['alias_count'] . " / "; 
switch ($limit['aliases']) {
 case "0" : print $PALANG['pOverview_limit_none']; break;
 case "-1": print "&infin;"; break;
 default  : print $limit['aliases']; break;
}
print " &nbsp; ";
print $PALANG['pOverview_alias_mailbox_count'] . ": " . $limit['mailbox_count'] . " / ";
switch ($limit['mailboxes']) {
 case "0" : print $PALANG['pOverview_limit_none']; break;
 case "-1": print "&infin;"; break;
 default  : print $limit['mailboxes']; break;
}
print "<br />\n";
print "<div id=\"submenu\">\n";
print $PALANG['pOverview_tasks'] . "&nbsp;:";

if( ( ( ($limit['aliases']=="0") && ($limit['alias_count']>=$limit['aliases']) ) || ( $total_used['emailsaliases']  >= $account_quota['emails_alias']) ) && ($account_quota['emails_alias'] != "-1") ) {
	print $PALANG['pOverview_no_add_aliases'];
 } else {
	print "<a target=\"_top\" href=\"create-alias.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_create_alias'] . "</a>";
	print "&middot;";
	print "<a target=\"_top\" href=\"import-alias.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_import_alias'] . "</a>";
 }

print "&middot;";

if( ( ( ($limit['mailboxes']=="0") && ($limit['mailbox_count']>=$limit['mailboxes'] ) ) || ( $total_used['emails']  >= $account_quota['emails']) ) && ($account_quota['emails'] != "-1")){
   print $PALANG['pOverview_no_add_mailboxes'];
 } else {
   print "<a target=\"_top\" href=\"create-mailbox.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_create_mailbox'] . "</a>";
	 print "&middot;";
	 print "<a target=\"_top\" href=\"import-mailbox.php?domain=" . $fDomain . "\">" . $PALANG['pMenu_import_mailbox'] . "</a>";

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

if (sizeof ($tAlias) > 0)
{
   print "<table>\n";
   print "   <tr class=\"header\">\n";
   print "      <td>" . $PALANG['pOverview_alias_address'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_alias_goto'] . "</td>\n";
   print "      <td>" . $PALANG['pOverview_alias_modified'] . "</td>\n";
   print "	<td>" . $PALANG['pOverview_alias_amavisd'] . "</td>\n";
   print "      <td colspan=\"2\">&nbsp;</td>\n";
   print "   </tr>\n";

   for ($i = 0; $i < sizeof ($tAlias); $i++)
   {
      if ((is_array ($tAlias) and sizeof ($tAlias) > 0))
      {
         print "   <tr class=\"hilightoff\" onMouseOver=\"className='hilighton';\" onMouseOut=\"className='hilightoff';\">\n";
         print "      <td>" . $tAlias[$i]['address'] . "</td>\n";
         print "      <td>" . ereg_replace (",", "<br>", $tAlias[$i]['goto']) . "</td>\n";
         print "      <td>" . $tAlias[$i]['modified'] . "</td>\n";
	 #print " <td> " . $tAlias[$i]['policy_id'] . "</td>\n";
	 $policy_id = ($tAlias[$i]['policy_id'] == 1) ? $PALANG['NO'] : $PALANG['YES'];
	 print " <td><a href=\"edit-security.php?address=" . urlencode ($tAlias[$i]['address']) . "&domain=$fDomain" . "\">" . $policy_id . "</a></td>\n";
         print "      <td><a href=\"edit-alias.php?address=" . urlencode ($tAlias[$i]['address']) . "&domain=$fDomain" . "\">" . $PALANG['edit'] . "</a></td>\n";
         print "      <td><a href=\"delete.php?delete=" . urlencode ($tAlias[$i]['address']) . "&domain=$fDomain" . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
         print "   </tr>\n";
      }
   }

   print "</table>\n";
   print "<p />\n";
}

if (sizeof ($tMailbox) > 0)
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
      
   for ($i = 0; $i < sizeof ($tMailbox); $i++)
   {
      if ((is_array ($tMailbox) and sizeof ($tMailbox) > 0))
      {

				 $classoff="hilightoff";
				 $classon="hilighton";
				 if ( $tMailbox[$i]['paid'] == 0 ){
					 $classon="lockhilighton";
					 $classoff="lockhilightoff";
				 }

         print "   <tr class=\"$classoff\" onMouseOver=\"className='$classon';\" onMouseOut=\"className='$classoff';\">\n";
         print "      <td>" . $tMailbox[$i]['username'] . "</td>\n";
         print "      <td>" . $tMailbox[$i]['name'] . "</td>\n";
         if ($CONF['quota'] == 'YES') {
            print "<td>";
						//						check_quota_user ($tMailbox[$i]['username']);
						//print "/";
            if($tMailbox[$i]['quota'] <= "0") {
              print "&infin;";
            } else {
              print $tMailbox[$i]['quota'] / $CONF['quota_multiplier'];
            }
            print "</td>";
         }
         print "      <td>" . $tMailbox[$i]['modified'] . "</td>\n";
         $active = ($tMailbox[$i]['active'] == 1) ? $PALANG['YES'] : $PALANG['NO'];
         print "      <td><a href=\"edit-active.php?username=" . urlencode ($tMailbox[$i]['username']) . "&domain=$fDomain" . "\">" . $active . "</a></td>\n";
				 $policy_id = ($tMailbox[$i]['policy_id'] == 1) ? $PALANG['NO'] : $PALANG['YES'];
				 print " <td><a href=\"edit-security.php?username=" . urlencode ($tMailbox[$i]['username']) . "&domain=$fDomain" . "\">" . $policy_id . "</a></td>\n";

				 //				 $responder_status = is_in_vacation();

				 if ($tMailbox[$i]['vacation_active'] == 1){
					 $responder_status = $PALANG['pOverview_mailbox_responder_active'];
				 }
				 else{
					 $responder_status = $PALANG['pOverview_mailbox_responder_inactive'];
				 }

				// XXX: Fix this !!!
				 print '      <td>'.$responder_status.'&nbsp;&nbsp;&nbsp;<a href="edit-vacation.php?username='. urlencode ($tMailbox[$i]['username']) .'&domain='.$fDomain.'">'.$PALANG['edit'].'</a></td>';

				 $result = db_query("SELECT * FROM alias WHERE address='".$tMailbox[$i]['username']."' AND goto='".$tMailbox[$i]['username']."' AND active='1'");
				 if ($result['rows'] == 1){
					 $forward_status = $PALANG['pOverview_mailbox_forward_inactive'];
				 }
				 else{
					 $forward_status = $PALANG['pOverview_mailbox_forward_active'];
				 }

				 print '      <td>'.$forward_status.'&nbsp;&nbsp;&nbsp;<a href="edit-alias.php?address='. urlencode ($tMailbox[$i]['username']) .'&domain='.$fDomain.'">'.$PALANG['edit'].'</a></td>';
				 print '      <td>';
				 if ( $tMailbox[$i]['id'] != '' ){
					 print '        <a href="'.$CONF['release_url'].'?key='.$tMailbox[$i]['id'].'&key2='.$tMailbox[$i]['key2'].'">Quarantine</a>';
				 }
				 print '      </td>';
				 print '      <td>'.check_quota_user($tMailbox[$i]['username']);
				 $date_overquota = check_overquota_user($tMailbox[$i]['username']);
				 if ( $date_overquota != NULL ){
					 print ' | Overquota : '.$date_overquota;
				 }
				 print '</td>';

         if ($CONF['encrypt'] == "cleartext")
         {
				    print '      <td><a href="../gen-pdf.php?username='. urlencode ($tMailbox[$i]['username']) .'&domain='.$fDomain.'&type=email">PDF</a></td>';
         }
         print "      <td><a href=\"edit-mailbox.php?username=" . urlencode ($tMailbox[$i]['username']) . "&domain=$fDomain" . "\">" . $PALANG['edit'] . "</a></td>\n";

         print "      <td><a href=\"delete.php?delete=" . urlencode ($tMailbox[$i]['username']) . "&domain=$fDomain" . "\"onclick=\"return confirm ('" . $PALANG['confirm'] . "')\">" . $PALANG['del'] . "</a></td>\n";
         print "   </tr>\n";
      }
   }
   print "</table>\n";
   print "<p />\n";
}
?>
