<?php
//
// File: accounts.inc.php
//
// Accounts management stuff
//
error_reporting  (E_NOTICE | E_ERROR | E_WARNING | E_PARSE);

if (ereg ("accounts.inc.php", $_SERVER['PHP_SELF']))
{
	header ("Location: ../login.php");
	exit;
}

//
// list_admins
// Action: Lists all accounts
// Call: list_accounts ()
//
function list_accounts ()
{
	$list = "";
   
	$result = db_query ("SELECT username FROM accounts ORDER BY username");
	if ($result['rows'] > 0)
	{
		$i = 0;
		while ($row = db_array ($result['result']))
		{
			$list[$i] = $row['username'];
			$i++;
		}
	}
	return $list;
}

//
// get_account_properties
// Action: Get all account properties.
// Call: get_account_properties (string account)
//
function get_account_properties ($username)
{
   $list = "";
        
   $result = db_query ("SELECT * FROM accounts, rights WHERE accounts.username='$username' AND accounts.id=rights.accounts_id ");
   $row = db_array ($result['result']);

	 $numberfields = mysql_num_fields($result['result']);
	 for ($i=0; $i<$numberfields ; $i++ ) {
		 $var = mysql_field_name($result['result'], $i);
		 $list[$var] = $row[$var];
	 }

   return $list;
}

//
// list_domains_for_users
// Action: Get all domain of a user.
// Call: list_domains_for user (string username)
//
function list_domains_for_users ($username)
{
   $list = "";
   //$result = db_query ("SELECT domain.domain FROM domain LEFT JOIN domain_admins ON domain.domain=domain_admins.domain WHERE domain_admins.username='$username' AND domain.active='1' ORDER BY domain_admins.domain");

	 $username_id = get_account_id($username);

	 $sql_query = "SELECT domain.*
FROM domain_admins, domain
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=domain.id
AND domain.active='1'
ORDER BY domain";

   $result = db_query ($sql_query);
   if ($result['rows'] > 0)
   {
      while ($row = db_array ($result['result']))
      {
         $list[] = $row['domain'];
      }
   }
   return $list;
}

