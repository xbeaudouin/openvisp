<div id="menu">
<?php


	 // Mysql PAR	 
	 if ( check_dbhosting() ){
		 
		 if ( (check_mysql_database_admin($SESSID_USERNAME) == true ) && ( ($total_used['mysqldb'] <= $account_quota['mysqldb'])  || ( $account_quota['mysqldb'] == '-1') ) ){
			 print_menu("databases/add-databases.php?username=" . $SESSID_USERNAME."&dbtype=mysql",$PALANG['pDBMenu_add_mysql_database']);
			 print_dot();
		 }
 		 if ( (check_mysql_database_admin($SESSID_USERNAME) == true ) && ( ($total_used['mysqlusers'] <= $account_quota['mysqlusers'])  || ( $account_quota['mysqlusers'] == '-1') ) ){
			 print_menu("databases/add-user.php?username=" . $SESSID_USERNAME."&dbtype=mysql",$PALANG['pDBMenu_add_mysql_user']);
			 print_dot();
		 }
		 
	 }


 // Postgresql Part

   if ( check_dbhosting('postgresql') ){
	
		 if ( (check_postgresql_database_admin($SESSID_USERNAME) == true ) && ( ($total_used['postgresqldb'] <= $account_quota['postgresqldb'])  || ( $account_quota['postgresqldb'] == '-1') ) ){
			 print_menu("databases/add-databases.php?username=" . $SESSID_USERNAME."&dbtype=mysql",$PALANG['pDBMenu_add_postgresql_database']);
			 print_dot();
		 }
	 }

print "<br/>";
	 if ( check_database_admin($SESSID_USERNAME) == true ){
		 print_menu("databases/list-databases.php?username=" . $SESSID_USERNAME,$PALANG['pDBMenu_list_db']);
		 print_dot();
	 }

	 if ( check_database_admin($SESSID_USERNAME) == true ){
		 print_menu("databases/list-dbusers.php?username=" . $SESSID_USERNAME,$PALANG['pDBMenu_list_db']);
		 print_dot();
	 }

print_menu("users/main.php",$PALANG['pAdminMenu_logout']);



?>
</div>
