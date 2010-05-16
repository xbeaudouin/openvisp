<?php

class USER
{

	private $db_link;
	
	function USER($db_link)
	{
		$this->db_link = $db_link;
		$this->remote_host = $_SERVER['REMOTE_ADDR'];
	}

	function fetch_info($username)
	{

		GLOBAL $PALANG;

		if ( $username == "" )
			{
				throw new Exception ($PALANG['pRights_no_webhosting']);
			}

		$query = "SELECT * FROM accounts WHERE username='$username'";
		$result = $this->db_link->sql_query($query);
		$this->data = $result['result'][0];
		$this->total_row = $result['rows'];
		$this->username = $username;
		$this->fetch_rights();
		$this->fetch_quota();
	}

	function fetch_rights()
	{
		$query = "SELECT * FROM rights WHERE accounts_id = ".$this->data['id'];
		$result = $this->db_link->sql_query($query);
		$this->rights = $result['result'][0];
	}

	function fetch_quota()
	{
		$query = "SELECT * FROM quota WHERE accounts_id = ".$this->data['id'];
		$result = $this->db_link->sql_query($query);
		$this->data_quota = $result['result'][0];
	}


	function check_access($part)
	{
		if ( $this->rights[$part] != 1 )
			{
				throw new Exception ("DSL vous n'avez pas les droits de gerer la partie $part");
			}
	}

	function fetch_domains()
	{
		if ( $this->rights['manage'] == 1 )
			{
				$query = "SELECT domain.domain, domain.id, domain.modified
				FROM domain
				WHERE domain.domain != 'ova.local'
        ";
			}
		else
			{
				$query = "SELECT domain.domain, domain.id, domain.modified
				FROM domain, domain_admins
				WHERE domain_admins.accounts_id = ".$this->data['id']."
				AND domain_admins.domain_id=domain.id
				";
			}
		$query .= "ORDER BY domain.domain";


		$result = $this->db_link->sql_query($query);

		$this->data_managed_domain = $result['result'];
		$this->total_managed_domain = $result['rows'];

	}

	function fetch_active_domains($search_param = NULL, $result_limit = NULL, $order_by_field = NULL, $order_dir = NULL)
	{
		if ( $this->rights['manage'] == 1 )
		{
		  $query = "SELECT domain.domain, domain.id, domain.modified
		  FROM domain
		  WHERE domain.domain != 'ova.local'
		  AND domain.active = 1
		  ";
		}
		else
		{
		  $query = "SELECT domain.domain, domain.id, domain.modified
		  FROM domain, domain_admins
		  WHERE domain_admins.accounts_id = ".$this->data['id']."
		  AND domain_admins.domain_id = domain.id
		  AND domain.active = 1
		  ";
		}
		
		if ( $search_param != NULL ){
		  $query .= "AND domain.domain like '".$search_param."%' ";
		}
		
		if ( $order_by_field == NULL ){
			$query .= "ORDER BY domain.domain ";
		}
		else {
			$query .= "ORDER BY $order_by_field $order_dir ";
		}

		if ( $result_limit != NULL ){
			$query .= "LIMIT $result_limit ";
		}

		$result = $this->db_link->sql_query($query);

		$this->data_managed_active_domain = $result['result'];
		$this->total_managed_active_domain = $result['rows'];

	}

	// Function : fetch_active_domains_with_mail

