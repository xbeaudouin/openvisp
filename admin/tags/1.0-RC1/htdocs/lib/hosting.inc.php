<?php
//
// File: hosting.inc.php
//
error_reporting  (E_NOTICE | E_ERROR | E_WARNING | E_PARSE);

if (ereg ("hosting.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../login.php");
   exit;
}

//
// check_hosting_session
// Action: Check if an user session already exists. If not redirect to login.php
//         if it is not logged, otherwise logout the user.
// Call: check_hosting_session()
//
function check_hosting_session()
{
  $SESSID_USERNAME = check_session();
  if (!check_webhosting_admin($SESSID_USERNAME)) {
    header ("Location: ../logout.php");
    exit;
  }

  return $SESSID_USERNAME;
}

//
// check_webhosting_admin
// Action: check if a  big admin has access to the webhosting pages and if
//         the option is enabled.
// Call: check_webhosting_admin (string admin)
//
function check_webhosting_admin ($username)
{

   if ( check_webhosting()) {
      // TODO: add rights subsystem here
      $result = db_query ("SELECT rights.* FROM rights, accounts WHERE accounts.username='$username' AND accounts.id=rights.accounts_id");
      $row = db_array ($result['result']);
      $list['ftp'] = $row['ftp'];
      $list['http'] = $row['http'];
      if (($list['ftp'] == 1) || ($list['http'] == 1))
      {
        return true;
      }
      //return check_admin ($username);
   } else {
     return false;
   }
}


//
// list_domains_ftpaccount_available
// Action: list all domain for an admin that have not reach ftp quota
// Call: list_domains_ftpaccount_available (string admin)
//
function list_domains_ftpaccount_available ($username)
{

	$list = "";
	$domain_list = "";

	if(check_admin($username)) {
		$domain_list = list_domains();
		for ( $i = 0; $i < sizeof($domain_list); $i++ ){
			$result2 = db_query("SELECT (SELECT count(*) as total_ftpaccount  FROM ftpaccount, domain WHERE domain.domain='".$domain_list[$i]." AND domain.id=ftpaccount.domain_id') as ftp_count, ftp_account FROM domain WHERE domain.domain='".$domain_list[$i]."'");
			if ($result2['rows'] > 0){
				$row2 = db_array ($result2['result']);
				if ( $row2['ftp_count'] < $row2['ftp_account'] ){
					$list[] = $domain_list[$i];
				}
			}
		}

	} else {

		$username_id = get_account_id($username);

		$sql_query = "SELECT domain.domain, domain.ftp_account
FROM domain_admins, domain
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=domain.id
AND domain.active='1'
ORDER BY domain.domain";

		$result = db_query ($sql_query);

		if ($result['rows'] > 0)
			{
        $i = 0;
        while ($row = db_array ($result['result']))
					{
						$result2 = db_query("SELECT count(*) as total_ftpaccount FROM ftpaccount,domain WHERE domain.domain='".$row['domain']."' AND domain.id=ftpaccount.domain_id");
						if ($result2['rows'] > 0){
							$row2 = db_array ($result2['result']);
							if ( $row2['total_ftpaccount'] < $row['ftp_account'] ){
								$list[$i] = $row['domain'];
							}
						}
						$i++;
					}
			}
	}
	return $list;

}

//
// list_domains_mysqldb_available
// Action: list all domain for an admin that have not reach mysqldb quota
// Call: list_domains_mysqldb_available (string admin)
//
function list_domains_mysqldb_available ($username)
{

	$list = "";
	$domain_list = "";
	$dbtype="mysql";

	$username_id = get_account_id($username);

	if(!check_admin($username)) {
		$domain_list = list_domains();
		for ( $i = 0; $i < sizeof($domain_list); $i++ ){

			$sql_query = "SELECT (
SELECT count(*) as total_db
FROM dbname, dbtype
WHERE dbname.accounts_id='$username_id'
AND dbname.dbtype_id=dbtype.id
AND dbtype.name='".$dbtype."') AS total_db_count, db_count
FROM domain
WHERE domain.domain='".$domain_list[$i]."'";

			$result2 = db_query($sql_query);
			if ($result2['rows'] > 0){
				$row2 = db_array ($result2['result']);
				if ( $row2['total_db_count'] < $row2['db_count'] ){
					$list[] = $domain_list[$i];

					$domain_alias_list = list_alias_domain($domain_list[$i]);

					if ( $domain_alias_list['rows'] > 0 ){
						for ( $j = 0; $j < $domain_alias_list['rows']; $j++ ){
									$list[] = $domain_alias_list['data'][$j];
						}
					}


				}
			}
		}

	} else {

		$sql_query = "SELECT domain.id, domain.domain, domain.db_count 
FROM domain
WHERE domain.active='1'
ORDER BY domain.domain";

		$result = db_query ($sql_query);

		if ($result['rows'] > 0)
			{
        $i = 0;
        while ($row = db_array ($result['result']))
					{

						$sql_query = "SELECT COUNT(*) AS total_db
FROM dbname, dbtype
WHERE dbname.domain_id='".$row['id']."'
AND dbname.dbtype_id=dbtype.id
AND dbtype.name='".$dbtype."'";

						$result2 = db_query($sql_query);
						if ($result2['rows'] > 0){
							$row2 = db_array ($result2['result']);
							if ( $row2['total_db'] < $row['db_count'] ){
								$list[] = $row['domain'];

								$domain_alias_list = list_alias_domain($row['domain']);

								if ( $domain_alias_list['rows'] > 0 ){
									for ( $j = 0; $j < $domain_alias_list['rows']; $j++ ){
										$list[] = $domain_alias_list['data'][$j];
									}
								}


							}
						}
						$i++;
					}
			}
	}
	return $list;

}




//
// get_website_list
// Action: list all websites of a domain and get properties
// Call: get_website_list (string domain)
//
function get_website_list ($domain)
{
   $list = "";

   $result = db_query ("SELECT whost.*, domain.domain FROM whost,domain WHERE domain.domain='$domain' and domain.id=whost.domain_id ORDER BY vhost");
	 if ( $result['rows'] > 0 ){
		 while ($row = db_array($result['result'])) {
			 $list[] = $row;
		 }
		 return $list;
	 }
	 else{
		 return false;
	 }

}