//
// user_exist
// Action: Check if the username is already exists.
// Call: user_exist (string username)
//
function user_exist ($username)
{
   $result = db_query ("SELECT username FROM accounts WHERE username='$username'");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// list_ftp_accounts
// Action: Get all FTP accounts of a user.
// Call: list_ftp_accounts (int iduser)
//
function list_ftp_accounts ($userid)
{
    $list = "";
     
    $result = db_query ("SELECT * FROM ftpaccount WHERE owner='$userid'");
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
// list_mailbox_accounts
// Action: Get all mailbox accounts of a user.
// Call: list_mailbox_accounts (string username)
//
function list_mailbox_accounts ($username)
{
    $list = "";
		$username_id = get_account_id($username);
		$sql_query = "SELECT mailbox.*, domain.domain
FROM mailbox,domain_admins,domain
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=mailbox.domain_id
AND mailbox.domain_id=domain.id
 ";

    $result = db_query ($sql_query);
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
// list_datacenter_accounts
// Action: Get all datacenter accounts of a user.
// Call: list_datacenter_accounts (string username)
//
function list_datacenter_accounts ($username)
{
    $list = "";
     
    $list['id'] = get_account_id($username);
    
    $result = db_query ("SELECT COUNT(*) FROM apc_admins WHERE id='".$list['id']."'");
    $row = db_row ($result['result']);
    $list['counter'] = $row[0];
    
    $result = db_query ("SELECT * FROM apc_admins WHERE id='".$list['id']."'");
    $row = db_array ($result['result']);
    
    $list['apc'] = $row['apc'];
    $list['port']     = $row['port'];
    $list['created'] = $row['created'];
    $list['active'] = $row['active'];
    
    return $list;
}

// Add / Edit Account function

// Why ? We need to avoid SQL stuff in all files if possible.

//
// chk_passwd_user
// Action: check if the password given is correct or not
// Call: chk_passwd_user (string user, string password)
//
function chk_passwd_user($user, $password)
{
  $result = db_query("SELECT password FROM accounts WHERE username='$user' AND enabled='1'");
  if ($result['rows'] == 1) 
  {
    $row = db_array ($result['result']);
    $checked_password = pacrypt($password, $row['password']);

    $result = db_query("SELECT password FROM accounts WHERE username='$user' AND password='$checked_password' AND enabled='1'");
    
    if ($result['rows'] == 1)
    { 
       return 1;
    }
  }
  return NULL;
}

//
// add_admin_user()
// Add an admin user (login + password), return NULL is error 
// Call: add_admin_user("username", "password)
//
function add_admin_user($fUsername, $password)
{

  $result = db_query("INSERT INTO accounts (username,password,created,modified) VALUES ('$fUsername','$password',NOW(),NOW())");

  if ($result['rows'] != 1) return NULL;
  else return "Ok";
}

//
// get_account_right()
// get account right  (ftp, email..)
// Call: get_account_right(int id)
//
function get_account_right($id)
{
	$list = "";
	$result = db_query ("SELECT * FROM rights WHERE accounts_id='$id'");
	if ($result['rows'] == 1)
	{
      	$row = db_array ($result['result']);
		$numberfields = mysql_num_fields($result['result']);
		for ($i=0; $i<$numberfields ; $i++ ) {
			$var = mysql_field_name($result['result'], $i);
			$list[$var] = $row[$var];
		}
	}
	return $list;
}


//
// get_account_info()
// get account informations
// Call: get_account_info(string username)
//
function get_account_info($username)
{
	$list = "";
	$result = db_query ("SELECT * FROM accounts WHERE username='$username'");
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$numberfields = mysql_num_fields($result['result']);
		for ($i=0; $i<$numberfields ; $i++ ) {
			$var = mysql_field_name($result['result'], $i);
			$list[$var] = $row[$var];
		}
	}

	return $list;
}


//
// get_account_id
// Action: return the id of named account (mostly to avoid MySQL 5 stuff everywhere)
// Call: get_account_id(string admin)
//
function get_account_id($username)
{
	$result = get_account_info($username);
	return $result['id'];
}

//
// get_account_quota()
// get account quota
// Call: get_account_quota(string username)
//
function get_account_quota($account_id)
{
	$list = "";
	$result = db_query ("SELECT * FROM quota WHERE accounts_id='$account_id'");
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$numberfields = mysql_num_fields($result['result']);
		for ($i=0; $i<$numberfields ; $i++ ) {
			$var = mysql_field_name($result['result'], $i);
			$list[$var] = $row[$var];
		}
	}
	return $list;
}

//
// transform_sadmin
// transform an admin account in sadmin
// Call: transform_sadmin(int id)
//
function transform_sadmin($id)
{
	$query = "UPDATE rights SET mail='1', datacenter='1', ftp='1', http='1', mysql='1', postgresql='1', domain='1', manage='1'
WHERE accounts_id='$id'";
	$result = db_query ($query);

	$query = "UPDATE quota SET diskspace='-1', ftp='-1', mysqldb='-1', mysqlusers='-1', postgresqldb='-1',postgresqlusers='-1', domains='-1',
mailboxes='-1', aliases='-1', http='-1', http_alias='-1'
WHERE accounts_id='$id'";
	$result = db_query ($query);
}

//
// transform_datacenter_sadmin
// Transform a datacenter account into super admin
// Call: transform_datacenter_sadmin(id, int)
// int : should be 1 = sadmin
//                 0 = normal user
function transform_datacenter_sadmin($id, $status) 
{
	$query = "UPDATE rights SET datacenter_manage='".$status."' WHERE accounts_id='$id'";
	$result = db_query ($query);
}

//
// update_right_admin
// update admin rights
// Call: transform_sadmin(int id)
//
function update_right_admin($id,$fMail,$fDomains,$fDatacenter,$fFTP,$fHTTP,$fMysql,$fPostgresql)
{
	$query = "UPDATE rights SET mail='$fMail', datacenter='$fDatacenter', ftp='$fFTP', http='$fHTTP', mysql='$fMysql', postgresql='$fPostgresql', domain='$fDomains', manage='0'
WHERE accounts_id='$id'";
	$result = db_query ($query);
}

//
// add_quota_admin
// add quota for a new admin
// Call: add_quota_admin( int id, int nbmysqluser, int nbmysqldb, int nbpostgresqluser, int nbpostgresqldb, int nbdomain, int nbwebsite, int nbwebsitealias,
// int nbftpaccount, int nbemail, int nbemailalias)
//
function add_quota_admin($id, $fNmysqlusers, $fNmysqldb, $fNposgresqlusers, $fNposgresqldb, $fNdomains,
												 $fNwebsite,$fNwebsitealias, $fNftpaccount, $fNbemail, $fNbemailalias,$fDiskspace)
{

	$query = "INSERT INTO quota VALUES('$id','$fDiskspace', '$fNftpaccount', '$fNmysqldb', '$fNmysqlusers', '$fNposgresqldb', '$fNposgresqlusers',
'$fNdomains', '$fNbemail', '$fNbemailalias', '$fNwebsite', '$fNwebsitealias',NOW(),NOW())";
	$result = db_query ($query);
}


