<div id="menu">
<?php
	 if ( isset($template) && ($template == "edit-filter.tpl" || $template == "list-filter.tpl" || $template == "add-filter.tpl") ){
?>
<?php
print_menu("users/add-filter.php",$PALANG['pMenu_addfilter']);
print_dot();
print_menu("users/list-filter.php",$PALANG['pMenu_listfilter']);
print_dot();

	 }
	 if ( check_mail_admin($SESSID_USERNAME,"nologout") == false )
{

	print_menu("users/main.php",$PALANG['pMenu_logout']);
	print_dot();

}
else
{

	print_menu("sendmail.php", $PALANG['pMenu_sendmail']);
	print_dot();
	print_menu("users/viewlog.php", $PALANG['pMenu_viewlog']);
	print_dot();
	print_menu("mail/overview.php", $PALANG['pMenu_overview']);

	if ( ($tDomain != NULL ) && ( isset($overview2) ) ){
		print_menu("mail/overview.php?domain=$tDomain",$PALANG['pMenu_overview'].' '.$tDomain);
		print_dot();
	}

	print_menu("users/main.php", $PALANG['pMenu_logout']);
}
?>
</div>
<p>