//
// check_modelname_available
// Action: check if the modelname is available.
// Call: check_modelname_available (string modelname)
//
function check_modelname_available ($modelname)
{
   $list = "";

   $result = db_query ("SELECT * FROM server_jobmodel WHERE role='$modelname'");
	 if ( $result['rows'] > 0 ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}

//
// check_server_prvip_available
// Action: check if the private ip is available, only one private ip is possible for all the server
// Call: check_server_prvip_available (string ip, int server_id)
//
function check_server_prvip_available ($srv_ip, $server_id)
{
   $list = "";

   $result = db_query ("SELECT server_job.server_id FROM server_ip,server_job WHERE server_ip.private='$srv_ip' AND server_job.server_ip_id=server_ip.id");
	 $row = db_array($result['result']);

	 if ( $result['rows'] > 0 && $row['server_id'] != $server_id ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}

//
// check_server_pubip_available
// Action: check if the public ip is available, only one public ip is possible for all the server if there is no private ip
// Call: check_server_pubip_available (string pub_ip, string prv_ip, int server_id)
//
function check_server_pubip_available ($srv_ip, $prv_ip="", $server_id=0)
{
   $list = "";
	 if ( $prv_ip != "" ){
		 $result = db_query ("SELECT * FROM server_ip, server_job WHERE server_ip.public='$srv_ip' AND server_ip.private='$prv_ip' AND server_ip.server_ip_id=server_job.server_id");
	 }
	 else{
		 $result = db_query ("SELECT * FROM server_ip, server_job WHERE public='$srv_ip' AND server_ip.server_ip_id=server_job.server_id");
	 }

// 	 $row = db_array($result['result']);
// 	 if ( $result['rows'] > 0 ){
// 		 $ip_available = "0";
// 		 while ($row = db_array($result['result'])) {
// 			 if ( $row['pk_server_ip'] != $server_id ){
// 				 $ip_available = "1"
// 			 }
// 		 }
// 	 }

	 if ( $result['rows'] > 0 && $row['server_id'] != $server_id ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}



//
// check_servername_available
// Action: check if the servername is available.
// Call: check_servername_available (string servername, int server_id)
//
function check_servername_available ($servername, $server_id=0)
{
   $list = "";

   $result = db_query ("SELECT * FROM server WHERE name='$servername'");
	 $row = db_array($result['result']);

	 //	 file_put_contents('php://stderr', "DEBUG OVA ".$result['rows'].":-".$row['id']."-:-$server_id-:\n"); 

	 if ( $result['rows'] > 0 && $row['id'] != $server_id ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}

//
// check_fqdn_available
// Action: check if the fqdn is available.
// Call: check_fqdn_available (string fqdn, int server_id)
//
function check_fqdn_available ($fqdn, $server_id=0)
{
   $list = "";

   $result = db_query ("SELECT * FROM server WHERE public_name='$fqdn'");
	 $row = db_array($result['result']);

	 if ( $result['rows'] > 0 && $row['id'] != $server_id ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}


//
// add_model_server
// Action: add a new server model
// Call: add_model_server (string modelname, int active, string desc)
//
function add_model_server ($modelname, $active, $desc)
{
   $list = "";

	 $sql_query = "INSERT INTO server_jobmodel (role, description, active, created, modified) VALUES ('$modelname','$desc','$active',NOW(),NOW())";
	 $result = db_query ($sql_query);
	 if ( $result['rows'] >= 1 ){
		 return 1;
	 }
	 else{
		 return 0;
	 }

}


//
// delete_server_num
// Action: delete a server by his number id
// Call: delete_server_num (string servername, int active, string desc)
//
function delete_server_num ($server_id)
{
   $list = "";
	 $sql_query = "DELETE FROM server WHERE server_id ='$server_id'";
	 $result = db_query ($sql_query);
   if ($result['rows'] >= 1){
		 return false;
	 }
	 else{
		 return true;
	 }
}

//
// modify_server
// Action: modify a  server
// Call: modify_server (string servername, string server fqdn, int active, string desc, string private ip, string public ip, int role, int server_id)
//
function modify_server ($servername, $serverfqdn, $active, $desc, $fServer_prv_ip, $fServer_pub_ip, $fServer_role,$fServer_id)
{

	GLOBAL $PALANG;
	$message = "";

	$sql_query = "UPDATE server SET name='$serverfqdn', public_name='$serverfqdn', active='$active', description='$desc', modified=NOW() WHERE id='$fServer_id'";

	$result = db_query ($sql_query);
	if ( $result['rows'] == 1 ){
		$message .= $PALANG['pModify_Server_Info_OK']."<br/>";

		$sql_query = "UPDATE server_ip, server_job SET server_ip.public='$fServer_pub_ip', server_ip.private='$fServer_prv_ip', server_ip.modified=NOW() WHERE server_job.server_id='$fServer_id' AND server_job.server_ip_id=server_ip.id";
		$result = db_query ($sql_query);
		if ( $result['rows'] == 1 ){
			$message .= $PALANG['pModify_Server_IP_OK']."<br/>";

			$sql_query = "SELECT DISTINCT server_ip_id FROM server_job WHERE server_id='$fServer_id'";
			$result = db_query ($sql_query);
			$row = db_array($result['result']);
			$server_ip_id = $row['server_ip_id'];

			$sql_query = "DELETE FROM server_job WHERE server_ip_id='$fServer_id'";
			$result = db_query ($sql_query);

			$total_done = 0;
			$total_error = 0;
			foreach ($fServer_role as $role){

				$sql_query = "INSERT INTO server_job(server_id, server_jobmodel_id, server_apps_id, server_ip_id, created ) VALUES('$fServer_id','$role','0','$server_ip_id', NOW())";
				$result = db_query ($sql_query);
				if ( $result['rows'] == 1 ){ $total_done++; }
				else { $total_error++; }
			}

			if ( $total_error == 0 ){
				$message .= $PALANG['pModify_Server_Apps_OK']."<br/>";
			}
			else{
				$message .= $PALANG['pModify_Server_Apps_KO']."<br/>";
			}
		}
		else{
			$message .= $PALANG['pModify_Server_IP_KO']."<br/>";
		}

	}
	else{
		$message .= $PALANG['pModify_Server_Info_KO']."<br/>";
	}
	return $message;
}


//
// add_new_server
// Action: add a new server
// Call: add_new_server (string servername, string server fqdn, int active, string desc)
//
function add_new_server ($servername, $serverfqdn, $active, $desc)
{
   $list = "";

	 $sql_query = "INSERT INTO server (name, public_name, description, active, created, modified) VALUES ('$servername','$serverfqdn','$desc','$active',NOW(),NOW())";
	 $result = db_query ($sql_query);
	 if ( $result['rows'] >= 1 )
		 { return $result['inserted_id']; }
	 else
		 { return 0; }

}

//
// add_new_server_job
// Action: Add a new job to a server
// Call add_new_server_job (int server_id, int server_jobmodel_id, int server_ip_id, int server_apps_id, int active)
//
function add_new_server_job ($fServer_id, $fServer_model, $fServer_ip_id, $fServer_app_id, $active, $login, $pass, $port=0)
{
	$sql_query = "INSERT INTO server_job (server_id, server_jobmodel_id, server_ip_id, server_apps_id, login, password, port, active, created) VALUES ('$fServer_id','$fServer_model','$fServer_ip_id','$fServer_app_id', '$login', '$pass', '$port','$active',NOW())";
	$result = db_query ($sql_query);
	if ( $result['rows'] >= 1 )
		{	return 1;	}
	else
		{	return 0;	}
}

//
// add_new_ip
// Action: Add a new IP
// Call add_new_ip ( string prv_ip, string pub_ip)
//
function add_new_ip ($fServer_prv_ip="", $fServer_pub_ip="")
{

		 $sql_query = "INSERT INTO server_ip (public, private, created) VALUES ('$fServer_pub_ip','$fServer_prv_ip',NOW())";
		 $result = db_query ($sql_query);

		 if ( $result['rows'] >= 1 )
			 { return $result['inserted_id']; }
		 else
			 { return 0; }
}

//
// add_new_model
// Action: add a new server model
// Call: add_new_model (string modelname, int active, string desc)
//
function add_new_model ($modelname, $active, $desc)
{
   $list = "";

	 $sql_query = "INSERT INTO server_jobmodel (role, description, active, created, modified) VALUES ('$modelname','$desc','$active',NOW(),NOW())";
	 $result = db_query ($sql_query);
	 if ( $result['rows'] >= 1 ){
		 return 1;
	 }
	 else{
		 return 0;
	 }

}



//
// get_database_list
// Action: list databases of a domain and get their properties
// Call: get_database_list (string domain, string database_type)
//
function get_databases_list ($domain,$dbtype)
{
   $list = "";

	 if ( $dbtype == "all" ){

		 $sql_query = "SELECT dbname.*, dbtype.name as type, server.name as server_name
FROM dbname, dbtype, domain, server
WHERE domain.domain='$domain'
AND domain.id=dbname.domain_id
AND dbname.dbtype_id = dbtype.id
AND dbname.server_id = server.id
ORDER BY dbname";

		 $result = db_query ($sql_query);
	 }
	 else{

		 $sql_query = "SELECT dbname.*, dbtype.name as type, server.name as server_name
FROM dbname, dbtype, domain, server
WHERE domain.domain='$domain'
AND domain.id=dbname.domain_id
AND dbname.dbtype_id = dbtype.id
AND dbtype.name='$dbtype'
AND dbname.server_id = server.id
ORDER BY dbname";

		 $result = db_query ($sql_query);
	 }
	 if ( $result['rows'] > 0 ){
		 while ($row = db_array($result['result'])) {
			 $list[] = $row;
		 }
		 return $list;
	 }
	 else{
		 return false;
	 }

}

//
// list_dbuser_access
// Action: list all database an account have an access
// Call: list_dbuser_access (string username)
//
function list_dbuser_access($username,$model_id, $server_id)
{

	$db_info = get_db_param(get_app_id('mysql'), $model_id, $server_id);

	$list = "";
	$sql_query = ("SELECT Db from db where User='$username'");
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);
	if ( $result['rows'] > 0 ){
		while ($row = db_array($result['result'])) {
			$list .= $row['Db']."<br/>";
		}
	}
	return $list;
}

//
// get_dbusers_list
// Action: list databases of a domain and get their properties
// Call: get_mysql_database_list (string domain, string database_type)
//
function get_dbusers_list ($domain,$dbtype)
{
   $list = "";

	 if ( $dbtype == "all" ){
		 $sql_query = "SELECT dbusers.*, dbtype.name as type, dbtype.id as type_id, server.name as server_name, server.id as server_id
FROM dbusers, domain, dbtype, server
WHERE domain.domain='$domain'
AND domain.id=dbusers.domain_id
AND dbusers.dbtype_id = dbtype.id
AND dbusers.server_id = server.id
ORDER BY username";

		 $result = db_query ($sql_query);
	 }
	 else{
		 $sql_query = "SELECT dbusers.*, dbtype.name as type, server.name as server_name
FROM dbusers, dbtype, domain, server
WHERE domain.domain='$domain'
AND domain.id=dbusers.domain_id
AND dbname.dbtype_id=dbtype.id
AND dbtype.name='$dbtype'
AND dbusers.server_id = server.id
ORDER BY username";

		 $result = db_query ($sql_query);
	 }
	 if ( $result['rows'] > 0 ){
		 while ($row = db_array($result['result'])) {
			 $list[] = $row;
		 }
		 return $list;
	 }
	 else{
		 return false;
	 }

}


//
// check_db_domain_available
// Action: return number of databases for a domain that can be created
// Call: check_db_domain_available (string domain)
//
function check_db_domain_available ($domain)
{
   $list = "";

	 $sql_query = "SELECT
 (
  SELECT COUNT(DISTINCT dbname) as total_db
  FROM dbname, domain, domain_alias
  WHERE domain.id=dbname.domain_id
  AND domain.id=domain_alias.domain_id
  AND
   ( domain.domain='".$domain."'
    OR domain_alias.dalias='".$domain."'
   )
 ) AS total_db_count, db_count
FROM domain, domain_alias
WHERE domain.domain='".$domain."'
OR
  ( domain.id=domain_alias.domain_id
    AND domain_alias.dalias='".$domain."'
  )
";

		 $result = db_query ($sql_query);

		 
		 if ( $result['rows'] > 0 ){
			 $row = db_array($result['result']);
			 return $row['db_count'] - $row['total_db_count'];
		 }
		 else{
			 return 0;
		 }

}

//
// check_dbuser_domain_available
// Action: return number of database user for a domain that can be created
// Call: check_dbuser_domain_available (string domain)
//
function check_dbuser_domain_available ($domain)
{
   $list = "";

		 $result = db_query ("SELECT (SELECT count(*) as total_dbuser  FROM dbusers,domain WHERE domain.domain='".$domain."' AND domain.id=dbusers.domain_id) as total_dbuser_count, db_users FROM domain WHERE domain.domain='".$domain."'");

		 
		 if ( $result['rows'] > 0 ){
			 $row = db_array($result['result']);
			 return $row['db_users'] - $row['total_dbuser_count'];
		 }
		 else{
			 return 0;
		 }

}


//
// get_mysql_database_size
// Action: get the size of a mysql db
// Call: get_mysql_database_size (string databasename)
//
function get_mysql_database_size ($database)
{

	return 0;

}


//
// get_website_alias_list
// Action: list all vhosts of a domain and get properties
// Call: get_website_alias_list (string domain)
//
function get_website_alias_list ($domain,$vhost)
{
   $website = "";
   $vhosts  = "";

   $result = db_query ("SELECT whost.id FROM whost,domain WHERE domain.domain='$domain' AND domain.id=whost.domain_id AND whost.vhost='$vhost'");
   if ( $result['rows'] > 0 ){
      $row = db_array($result['result']);
      $website = $row['id'];
//       $result = db_query ("SELECT * FROM whost WHERE isalias='$website' ORDER BY vhost");
//       if ( $result['rows'] > 0) {
// 				while ($row = db_array($result['result'])) {
// 					$vhosts[] = $row;
// 				}
// 				return $vhosts;
//       }
//       else {
// 				return false;
//       }
   } 
   else { 
      return false; 
   }
}

//
// get_site_info
// Action: get information for a site
// Call: get_site_info (string domain, string vhost)
//
function get_site_info ($domain, $vhost)
{
   $list = "";

   $result = db_query ("SELECT * FROM whost,domain WHERE domain.domain='$domain' AND domain.id=whost.domain_id AND whost.vhost='$vhost' ");
   $row = db_array ($result['result']);

	 if (is_array($row) or is_object($row)){
		 reset($row);
		 while (list($key, $value) = each($row)) {
			 $list[$key] = $value;
		 }
	 }
	 return $list;

}

//
// update_site_info
// Action: update information for a site
// Call: update_site_info (string domain, string vhost, string options)
//
function update_site_info ($domain, $vhost, $voptions)
{
	$domain_id = get_domain_id($domain);
	 $result = db_query ("UPDATE whost SET SetEnv='$voptions', modified=NOW() WHERE domain_id='$domain_id' AND vhost='$vhost'");
	 return ($result['rows']);
}




//
// get_website_list_owner
// Action: list all websites of a user
// Call: get_website_list_owner (string username)
//
function get_website_list_owner($username)
{
   $list = "";
   
		$username_id = get_account_id($username);

		$sql_query = "SELECT whost.vhost,whost.domain
FROM whost, domain_admins
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=whost.domain_id
ORDER BY whost.vhost";


   $result = db_query ($sql_query);
   if ($result['result'] > 0)
   {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
        $list[$i]['vhost']  = $row['vhost'];
        $list[$i]['domain'] = $row['domain'];
        $i++;
      }
   }
   return $list;
}

//
// check_ftp_account_exist
// Action: check if a ftp account already exist
// Call: check_ftp_account_exist (string account, string domain)
//
function check_ftp_account_exist ($account, $domain){
	//	print "NG : SELECT * FROM ftpaccount WHERE login='$account'";
	$result = db_query ("SELECT * FROM ftpaccount WHERE login='$account.$domain'");
	if ($result['rows'] > 0){
		return 1;
	}
	else{
		return 0;
	}
}


//
// get_ftpaccount_list
// Action: list all FTP accounts of a domain and get properties
// Call: get_ftpaccount_list (string domain)
//
function get_ftpaccount_list ($domain)
{
   $list = "";

   $result = db_query ("SELECT COUNT(*) FROM ftpaccount,domain WHERE domain.domain='$domain' AND domain.id=ftpaccount.domain_id");
   $row = db_row ($result['result']);
   $list['counter'] = $row[0];

   $result = db_query ("SELECT * FROM ftpaccount,domain WHERE domain.domain='$domain' AND domain.id=ftpaccount.domain_id ORDER BY login");
   if ($result['result'] > 0)
   {
     $i = 0;
     while ($row = db_array ($result['result']))
     {
			 $list[$i] = $row;
/*        $list[$i]['login']        = $row['login']; */
/*        $list[$i]['password']     = $row['password']; */
/*        $list[$i]['domain']       = $row['domain']; */
/*        $list[$i]['dir']          = $row['dir']; */
/*        $list[$i]['quotafs']      = $row['quotafs']; */
/*        $list[$i]['quotasz']      = $row['quotasz']; */
/*        $list[$i]['ratioul']      = $row['ratioul']; */
/*        $list[$i]['ratiodl']      = $row['ratiodl']; */
/*        $list[$i]['bandwidthul']  = $row['bandwidthul']; */
/*        $list[$i]['bandwidthdl']  = $row['bandwidthdl']; */
/*        $list[$i]['active']       = $row['active']; */
/*        $list[$i]['paid']       = $row['paid']; */

			 $result2 = db_query("SELECT sum(size) as size FROM stats_ftpaccount WHERE ftpaccount_id = '".$list[$i]['id']."' GROUP BY date ORDER BY date DESC LIMIT 1");
			 if ($result2['rows'] == 1){
				 $row = db_array ($result2['result']);
				 $list[$i]['disk_used'] = $row['size'];
			 }
			 else{
				 $list[$i]['disk_used'] = 0;
			 }

			 $now = time();
			 $fMonth = date("m",$now);
			 $fYear = date("Y",$now);

			 $result2 = db_query("SELECT sum(upload) as size_ul, sum(download) as size_dl FROM stats_ftptransfert WHERE ftpaccount_id = '".$list[$i]['id']."' AND date like '$fYear-$fMonth-%'");
			 if ($result2['rows'] == 1){
				 $row = db_array ($result2['result']);
				 $list[$i]['ftp_transfert_ul'] = $row['size_ul'];
				 $list[$i]['ftp_transfert_dl'] = $row['size_dl'];
			 }
			 else{
				 $list[$i]['ftp_transfert_ul'] = 0;
				 $list[$i]['ftp_transfert_dl'] = 0;
			 }


       $i++;
		 }
   }
   return $list;
}

// list_ftp_month
// Action: get all traffic for an ftp account on month
// Call: list_ftp_month(string ftplogin, int month, int year)
function list_ftp_month($ftplogin, $fMonth, $fYear){

	$result = db_query("SELECT download, upload, date FROM ftptransfert_stat WHERE ftpaccount = '".$ftplogin."' AND date like '$fYear-$fMonth-%'");

	if ($result['rows'] > 0)
		{
			while ($row = db_array ($result['result']))
        {
					$date1 = explode("-",$row['date']);
					$date = explode(" ",$date1[2]);
					$row['date'] = $date[0];
					$list[] = $row;
        }
		}

	return $list;

}

// get_list_date_ftp
// Action: get all available date for transfert for an account
// Call: get_list_date_ftp(string ftplogin)
function get_list_date_ftp($ftplogin){
	$list = "";
	$result = db_query("SELECT DISTINCT CONCAT(YEAR(date),'-',MONTH(date)) AS date FROM ftptransfert_stat WHERE ftpaccount = '".$ftplogin."' ");

	if ($result['rows'] > 0)
		{
			while ($row = db_array ($result['result']))
        {
					$full_date = explode('-', $row['date']);
					if ( $full_date[1] < 10 ){
						$row['date'] = $full_date[0].'-0'.$full_date[1];
					}
					$list[] = $row;
        }
		}

	return $list;

}

// mysql_create_db
// Action: Create a new database
// Call: mysql_create_db(string database_name, string domain)
//
function mysql_create_db ($database_name, $domain, $server_id, $apps_id, $model_id, $port, $ip_id){

	GLOBAL $SESSID_USERNAME;
	$domain_alias_id = get_alias_domain_id($domain);
	if ( $domain_alias_id['id'] > 0 )
		{	$domain_id = $domain_alias_id['domain_id'];	}
	else
		{	$domain_id = get_domain_id($domain); }


	$db_type_id = get_dbtype_id('mysql');
	$account_id = get_account_id($SESSID_USERNAME);


	$db_info = get_db_param($apps_id, $model_id, $server_id,$port);
	$sql_query = "CREATE DATABASE $database_name;";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);
	$sql_query = "INSERT INTO dbname(domain_id,accounts_id,server_id,dbtype_id,dbname,created,dalias_id,port,server_ip_id) VALUES ('$domain_id','$account_id','$server_id','$db_type_id','$database_name',NOW(),'".$domain_alias_id['id']."','".$port."','".$ip_id."')";
	$result = db_query ($sql_query);
	$sql_query = "GRANT ALL PRIVILEGES ON $database_name.* TO '".$db_info['login']."'@'%' WITH GRANT OPTION ";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);
	$sql_query = "FLUSH PRIVILEGES";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);

}