//
// update_quota_admin
// update the quota of the user
// Call: update_quota_admin( int id, int nbmysqluser, int nbmysqldb, int nbpostgresqluser, int nbpostgresqldb, int nbdomain, int nbwebsite, int nbwebsitealias,
// int nbftpaccount, int nbemail, int nbemailalias)
//
function update_quota_admin(	$id, $fNmysqlusers, $fNmysqldb, $fNposgresqlusers, $fNposgresqldb, $fNdomains,
															$fNwebsite,$fNwebsitealias, $fNftpaccount, $fNbemail, $fNbemailalias, $fDiskspace)
{

	$query = "UPDATE quota SET diskspace='$fDiskspace', ftp='$fNftpaccount', mysqldb='$fNmysqldb', mysqlusers='$fNmysqlusers', postgresqldb='$fNposgresqldb',
	postgresqlusers='$fNposgresqlusers', domains='$fNdomains', mailboxes='$fNbemail', aliases='$fNbemailalias', http='$fNwebsite', http_alias='$fNwebsitealias'
WHERE accounts_id='$id'";
	$result = db_query ($query);
}

//
// get_account_dbused
// get the size of admin DB
// Call: get_account_dbused(string username)
// 
function get_account_dbused($username){

	$sql = "SHOW TABLE STATUS";
	$result = mysql_query($sql);
	while($row = mysql_fetch_array($result))
		{ $total = $row['Data_length']+$row['Index_length']; }
	echo($total);

}