	function fetch_active_domains_with_mail($search_param = NULL, $result_limit = NULL, $order_by_field = NULL, $order_dir = NULL)
	{
		if ( $this->rights['manage'] == 1 )
		{
		  $query = "SELECT domain.domain, domain.id, domain.modified
		  FROM domain
		  WHERE domain.domain != 'ova.local'
		  AND domain.active = 1
		  AND domain.mailboxes <> 0
		  ";
		}
		else
		{
		  $query = "SELECT domain.domain, domain.id, domain.modified
		  FROM domain, domain_admins
		  WHERE domain_admins.accounts_id = ".$this->data['id']."
		  AND domain_admins.domain_id = domain.id
		  AND domain.active = 1
		  AND domain.mailboxes <> 0
		  ";
		}
		
		if ( $search_param != NULL ){
		  $query .= "AND domain.domain like '".$search_param."%' ";
		}
		
		if ( $order_by_field == NULL ){
			$query .= "ORDER BY domain.domain ";
		}
		else {
			$query .= "ORDER BY $order_by_field $order_dir ";
		}

		if ( $result_limit != NULL ){
			$query .= "LIMIT $result_limit ";
		}

		$result = $this->db_link->sql_query($query);

		debug_info($query);

		$this->data_managed_active_domain_with_mail = $result['result'];
		$this->total_managed_active_domain_with_mail = $result['rows'];

	}


	function check_quota($type)
	{

		if ( ! isset($this->data_quota_used[$type]) )
			{

				$this->fetch_domains();
				$this->data_quota_used[$type] = 0;

				for ( $i = 0; $i < $this->total_managed_domain; $i++ )
					{
						$domain = new DOMAIN($this->db_link);
						$domain->fetch_by_domainname($this->data_managed_domain[$i]['domain']);
						$this->data_quota_used[$type] =+ $domain->used_quota[$type];
					}

			}


		if ( $this->data_quota_used[$type] < $this->data_quota[$type] || $this->data_quota[$type] == "-1" )
			{
				return TRUE;
			}
		else {
				throw new Exception ("DSL vous n'avez pas assez de quota disponible sur $type");
		}


	}

	//
	// function fetch_quota_status
	// this function fetch values for consumned thing
	//

	function fetch_quota_status()
	{
	  if ( $this->rights['manage'] == 1 )
	  {

	    $query = "SELECT COUNT(username) AS total_mailbox FROM mailbox";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_mailbox = $result['result'][0]['total_mailbox'];
	    
	    $query = "SELECT COUNT(address) AS total_alias FROM alias";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_mail_alias = $result['result'][0]['total_alias'];

	    $query = "SELECT COUNT(vhost) AS total_vhost FROM whost";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_web_host = $result['result'][0]['total_vhost'];

	    $query = "SELECT COUNT(login) AS total_ftp_account FROM ftpaccount";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_ftp_account = $result['result'][0]['total_ftp_account'];

	    $query = "SELECT COUNT(dbname.id) AS total_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_mysql_db = $result['result'][0]['total_db'];

	    $query = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_mysql_user = $result['result'][0]['count_users'];
	    
	    $query = "SELECT COUNT(dbname.id) AS total_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_pgsql_db = $result['result'][0]['total_db'];

	    $query = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed_pgsql_user = $result['result'][0]['count_users'];
	    
	  }
	}


	function check_domain_access($domain_id)
	{
	  if ($this->rights['manage'] != 1){
	    $query = "SELECT domain_id
	    FROM domain_admins
	    WHERE domain_admins.accounts_id = ".$this->data['id']."
	    AND domain_admins.domain_id=$domain_id";
	    
	    $result = $this->db_link->sql_query($query);
	    if ( $result['rows'] == 0 ){
	      session_unset ();
	      session_destroy ();
	      header ("Location: ../login.php");
	      exit;
	    }
	  }
	}

	function check_domain_admin()
	{
	  if ( ($this->rights['manage'] != 1) || ($this->rights['domain'] != 1) ){
	      session_unset ();
	      session_destroy ();
	      header ("Location: ../login.php");
	      exit;
	    
	  }
	}


	function all_user_mailbox_size(){

		$sql_query = "SELECT SUM(stats_mailbox.size) as total_mbox_size
    FROM stats_mailbox, mailbox, domain_admins
    WHERE domain_admins.accounts_id = ".$this->accounts_id."
    AND domain_admins.domain_id = mailbox.domain_id
    AND mailbox.id=stats_mailbox.mailbox_id
    GROUP BY date, mailbox.id
    ORDER BY date DESC
    LIMIT 1";


	}

	
}


?>