//
// mysql_create_db_and_user
// Action: prepare to create new database for a domain and associated users
// Call: mysql_create_db_and_user(string domain, int number_to_create, int server_id, int apps_id, int model_id, int port, int ip_id)
//
function mysql_create_db_and_user ($domain, $number, $server_id, $apps_id, $model_id, $port, $ip_id){

	for ( $i=1; $i <= $number; $i++){
		$db_name=generate_dbname($domain);
		$dbuser_name=generate_dbusername($domain);

		mysql_create_db($db_name,$domain, $server_id, $apps_id, $model_id, $port, $ip_id);
		mysql_create_user($dbuser_name,$domain, $server_id, $apps_id, $model_id, $port, $ip_id);
		mysql_associate_user_db($dbuser_name,$db_name, $server_id, $apps_id, $model_id, $port );
	}
	
}


//
// mysql_create_users
// Action: prepare to create new mysql users for a domain
// Call: mysql_create_users(string domain, int number_to_create, int server_id, int apps_id, int model_id, int port, int ip_id)
//
function mysql_create_users ($domain, $number, $server_id, $apps_id, $model_id, $port, $ip_id){

	for ( $i=1; $i <= $number; $i++){
		$dbuser_name=generate_dbusername($domain);
		mysql_create_user($dbuser_name,$domain,$server_id, $apps_id, $model_id, $port, $ip_id);
	}
	
}


