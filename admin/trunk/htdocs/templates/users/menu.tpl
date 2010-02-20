<div id="menu">
<?php
  if (check_domain_admin($SESSID_USERNAME) == true ){
     $domain_overquota = 1;
     if (($total_used['domains'] < $account_quota['domains']) || $account_quota['domains'] == "-1" ) {
        $domain_overquota = 0;
        print_menu("domain/create-domain.php?username=".$SESSID_USERNAME,$PALANG['pAdminMenu_create_domain']);
        print_dot();
        print_menu("domain/import-domain.php?username=".$SESSID_USERNAME,$PALANG['pAdminMenu_import_domain']);
        print_dot();
        print_menu("users/create-domain-alias.php",$PALANG['pAdminMenu_create_domain_alias']);
        print_dot();
     }
     print_menu("users/list-domain.php?username=".$SESSID_USERNAME,$PALANG['pAdminMenu_list_domain']);
     print "<p>";
     print_menu("users/viewlog.php",$PALANG['pAdminMenu_viewlog']);
     print_dot();
  }
  print_menu("users/main.php",$PALANG['pAdminMenu_logout']);
?>
</div>
