<?php
//
// File: add-databases.php
//
// Template File: add-databases.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// -none-
//
require ("../variables.inc.php");
require ("../config.inc.php");
require ("../lib/functions.inc.php");
require ("../lib/hosting.inc.php");
require ("../lib/accounts.inc.php");
include ("../languages/" . check_language () . ".lang");

$SESSID_USERNAME = check_user_session();

$account_information = get_account_info($SESSID_USERNAME);
$account_quota = get_account_quota($account_information['id']);
$account_rights = get_account_right($account_information['id']);
$total_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));


if ( $SESSID_USERNAME != "")
{

  if ( check_database_admin($SESSID_USERNAME) )
		{

			if ($_SERVER['REQUEST_METHOD'] == "GET")
				{

					$fUsername = get_get('username');
					$fDBtype = get_get('dbtype');

					$list_domains = list_domains_mysqldb_available ($fUsername);
					$mysql_admin = check_mysql_database_admin($fUsername);
					$postgresql_admin = check_postgresql_database_admin($fUsername);
					$list_database_server = get_list_database_server();

					include ("../templates/header.tpl");
					include ("../templates/databases/menu.tpl");
					include ("../templates/databases/add-databases.tpl");
					include ("../templates/footer.tpl");
				}

			if ($_SERVER['REQUEST_METHOD'] == "POST"){

					$fUsername = get_post('fUsername');
					$fDBnum = get_post('fDBnum');
					$fDBtype = get_post('fDBtype');
					$fDomain = get_post('fDomain');
					$fServer_apps_id = explode("-", get_post('fServer_id'));
					$fCreateuser = get_post('fCreateuser');
					$list_database_server = get_list_database_server();

					if ($fCreateuser == "on" ){ $fCreateuser = 'Y';} else {$fCreateuser = 'N';}

					$list_domains = list_domains_mysqldb_available ($fUsername);
					$mysql_admin = check_mysql_database_admin($fUsername);
					$postgresql_admin = check_postgresql_database_admin($fUsername);

					if ( $fDBtype == "mysql" ){

						// Check if
						// - the user can manage mysql database
						// - user mysql quota (number of db) is ok
						// - the new database will not make user overquota (first control on ajax in the form and second control again)
						// 
						if ( check_mysql_database_admin($fUsername) && ( ( $total_used['mysqldb'] <= $account_quota['mysqldb'])  || ( $account_quota['mysqldb'] == '-1') ) && 
								 ( check_db_domain_available($fDomain) >= $fDBnum ) ){
							if ( $fCreateuser == "Y" ){
								mysql_create_db_and_user($fDomain, $fDBnum, $fServer_apps_id[0], $fServer_apps_id[1], $fServer_apps_id[2], $fServer_apps_id[3], $fServer_apps_id[4]);
							}
							else{
								mysql_create_onlydb($fDomain,$fDBnum, $fServer_apps_id[0], $fServer_apps_id[1], $fServer_apps_id[2], $fServer_apps_id[3], $fServer_apps_id[4]);
							}
						}
					}

					include ("../templates/header.tpl");
					include ("../templates/databases/menu.tpl");
					include ("../templates/databases/add-databases.tpl");
					include ("../templates/footer.tpl");


			}

			
		}

 }

?>