//
// mysql_create_user
// Action: create a new user in mysql database
// Call: mysql_create_user(string dbusername, string domain, int server_id, int apps_id, int model_id, int port, int ip_id)
//
function mysql_create_user($dbuser_name, $domain, $server_id, $apps_id, $model_id, $port, $ip_id){
	GLOBAL $CONF;

	$domain_alias_id = get_alias_domain_id($domain);
	if ( $domain_alias_id['id'] > 0 )
		{	$domain_id = $domain_alias_id['domain_id'];	}
	else
		{	$domain_id = get_domain_id($domain); }

	$dbtype_id = get_dbtype_id('mysql');

	if ($CONF['password_generator'] == ""){
		$dbuser_pass = generate_password ();
	}
	else {
		$dbuser_pass = exec($CONF['password_generator']);
	}

	GLOBAL $SESSID_USERNAME;
	$account_id = get_account_id($SESSID_USERNAME);

	$db_info = get_db_param($apps_id, $model_id, $server_id, $port);

	$sql_query = "CREATE USER '$dbuser_name'@'%' IDENTIFIED BY '$dbuser_pass'";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);

	$sql_query = "INSERT INTO dbusers(domain_id,accounts_id,server_id,dbtype_id,username,password,created,dalias_id,port, server_ip_id) VALUES ('$domain_id','$account_id','$server_id','$dbtype_id','$dbuser_name','$dbuser_pass',NOW(),'".$domain_alias_id['id']."','".$port."','".$ip_id."')";
	$result = db_query ($sql_query);

	$sql_query = "FLUSH PRIVILEGES";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);


}

