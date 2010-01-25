<div id="menu">
<?php
	 if ( check_ftp_admin($SESSID_USERNAME) == true ){
?>

 <a target="_top" href="list-ftp.php?username=<?php print $SESSID_USERNAME; ?>"><?php print $PALANG['pAdminMenu_list_ftp']; ?></a>&middot;

<?php
	 }

	 if ( check_http_admin($SESSID_USERNAME) == true ){
?>

 <a target="_top" href="list-webvirtual.php?username=<?php print $SESSID_USERNAME; ?>"><?php print $PALANG['pAdminMenu_list_webvirtual']; ?></a>
<p>

<?php
   }
if ( (check_ftp_admin($SESSID_USERNAME) == true ) && ( ($total_used['ftp'] <= $account_quota['ftp'])  || ( $account_quota['ftp'] == '-1') ) ){
?>

 <a target="_top" href="add-ftp.php?username=<?php print $SESSID_USERNAME; ?>"><?php print $PALANG['pWhostMenu_add_ftp']; ?></a>&middot;

<?php
	 }

if ( ( check_http_admin($SESSID_USERNAME) == true ) && ( ($total_used['http'] <= $account_quota['http']) || ($account_quota['http'] == '-1' ) ) ){
?>

 <a target="_top" href="add-website.php?username=<?php print $SESSID_USERNAME; ?>"><?php print $PALANG['pWhostMenu_add_website']; ?></a>&middot;
 <a target="_top" href="add-website-alias.php?username=<?php print $SESSID_USERNAME; ?>"><?php print $PALANG['pWhostMenu_add_website_alias']; ?></a>&middot;

<?php
		}
 print "<a target=\"_top\" href=\"../users/main.php\">" . $PALANG['pAdminMenu_logout'] . "</a>\n";
?>
</div>
