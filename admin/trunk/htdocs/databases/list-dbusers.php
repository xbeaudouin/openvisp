<?php
//
// File: list-dbusers.php
//
// Template File: list-dbusers.tpl
//
// Template Variables:
//
// -none-
//
// Form POST \ GET Variables:
//
// username
// dbtype
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

  if ( check_mysql_database_admin($SESSID_USERNAME) )
		{

			if ($_SERVER['REQUEST_METHOD'] == "GET")
				{

					$fUsername = get_get('username');
					$list_domains = list_domains_for_admin ($fUsername);

					$mysql_admin = check_mysql_database_admin($fUsername);
					$postgresql_admin = check_postgresql_database_admin($fUsername);

          if ((is_array ($list_domains) and sizeof ($list_domains) > 0))
          {
              for ($i = 0; $i < sizeof ($list_domains); $i++)
              {
                $domain_properties[$i] = get_domain_properties ($list_domains[$i]);
								
								if ( $mysql_admin == "1" && $postgresql_admin == "1" ){
									$domain_dbusers[$i] = get_dbusers_list ($list_domains[$i],"all");
								}
								elseif ( $mysql_admin == "1" ){
									$domain_dbusers[$i] = get_dbusers_list ($list_domains[$i],"mysql");
								}
								elseif ( $postgresql_admin == "1" ){
									$domain_dbusers[$i] = get_dbusers_list ($list_domains[$i],"postgresql");
								}

              }
          }
					include ("../templates/header.tpl");
					include ("../templates/databases/menu.tpl");
					include ("../templates/databases/list-dbusers.tpl");
					include ("../templates/footer.tpl");
				}
			
		}

	print $PALANG['pDBNot_admin']."<br/>";

 }

?>