//
// mysql_associate_user_db
// Action: allow a mysqluser to access a DB
// Call: mysql_associate_user_db(string dbuser_name, string db_name, int port)
//
function mysql_associate_user_db($dbuser_name, $db_name, $server_id, $apps_id, $model_id, $port){

	$db_info = get_db_param($apps_id, $model_id, $server_id, $port);

	$sql_query = "GRANT ALL PRIVILEGES ON $db_name.* TO '$dbuser_name'@'%' WITH GRANT OPTION";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);

	$sql_query = "FLUSH PRIVILEGES";
	$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);

// 	if ( $result['rows'] > 0 ){
// 		return 1;
// 	}
// 	return 0;
}



//
// mysql_create_onlydb
// Action: prepare to create new databases for a domain
// Call: mysql_create_onlydb(string domain, int number_to_create)
//
function mysql_create_onlydb ($domain, $number, $server_id, $apps_id, $model_id){

	for ( $i=1; $i <= $number; $i++){
		$db_name=generate_dbname($domain);
		mysql_create_db($db_name,$domain, $server_id, $apps_id, $model_id);
	}
	
}



// generate_dbusername
// Action: generate from a domain name a dbusername
// Call: generate_dbname(sring domain)
//
function generate_dbusername ($domain){

	$unwanted_char = array("-");
	// Fetch the length of the domain
	$domain2 = 	str_replace($unwanted_char,"",$domain);
	$length = strlen($domain2);
	// Fetch the position of the last .
	$dot_pos = strrpos($domain2, ".");

	if ( $length > 11 )
		{
			$pos1 = 4;
			$pos2 = 4;
			if ( ($length - $dot_pos) < 4 ){
				$pos1++;
			}
			$domain2 = substr($domain2, 0, $pos1).substr($domain2, $dot_pos-$pos2, $length);
		}
	$dbuser_name="us".substr($domain2, $dot_pos+1,$length).str_replace($unwanted_char,"",substr($domain2, 0, $dot_pos));


	// Fetch the last database username like usextdomain???
	$sql_query = "SELECT MAX(MID(dbusers.username,LENGTH(dbusers.username)-2,3)) as counter
FROM dbusers
WHERE dbusers.username like '$dbuser_name%'";


	$result = db_query ($sql_query);

	if ($result['rows'] > 0)
		{
			$row = db_array ($result['result']);
			$compteur = $row['counter'];
			$compteur++;
		}
	else
		{
			$compteur = "0";
		}

	$string ="%03s";

	return $dbuser_name.sprintf( $string, $compteur);

}


// generate_dbname
// Action: generate from a domain name a dbname
// Call: generate_dbname(sring domain)
//
function generate_dbname ($domain){

	$unwanted_char = array("-");
	// Fetch the length of the domain
	$domain2 = 	str_replace($unwanted_char,"",$domain);
	$length = strlen($domain2);
	// Fetch the position of the last .
	$dot_pos = strrpos($domain2, ".");

	if ( $length > 55 ){
		$domain2 = substr($domain2, 0, 29).substr($domain2, $length-24, $length);
	}
	$db_name="db".substr($domain2, $dot_pos+1,$length).str_replace($unwanted_char,"",substr($domain2, 0, $dot_pos));



	// Fetch the last database name like dbextdomain???
	$sql_query = "SELECT MAX(MID(dbname.dbname,LENGTH(dbname.dbname)-2,3)) as counter
FROM dbname
WHERE dbname.dbname like '$db_name%'";


	$result = db_query ($sql_query);

	if ($result['rows'] > 0)
		{
			$row = db_array ($result['result']);
			$compteur = $row['counter'];
			$compteur++;
		}
	else
		{
			$compteur = "0";
		}

	$string ="%03s";

	return $db_name.sprintf( $string, $compteur);


}



//
// list_web_domains_for_admin
// Action: Lists all the domains for an admin with a virtual defined.
// Call: list_web_domains_for_admin (string admin)
//
function list_web_domains_for_admin ($username)
{
   $list = "";
   if(check_admin($username)) {
     $list = list_web_domains_for_sadmin();
   } else {
     //$result = db_query ("SELECT domain.domain FROM domain LEFT JOIN domain_admins ON domain.domain=domain_admins.domain WHERE domain_admins.username='$username' AND domain.active='1' ORDER BY domain_admins.domain");

		 $sql_query = "SELECT DISTINCT(whost.domain)
FROM whost, domain_admins
WHERE domain_admins.username='$username'
AND domain_admins.active='1'
AND domain_admins.domain=whost.domain
ORDER BY domain";

		 $result = db_query ($sql_query);
     if ($result['rows'] > 0)
     {
        $i = 0;
        while ($row = db_array ($result['result']))
        {
           $list[$i] = $row['domain'];
           $i++;
        }
     }
   }
   return $list;
}

//
// list_web_domains_for_sadmin
// Action: Lists all the domains for an sadmin with a virtual defined.
// Call: list_web_domains_for_sadmin ()
//
function list_web_domains_for_sadmin ()
{
   $list = "";
	 $result = db_query ("SELECT DISTINCT(domain) FROM whost ORDER BY domain");
	 if ($result['rows'] > 0)
     {
			 $i = 0;
			 while ($row = db_array ($result['result']))
				 {
           $list[$i] = $row['domain'];
           $i++;
				 }
     }
   return $list;
}

