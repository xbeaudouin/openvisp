<div id="menu">
<?php
	 print_menu("accounts/list-accounts.php",$PALANG['pAccountMenu_list_accounts']);
print_dot();
print_menu("accounts/add-account.php",$PALANG['pAccountMenu_add_account']);
echo "<p>";
print_menu("users/viewlog.php",$PALANG['pAccountMenu_viewlog']);
print_dot();
/*
print_menu("backup.php",$PALANG['pAccountMenu_backup']);
print_dot();
*/
	 print_menu("users/main.php",$PALANG['pAccountMenu_logout']);

/*  <a target="_top" href="list-accounts.php"><?php print $PALANG['pAccountMenu_list_accounts']; ?></a>&middot;
  <a target="_top" href="add-account.php"><?php print $PALANG['pAccountMenu_add_account']; ?></a>
 <p>
  <a target="_top" href="../viewlog.php"><?php print $PALANG['pAccountMenu_viewlog']; ?></a>&middot;
  <a target="_top" href="../backup.php"><?php print $PALANG['pAccountMenu_backup']; ?></a>&middot;
  <a target="_top" href="../users/main.php"><?php print $PALANG['pAccountMenu_logout']; ?></a>
*/

?>

</div>
