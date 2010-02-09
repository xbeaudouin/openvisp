<div id="toppage">
<div class="menu"><?php print $PALANG['main_welcome'] . " (". $SESSID_USERNAME . ")"; ?></div>
<br />
<table class="main">
<tr>
<?php

if(check_mail_admin($SESSID_USERNAME,"1"))
  {
		print "<td><a target=\"_top\" href=\"../mail/overview.php\"><img src=\"../images/ico-email.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"></a></td>";
		print "<td><a target=\"_top\" href=\"../mail/overview.php\">" . $PALANG['pMainMain_mail_admin'] . "</td>\n";
		print "</tr>";


		if( check_policyhosting() )
			{
				print "<tr>";
				print "<td><a target=\"_top\" href=\"../mail/policyd.php\"><img src=\"../images/copwhite.gif\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"></a></td>";
				print "<td><a target=\"_top\" href=\"../mail/policyd.php\">" . $PALANG['pMainMain_policyd'] . "</td>\n";
				print "</tr>";
			}

  }

if ( check_domain_admin($SESSID_USERNAME) )
{
  print "<tr>\n<td nowrap><a target=\"_top\" href=\"../users/list-domain.php\"><img src=\"../images/ico-system.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"</a></td>\n";
  print "<td><a target=\"_top\" href=\"../users/list-domain.php?username=" . $SESSID_USERNAME . "\">".$PALANG['pWhostMenu_domain'] . "\n </a></td></tr>\n";


?>


<tr>
  <td nowrap><a target="_top" href="../users/userinfo.php"><img src="../images/ico-infos.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="../users/userinfo.php"><?php print $PALANG['pMain_userinfo']; ?></a></td>
</tr>

<?php

}

if ( check_admin($SESSID_USERNAME)  ){
	?>

<tr>
  <td nowrap><a target="_top" href="../accounts/list-accounts.php"><img src="../images/ico-manage_user.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="../accounts/list-accounts.php"><?php print $PALANG['pMainMain_account_admin']; ?></a></td>
</tr>

<?php
 }

if ( $CONF['manage_server'] == 'YES' )
	{
?>

<tr>
  <td nowrap><a target="_top" href="../server/manage.php"><img src="../images/servers.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="../server/manage.php"><?php print $PALANG['pMainMain_server_admin']; ?></a></td>
</tr>


<?php
 }

  print "<tr>\n<td nowrap><a target=\"_top\" href=\"../mailbox-password.php\"><img src=\"../images/ico-keys.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"></a></td>\n";
  print "<td><a target=\"_top\" href=\"../mailbox-password.php\">" . $PALANG['pUsersMenu_password'] . "</a></td></tr>\n";

if($CONF['datacenter'] == "YES")
{
if(check_datacenter_admin($SESSID_USERNAME)) {
	print "<tr>\n<td nowrap><a target=\"_top\" href=\"../datacenter/main.php\"><img src=\"../images/rack.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"></a></td>\n";
       print "<td><a target=\"_top\" href=\"../datacenter/main.php\">".$PALANG['pMainMain_datacenter_admin'] . "\n </a></td></tr>\n";
 }
}

if( check_webhosting() )
{
if (check_webhosting_admin($SESSID_USERNAME))
{
  print "<tr>\n<td nowrap><a target=\"_top\" href=\"../hosting/list-webvirtual.php?username=" . $SESSID_USERNAME . "\"><img src=\"../images/ico-browser.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"</a></td>\n";
  print "<td><a target=\"_top\" href=\"../hosting/list-webvirtual.php?username=" . $SESSID_USERNAME . "\">".$PALANG['pMainMain_web_admin'] . "\n </a></td></tr>\n";
}
}

if ( check_dbhosting() ){

	if ( check_database_admin($SESSID_USERNAME)){
		print "<tr>\n<td nowrap><a target=\"_top\" href=\"../databases/list-databases.php?username=" . $SESSID_USERNAME . "\"><img src=\"../images/mysql_logo.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"</a></td>\n";
		print "<td><a target=\"_top\" href=\"../databases/list-databases.php?username=" . $SESSID_USERNAME . "\">".$PALANG['pMainMain_database_admin'] . "\n </a></td></tr>\n";
	}

 }

if ( ($CONF['vacation'] == 'YES') && (is_enduser_mailbox_account($SESSID_USERNAME)) )
{

	$user_vacation = is_in_vacation($SESSID_USERNAME);

	// $result = db_query("SELECT * FROM vacation WHERE email='$SESSID_USERNAME' and active ='1'");
if ($user_vacation['rows'] == 1)
{
  $vacation_status=$PALANG['pOverview_mailbox_responder_msg_status_active'];
}
else
{
  $vacation_status=$PALANG['pOverview_mailbox_responder_msg_status_inactive'];
}

print "   <td nowrap>\n";
print "      <a target=\"_top\" href=\"vacation.php\"><img src=\"../images/ico-responder.png\" width=\"64\" height=\"64\" class=\"png_main\" border=\"0\"></a><br/>".$vacation_status."\n";
print "   </td>\n";
print "   <td>\n";
print "      " . $PALANG['pUsersMain_vacation'] . "\n";
print "   </td>\n";
print "</tr>\n";
}

$sql_query = "SELECT mailbox.allowchangefwd, mailbox.allowchangepwd, domain.allowchangepwd as domallowchangepwd, domain.allowchangefwd as domallowchangefwd
FROM mailbox, domain
WHERE mailbox.username='$SESSID_USERNAME'
AND mailbox.domain_id=domain.id";

$result = db_query($sql_query);
if ($result['rows'] == 1)
{
  $row = db_array($result['result']);
  if ( ($row['allowchangefwd'] == 1 ) && ($row['domallowchangefwd']) && ($CONF["usermanagefwd"] == "YES") )
  {
  ?>
  <tr>
  <td nowrap><a target="_top" href="edit-alias.php"><img src="../images/ico-system.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="edit-alias.php"><?php print $PALANG['pUsersMenu_edit_alias']; ?></a></td>
  </tr>
																		
  <?php
    if ( !(check_domain_admin($SESSID_USERNAME))){
  ?>
  <tr>
  <td nowrap><a target="_top" href="../mailbox-password.php"><img src="../images/ico-keys.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="../mailbox-password.php"><?php print $PALANG['pUsersMenu_password']; ?></a></td>
  </tr>

  <?php
    }
  }
}
?>
</tr>
<?php 
if ( is_enduser_mailbox_account($SESSID_USERNAME) )
{ 
?>

<tr>
  <td nowrap><a target="_top" href="list-filter.php"><img src="../images/ico-filter.png" width="64" height="64" class="png_main" border="0"></a></td>
  <td><a target="_top" href="list-filter.php"><?php print $PALANG['pUsersMenu_filter']; ?></a></td>
</tr>

<?php
}
?>

<tr>
<td nowrap><a target="_top" href="../logout.php"><img src="../images/ico-exit.png" width="64" height="64" class="png_main" border="0"></a></td>
<td><a target="_top" href="../logout.php"><?php print $PALANG['pMainMain_logout']; ?></a></td>
</tr>
</table>
<br /><br />
</div>