//
// get_dir_hash
// Action: Used to build DocumentRoot of a domain/vhost.
// Call: get_dir_hash($domain)
//
function get_dir_hash($domain)
{
   global $CONF;

   $directory = explode('.',$domain);
   if (strlen($directory[0]) > 2)
   {
       $dir = $directory[0]{0} . "/" . $directory[0]{1} . "/" . $directory[0]{2};
   }
   if (strlen($directory[0]) == 2)
   {
       $dir = $directory[0]{0} . "/" . $directory[0]{1};
   }
   if (strlen($directory[0]) == 1)
   {
       $dir = $directory[0]{0};
   }

   return $dir;
}

//
// modify_domain_ftp
// Action: Used to block / unblock all ftp account on a domain
// Call: modify_domain_ftp(int domain_id, int action)
//
function modify_domain_ftp($domain_id, $action)
{
	GLOBAL $PALANG;
	$result = db_query ("UPDATE ftpaccount SET paid='$action',modified=NOW() WHERE domain_id='$domain_id'");

	if ($result['rows'] >= 1){
		if ( $action == 0 ){
			return $PALANG['lock_domain_ftp_account_successfull'];
		}
		else{
			return $PALANG['unlock_domain_ftp_account_successfull'];
		}
	}
	else{
		if ( $action == 0 ){
			return $PALANG['lock_domain_ftp_account_unsuccessfull'];
		}
		else{
			return $PALANG['unlock_domain_ftp_account_unsuccessfull'];
		}
	}

}

//
// list_server_model
// Action: list all server model in the database
// Call: list_server_model
//
function list_server_model(){

		$list = "";

		$query = "
SELECT *
FROM server_jobmodel
WHERE active=1
ORDER BY role
";

		$result = db_query ($query);
		if ($result['rows'] > 0)
			{
				while ( $row = db_array ($result['result']) ){
					$list[] = $row;
				}
				
			}
		return $list;

}

//
// server_info
// Action: Get server info
// Call: server_info(int server_num)
//
function server_info($server_id){

	$list="";

	$query = "
SELECT server.*, server_ip.public, server_ip.private, server_ip.comment
FROM server, server_job, server_jobmodel, server_ip
WHERE server.id=server_job.server_id
AND server_job.server_ip_id=server_ip.id
AND server.id = '$server_id'
";

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			$row = db_array ($result['result']);
			$list['name'] = $row['name'];
			$list['id'] = $row['id'];
			$list['public_name'] = $row['public_name'];
			$list['description'] = $row['description'];
			$list['ip'] = '<div id="public_ip">'.$row['public']."</div><br/>".$row['private']."<br/>";
			$list['public'] = $row['public'];
			$list['private'] = $row['private'];
			$list['comment'] = $row['comment'];
			$list['active'] = $row['active'];
			if ( $row['active'] == "1" ) { $list['active'] = "checked"; } else { $list['active'] = "unchecked"; }
		}

	return $list;

}

//
// server_role
// Action: Get server role
// Call: server_role(int server_num)
//
function server_role($server_id){

	$list="";

	$query = "
SELECT server_jobmodel.*
FROM  server_job, server_jobmodel
WHERE server_job.server_id = '$server_id'
AND server_job.server_jobmodel_id=server_jobmodel.id
";


	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ($row = db_array ($result['result']))
				{
					$list[] = $row;
				}
		}

	return $list;

}



//
// list_server
// Action: list all server in the database
// Call: list_server
//
function list_server(){

// 	$query = "
// SELECT server.*, server_ip.*, ( SELECT CONCAT(server_jobmodel.role) FROM server_jobmodel WHERE server.id=server_job.fk_server_id
// AND server_job.fk_server_jobmodel_id=server_jobmodel.id )

// FROM server, server_job, server_jobmodel, server_ip
// WHERE server.id=server_ip.fk_server_id
// ";

	$query = "
SELECT server.*, server_ip.public, server_ip.private, server_ip.comment, server_jobmodel.role
FROM server
LEFT OUTER JOIN server_job ON server.id=server_job.server_id
LEFT OUTER JOIN server_jobmodel ON server_jobmodel.id=server_job.server_jobmodel_id
LEFT OUTER JOIN server_ip ON server_job.server_ip_id=server_ip.id 

";

	$list="";

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			$i = 0;
			$j = 0;
			while ($row = db_array ($result['result']))
				{
					if ( $i == 0 ){
						$list[$i]['name'] = $row['name'];
						$list[$i]['id'] = $row['id'];
						$list[$i]['desc'] = $row['description'];
						$list[$i]['ip'] = '<div id="public_ip">'.$row['public']."</div><br/>".$row['private']."<br/>";
						$list[$i]['public'] = $row['public'];
						$list[$i]['private'] = $row['private'];
						$list[$i]['comment'] = $row['comment'];
						$list[$i]['role'] = $row['role'];
						$list[$i]['active'] = $row['active'];
						$i++;

					}
					else{
						if ( $list[$j]['name'] == $row['name'] ){
							$list[$j]['ip'] .= '<div id="public_ip">'.$row['public']."</div><br/>".$row['private']."<br/>";


							// Here we keep only unique Public and Private IP address
							if ( ! preg_match("/".$list[$j]['public']."/", $row['public']) ){
								$list[$j]['public'] .= " ".$row['public'];								
							}

							if ( ! preg_match("/".$list[$j]['private']."/", $row['private']) ){
								$list[$j]['private'] .= " ".$row['private'];
							}

							$list[$j]['comment'] .= "\n".$row['comment'];
							$list[$j]['role'] .= "<br/>\n<br/>\n".$row['role'];
							//							$list[$j]['active'] .= "\n".$row['active'];

						}
						else{
							$list[$i]['name'] = $row['name'];
							$list[$i]['id'] = $row['id'];
							$list[$i]['desc'] = $row['description'];
							$list[$i]['ip'] = '<div id="public_ip">'.$row['public']."</div><br/>".$row['private']."<br/>";
							$list[$i]['public'] = $row['public'];
							$list[$i]['private'] = $row['private'];
							$list[$i]['comment'] = $row['comment'];
							$list[$i]['role'] = $row['role'];
							$list[$i]['active'] = $row['active'];
							$j = $i;
							$i++;

						}
					}



				}
		}
	return $list;


}

//
// list_server_job
// Action: list jobs for a server
// Call: list_server_job (int server_id)
//
function list_server_job($fServer_id){


	$query = "
SELECT server_jobmodel.id, server_jobmodel.role
FROM server_jobmodel, server_job
WHERE server_job.server_id='$fServer_id'
AND server_job.server_jobmodel_id=server_jobmodel.id
AND server_jobmodel.active='1'
";

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}

		}
	return $list;

}

//
// list_app_job
// Action: List application available for a job
// Call: list_app_job (int id_job)
//
function list_app_job($job_id){

	$list = "";

	$query = "
SELECT *
FROM server_apps
WHERE server_apps.server_jobmodel_id = '$job_id'
AND active='1'
";

	//	file_put_contents('php://stderr', "DEBUG OVA $query \n"); 

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}

		}
	return $list;

}

