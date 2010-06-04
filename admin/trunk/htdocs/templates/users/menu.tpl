<div id="menu">
<?php
  if ( $user_info->rights['domain'] == 1){
     $domain_overquota = 1;
     if ( ( $user_info->data_managed['domains'] < $user_info->data_quota['domains'] ) || $user_info->data_quota['domains'] == "-1" ) {
        $domain_overquota = 0;
        print_menu("domain/create-domain.php",$PALANG['pAdminMenu_create_domain']);
        print_dot();
        print_menu("domain/import-domain.php",$PALANG['pAdminMenu_import_domain']);
        print_dot();
        print_menu("users/create-domain-alias.php",$PALANG['pAdminMenu_create_domain_alias']);
        print_dot();
     }
     print_menu("users/list-domain.php",$PALANG['pAdminMenu_list_domain']);
     print "<p>";
     print_menu("users/viewlog.php",$PALANG['pAdminMenu_viewlog']);
     print_dot();
  }
  print_menu("users/main.php",$PALANG['pAdminMenu_logout']);
?>
</div>
