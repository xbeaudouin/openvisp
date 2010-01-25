<div id="menu">
<?php

print_menu("server/list-server.php?fType=server", $PALANG['pMenu_server_list']);
print_dot();
print_menu("server/list-server.php?fType=model", $PALANG['pMenu_server_list_model']);
print_dot();
print_menu("server/list_apps.php", $PALANG['pMenu_server_list_app']);

print "<br/>";

print_menu("server/add_server.php?fType=server", $PALANG['pMenu_server_add']);
print_dot();
print_menu("server/add_server.php?fType=model", $PALANG['pMenu_server_add_model']);
print_dot();
print_menu("server/add_app.php", $PALANG['pMenu_server_add_app']);
print_dot();
print_menu("users/main.php",$PALANG['pAdminMenu_logout']);

?>
</div>
<p>