//
// list_server_job_app
// Action: List application used by a server and a defined job
// Call: list_server_job_app (int fServer_id, int job_id)
//
function list_server_job_app($fServer_id, $job_id){


	// Marche pas a controler

	$query = "QWEQWEQWEW
SELECT server_jobmodel.id, server_jobmodel.role
FROM server_jobmodel, server_job
WHERE server_job.server_id='$fServer_id'
AND server_job.server_jobmodel_id=server_jobmodel.id
AND server_jobmodel.active='1'
AND server_apps.server_jobmodel_id=server_jobmodel.id
AND server_apps.active='1'
";

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}

		}
	return $list;

}

//
// add_new_app
// Action: add a new application
// Call: add_new_app (string application, int active, string desc, int jobmodel)
//
function add_new_app ($application, $active, $version, $desc, $jobmodel)
{

	 $sql_query = "INSERT INTO server_apps (apps, description, version, server_jobmodel_id, active, created) VALUES ('$application','$desc','$version','$jobmodel','$active',NOW())";
	 $result = db_query ($sql_query);
	 if ( $result['rows'] >= 1 ){
		 return 1;
	 }
	 else{
		 return 0;
	 }

}

//
// check_appname_available
// Action: check if the appname is available.
// Call: check_appname_available (string appname, string version)
//
function check_appname_available ($application, $version)
{

   $result = db_query ("SELECT * FROM server_apps WHERE apps='$application' AND version='$version'");
	 if ( $result['rows'] > 0 ){
		 return 0;
	 }
	 else{
		 return 1;
	 }

}

//
// policyd_server_exist
// Action: check if at last 1 policyd server exist
// Call policyd_server_exist
//
function policyd_server_exist ()
{
	$result = db_query ("SELECT count(*) as nb_policy FROM server_apps, server_jobmodel WHERE server_jobmodel.role='Policyd' AND server_jobmodel.id=server_apps.server_jobmodel_id");
	if ( $result['rows'] > 0 ){
		$row = db_array ($result['result']);
		return $row['nb_policy'];
	}
	else{
		 return 0;
	}
	
}


//
// list_apps
// Action: List all application created
// Call: list_apps ()
//
function list_apps(){

	$list = "";

	$query = "
SELECT server_jobmodel.role, server_apps.apps, server_apps.version
FROM server_apps, server_jobmodel
WHERE server_apps.server_jobmodel_id=server_jobmodel.id
ORDER BY server_apps.apps
";

	//	file_put_contents('php://stderr', "DEBUG OVA $query \n"); 

	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}

		}
	return $list;

}

//
// list_apps
// Action: List all application created
// Call: list_apps ( int server)
//
function list_server_apps($server_id){

	$list = "";

	$query = "
SELECT server_jobmodel.role, server_apps.apps, server_apps.version, server_job.login, server_job.password, server_job.active, server_job.port, server_job.server_jobmodel_id, server_job.server_apps_id, server_ip_id, server_ip.public, server_ip.private, server_ip.hostname
FROM server_apps, server_jobmodel, server_job, server_ip
WHERE server_job.server_id='$server_id'
AND server_job.server_jobmodel_id=server_jobmodel.id
AND server_job.server_apps_id=server_apps.id
AND server_job.server_ip_id=server_ip.id
ORDER BY server_apps.apps
";


	$result = db_query ($query);
	if ($result['rows'] > 0)
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}

		}
	return $list;

}

//
// add_app_server
// Action: Add a new application to a server
// Call: add_app_server ( int server, int app, int jobmodel, int ip, string login, string password, int port)
//
function add_app_server($server_id, $app_id, $jobmodel_id, $ip_id, $login="", $password="", $port="" ){

	$sql_query = "INSERT INTO server_job(server_id, server_jobmodel_id, server_apps_id, server_ip_id, login, password, port, created, active) VALUES('$server_id','$jobmodel_id','$app_id',$ip_id, '$login', '$password', '$port', NOW(),'1')";

	//	file_put_contents('php://stderr', "DEBUG OVA $query \n"); 

	$result = db_query ($sql_query);
	if ($result['rows'] >= 1)
		{
			return TRUE;
		}
	else
		{
			return FALSE;
		}

}


//
// modify_app_server
// Action: Modify an application linked to a server
// Call: modify_app_server (int server_id, int server_ip_id, int server_app_id, int server_jobmodel_id, int port, string upfield, string newvalue)
//
function modify_app_server ($server_id, $server_ip_id, $server_app_id, $server_jobmodel_id, $port, $upfield, $newvalue )
{

		$sql_query = "UPDATE server_job SET $upfield='$newvalue' WHERE server_ip_id='$server_ip_id' AND server_id='$server_id' AND server_apps_id='$server_app_id' AND server_jobmodel_id='$server_jobmodel_id' AND port='".$port."'";
		$result = db_query ($sql_query);
		if ( $result['rows'] == 1 ){
			return "OK";
		}
		else{
			return "NOK";
		}
}


//
// delete_app_server
// Action: delete application linked to a server
// Call: modify_app_server (int server_id, int server_ip_id, int server_app_id, int server_jobmodel_id, int port)
//
function delete_app_server ($server_id, $server_ip_id, $server_app_id, $server_jobmodel_id, $port)
{

	$sql_query = "DELETE FROM server_job WHERE server_ip_id='$server_ip_id' AND server_id='$server_id' AND server_apps_id='$server_app_id' AND server_jobmodel_id='$server_jobmodel_id' AND port='".$port."'";
	$result = db_query ($sql_query);
	if ($result['rows'] >= 1){
		return "OK";
	}
	else{
		return "KO";
	}

}

//
// add_ip_ifnot_exist
// Action: look for an ip in the database, if exist return the id, if not insert it and return id.
// Call: add_ip_ifnot_exist (string pub_ip, string priv_ip)
//
function add_ip_ifnot_exist ($pub_ip, $priv_ip)
{

	$ip_id = get_ip_id($pub_ip, $priv_ip);
	if ( $ip_id != FALSE )
		{
			return $ip_id;
		}
	else
		{
			return add_new_ip ($priv_ip, $pub_ip);
		}

}

//
// get_ip_id
// Action: fetch the ip id
// Call: get_ip_id (string pub_ip, string priv_ip)
//
function get_ip_id ($pub_ip="", $priv_ip="")
{
	$sql_query = "SELECT id FROM server_ip WHERE public='$pub_ip' AND private='$priv_ip'";
	$result = db_query ($sql_query);
	$row = db_array ($result['result']);
	if ( $result['rows'] > 0 )
		{
			return $row['id'];
		}
	else
		{
			return FALSE;
		}

}

//
// get_list_database_server
// Action: get the whole list for database server
// Call: get_list_database_server(string db_type)
//
function get_list_database_server($db_type="mysql")
{

		$list = "";

		$query = "
SELECT server.id, server.name, server_apps.id as apps_id, server_apps.apps, server_apps.version, server_job.server_jobmodel_id as model_id, server_job.port, server_job.server_ip_id
FROM server_apps, server_job, server
WHERE server_apps.apps = '$db_type'
AND server_apps.active = 1
AND server_apps.id = server_job.server_apps_id
AND server_job.server_id = server.id
ORDER BY server.name
";

		$result = db_query ($query);
		if ($result['rows'] > 0)
			{
				while ( $row = db_array ($result['result']) ){
					$list[] = $row;
				}
				
			}
		return $list;

}