//
// get_account_used()
// get how much an account used
// Call: get_account_quota(string username, int admin)
//
function get_account_used($username,$admin)
{
	$list = "";

	$username_id = get_account_id($username);

	if ( check_admin($username) == 1 ){
		$query_domain = "SELECT count(domain) as count_domain FROM domain WHERE domain != 'ova.local'";
		$query_mailbox = "SELECT COUNT(username) AS count_mailbox FROM mailbox";
		$query_mailalias = "SELECT COUNT(address) AS count_alias FROM alias";
		$query_http = "SELECT COUNT(vhost) AS count_vhost FROM whost";
		$query_ftp = "SELECT COUNT(login) AS count_ftp FROM ftpaccount";
		$query_mysql_db = "SELECT COUNT(dbname.id) AS count_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='mysql'";
		$query_mysql_users = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='mysql'";
		$query_postgresql_db = "SELECT COUNT(dbname.id) AS count_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='postgresql'";
		$query_postgresql_users = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='postgresql'";
	}
	else{

		$query_domain = "SELECT count(domain_admins.domain_id) as count_domain FROM domain_admins WHERE domain_admins.accounts_id='$username_id'";
		$query_mailbox = "SELECT COUNT(mailbox.username) AS count_mailbox FROM mailbox,domain_admins WHERE domain_admins.accounts_id='$username_id' AND domain_admins.domain_id=mailbox.domain_id";

		$query_mailalias = "SELECT COUNT(alias.address) AS count_alias
FROM alias, domain_admins, policy
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=policy.domain_id
AND policy.id=alias.policy_id";

		$query_http = "SELECT COUNT(vhost) AS count_vhost
FROM whost, domain_admins
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=whost.domain_id";

		$query_ftp = "SELECT COUNT(login) AS count_ftp
FROM ftpaccount,domain_admins
WHERE domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=ftpaccount.domain_id";

		$query_mysql_db = "SELECT COUNT(dbname.id) AS count_db
FROM dbname, dbtype, domain_admins
WHERE dbtype.name='mysql'
AND dbtype.id=dbname.dbtype_id
AND dbname.domain_id=domain_admins.domain_id
AND domain_admins.accounts_id='$username_id'";

		$query_mysql_users = "SELECT COUNT(*) AS count_users
FROM dbusers, domain_admins, dbtype
WHERE dbtype.name='mysql'
AND dbtype.id=dbusers.dbtype_id
AND domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=dbusers.domain_id";

		$query_postgresql_db = "SELECT COUNT(dbname.id) AS count_db
FROM dbname, dbtype, domain_admins
WHERE dbtype.name='postgresql'
AND dbtype.id=dbname.dbtype_id
AND dbname.domain_id=domain_admins.domain_id
AND domain_admins.accounts_id='$username_id'";

		$query_postgresql_users = "SELECT COUNT(*) AS count_users
FROM dbusers, domain_admins, dbtype
WHERE dbtype.name='postgresql'
AND dbtype.id=dbusers.dbtype_id
AND domain_admins.accounts_id='$username_id'
AND domain_admins.domain_id=dbusers.domain_id";

	}


	$result = db_query ($query_domain);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['domains'] = $row['count_domain'];
	}
	else { $list['domains'] = 0; }

	$result = db_query ($query_mailbox);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['emails'] = $row['count_mailbox'];
	}
	else { $list['emails'] = 0; }

	$result = db_query ($query_mailalias);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['emailsaliases'] = $row['count_alias'];
	}
	else { $list['emailsaliases'] = 0; }

	$result = db_query ($query_http);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['http'] = $row['count_vhost'];
	}
	else { $list['http'] = 0; }

	$result = db_query ($query_ftp);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['ftp'] = $row['count_ftp'];
	}
	else { $list['ftp'] = 0; }

	$result = db_query ($query_mysql_db);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['mysqldb'] = $row['count_db'];
	}
	else { $list['mysqldb'] = 0; }

	$result = db_query ($query_mysql_users);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['mysqlusers'] = $row['count_users'];
	}
	else { $list['mysqlusers'] = 0; }

	$result = db_query ($query_postgresql_db);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['postgresqldb'] = $row['count_db'];
	}
	else { $list['postgresqldb'] = 0; }

	$result = db_query ($query_postgresql_users);
	if ($result['rows'] == 1)
	{
		$row = db_array ($result['result']);
		$list['postgresqlusers'] = $row['count_users'];
	}
	else { $list['postgresqlusers'] = 0; }

	$list['http_alias'] = "0";

	return $list;
}



// 
// check_domain_quota
// Action: check if we can add new domain
// call check_domain_quota ();
//

function check_domain_quota()
{
	GLOBAL $CONF;
	GLOBAL $SESSID_USERNAME;
	GLOBAL $PALANG;

	$account_information = get_account_info($SESSID_USERNAME);
	$account_quota = get_account_quota($account_information['id']);
	$quota_used = get_account_used($SESSID_USERNAME,check_admin($SESSID_USERNAME));

	if ( $account_quota['domains'] != "-1" && $quota_used['domains'] >= $account_quota['domains'] ) return FALSE; 
	
	return TRUE;

}


?>