//
// get_db_param
// Action: get the parameters to connect to mysql server
// Call: get_db_param (int apps_id, int jobmodel_id, int server_id, int port)

function get_db_param($app_id, $jobmodel_id, $server_id, $port)
{

		$list = "";

		$query = "
SELECT server_job.login, server_job.password, server_job.port, server_ip.public, server_ip.private
FROM server_job, server_ip
WHERE server_job.server_ip_id = server_ip.id
AND server_job.server_jobmodel_id='$jobmodel_id'
AND server_job.server_apps_id='$app_id'
AND server_job.server_id='$server_id'
AND server_job.port='$port'
";

		$result = db_query ($query);
		if ($result['rows'] > 0)
			{
				$row = db_array ($result['result']) ;
				$list = $row;				
			}
		return $list;

}

//
// get_dbtype_id
// Action: fetch the db_type id
// Call: get_dbtype_id (string dbtype)
//
function get_dbtype_id ($db_type)
{
	$sql_query = "SELECT id FROM dbtype WHERE name='$db_type'";
	$result = db_query ($sql_query);
	$row = db_array ($result['result']);
	if ( $result['rows'] > 0 )
		{
			return $row['id'];
		}
	else
		{
			return FALSE;
		}

}


//
// get_app_id
// Action: fetch the app id
// Call: get_app_id (string app)
//
function get_app_id ($app)
{
	$sql_query = "SELECT id FROM server_apps WHERE apps='$app'";
	$result = db_query ($sql_query);
	$row = db_array ($result['result']);
	if ( $result['rows'] > 0 )
		{
			return $row['id'];
		}
	else
		{
			return FALSE;
		}

}

//
// list_databases
// Action: list database with or without criteria
// Call: list_databases ()
//
function list_databases($domain=NULL, $dbname=NULL, $comment=NULL, $active='1', $limit='20')
{

	$list = "";
	$query_add = "";

	if ( $domain != NULL ) $query_add .= "AND domain.domain like '%$domain%' \n";
	//if ( $domain != NULL ) $query_add .= "AND domain.id=domain_alias.domain_id AND ( domain.domain like '%".$domain."%' OR domain_alias.dalias like '%".$domain."%') \n";
	if ( $dbname != NULL ) $query_add .= "AND dbname.dbname like '%$dbname%' \n";
	if ( $comment != NULL ) $query_add .= "AND dbname.comment like '%$comment%' \n";
	//	if ( $active != NULL ) $query_add .= "AND dbname.active like '%$comment%' \n";

	$sql_query = "SELECT dbname.id as db_id, dbname.dbname, dbname.description, domain.domain, dbtype.name as dbtype_name, server.public_name as server_name, dbname.dalias_id, dbname.port, server_id, server_ip_id
FROM dbname, domain, dbtype, server, server_ip
WHERE dbname.domain_id = domain.id
AND dbname.dbtype_id = dbtype.id
AND dbname.server_id = server.id
AND dbname.server_ip_id = server_ip.id

$query_add
LIMIT $limit
";

	$result = db_query ($sql_query);

	if ( $result['rows'] > 0 )
		{
			while ( $row = db_array ($result['result']) ){
				$list[] = $row;
			}
		}

	return $list;
}

//
// list_server_model_with_app
// Action: list all server model in the database with apps attached
// Call: list_server_model_with_app
//
function list_server_model_with_app(){

		$list = "";

		$query = "
SELECT server_jobmodel.*
FROM server_jobmodel, server_apps
WHERE server_jobmodel.active=1
AND server_jobmodel.id=server_apps.server_jobmodel_id
AND server_apps.active=1
ORDER BY role
";

		$result = db_query ($query);
		if ($result['rows'] > 0)
			{
				while ( $row = db_array ($result['result']) ){
					$list[] = $row;
				}
				
			}
		return $list;

}


//
// get_id_jobmodel
// Action: get a jobmodel id from his name
// Call: get_id_jobmodel (string jobmodel_name)
//
function get_id_jobmodel($jobmodel)
{

	$sql_query = "SELECT id FROM server_jobmodel WHERE role='$jobmodel'";
	$result = db_query ($sql_query);

	if ( $result['rows'] > 0 )
		{
			$row = db_array ($result['result']);
			return $row['id'];
		}
	else
		{
			return FALSE;
		}

}


//
// get_db_param2
// Action: get the parameters to connect to mysql server (new method with server_id, server_ip and port)
// Call: get_db_param (int server_id, int port, int ip_id)

function get_db_param2($server_id, $port, $ip_id)
{

		$list = "";

		$query = "
SELECT server_job.login, server_job.password, server_job.port, server_ip.public, server_ip.private
FROM server_job, server_ip
WHERE server_job.server_ip_id = server_ip.id
AND server_job.server_id='$server_id'
AND server_job.port='$port'
AND server_job.server_ip_id='$ip_id'
";

		$result = db_query ($query);
		if ($result['rows'] > 0)
			{
				$row = db_array ($result['result']) ;
				$list = $row;				
			}
		return $list;

}



//
// list_database_accounts
// Action: get list of user that have access to a database
// Call: list_database_accounts(int server_id, int port, int ip_id, string db_name)
//
function list_database_accounts($server_id, $port, $ip_id, $db_name)
{

	$list = array();

		$db_info = get_db_param2($server_id, $port, $ip_id);

		$sql_query = "
SELECT Db, User
FROM db
WHERE Db='$db_name'
AND User like 'us%'
";

		$result = db_query ($sql_query,1,"mysql",'mysql', $db_info['private'], $db_info['login'], $db_info['password'], $db_info['port']);
		if ($result['rows'] > 0)
			{
				while ($row = db_array ($result['result']) )
					{
						$row['password']= get_dbuser_password($row['User']);
						$list[] = $row;
					}

			}
		
		return $list;


}

//
// get_dbuser_password
// Action: get the password of a database user
// Call: get_dbuser_password (string db_user)
//
function get_dbuser_password($db_user)
{

	$sql_query = "SELECT password FROM dbusers WHERE username='$db_user'";
	$result = db_query ($sql_query);
	if ( $result['rows'] > 0 )
		{
			$row = db_array ($result['result']);
			return $row['password'];
		}
	else
		{
			return FALSE;
		}

}

//
// get_ip_info
// Action: fetch the information about an ip id
// Call: get_ip_info (int ip_id)
//
function get_ip_info ($ip_id)
{
	$sql_query = "SELECT * FROM server_ip WHERE id='$ip_id'";
	$result = db_query ($sql_query);
	$row = db_array ($result['result']);
	if ( $result['rows'] > 0 )
		{
			return $row;
		}
	else
		{
			return FALSE;
		}

}


//
// list_server_ftpd
// Action: check the whole list of ftpd server
// Call: list_server_ftpd ()
//
function list_server_ftpd()
{

	$sql_query = "SELECT server.*
FROM server_jobmodel, server_job, server
WHERE server_jobmodel.role='ftpd'
AND server_jobmodel.id=server_job.server_jobmodel_id
AND server_job.server_id=server_id
";

	$result = db_query ($sql_query);
	if ($result['rows'] > 0 )
		{
			while ($row = db_array ($result['result']) )
					{
						$list[] = $row;
					}
			return $list;
		}
	else
		{
			return FALSE;
		}
}



?>
