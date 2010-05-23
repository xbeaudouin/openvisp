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

		global $PALANG;

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

	//
	// function fetch_domains
	// This function list the domain associated with the user account
	// Call fetch_domains()

	function fetch_domains($search_param = NULL, $result_limit = NULL, $order_by_field = NULL, $order_dir = NULL)
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

	// function check_quota
	// This function to feth a status of a speficied quota like total mailbox used per user
	// Return True if user can add a new type of data, an exception in the other case.
	// Call check_quota(string type)

	function check_quota($type)
	{

		global $domain_info;

		if ( ! isset($this->data_quota_used[$type]) )
			{

				if ( ! isset($this->total_managed_domain)){
					$this->fetch_domains();
				}
				$this->data_quota_used[$type] = 0;

				for ( $i = 0; $i < $this->total_managed_domain; $i++ )
					{
						$domain_info->fetch_by_domainname($this->data_managed_domain[$i]['domain']);
						$this->data_quota_used[$type] += $domain_info->used_quota[$type];
					}

			}
		 
		$array['quota'] = $this->data_quota[$type];
		$array['used_quota'] = $this->data_quota_used[$type];
		$array['available_quota'] = $this->data_quota[$type] - $this->data_quota_used[$type];

		$this->data_managed[$type] = $this->data_quota_used[$type];


		if ( $this->data_quota_used[$type] < $this->data_quota[$type] || $this->data_quota[$type] == "-1" ){
			$array['result'] = TRUE;
		}
		else {
			//throw new Exception ("DSL vous n'avez pas assez de quota disponible sur $type");
			$array['result'] = FALSE;
		}

		return $array;
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
	    $this->data_managed['mailboxes'] = $result['result'][0]['total_mailbox'];
	    
	    $query = "SELECT COUNT(address) AS total_alias FROM alias";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['aliases'] = $result['result'][0]['total_alias'];

	    $query = "SELECT COUNT(vhost) AS total_vhost FROM whost";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['web_host'] = $result['result'][0]['total_vhost'];

	    $query = "SELECT COUNT(login) AS total_ftp_account FROM ftpaccount";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['ftp_account'] = $result['result'][0]['total_ftp_account'];

	    $query = "SELECT COUNT(dbname.id) AS total_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['mysql_db'] = $result['result'][0]['total_db'];

	    $query = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['mysql_user'] = $result['result'][0]['count_users'];
	    
	    $query = "SELECT COUNT(dbname.id) AS total_db FROM dbname, dbtype WHERE dbname.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['pgsql_db'] = $result['result'][0]['total_db'];

	    $query = "SELECT COUNT(dbusers.id) AS count_users FROM dbusers,dbtype WHERE dbusers.dbtype_id=dbtype.id AND dbtype.name='mysql'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['pgsql_user'] = $result['result'][0]['count_users'];

	    $query = "SELECT COUNT(domain.id) AS count_domain FROM domain WHERE domain != 'ova.local'";
	    $result = $this->db_link->sql_query($query);
	    $this->data_managed['domains'] = $result['result'][0]['count_domain'];
	    
	  }
		else{
			if ( ! isset($this->total_managed_domain)){
				$this->fetch_domains();
			}

			$this->data_managed['domains'] = sizeof($this->total_managed_domain) + 1;
			
			$domain_info = $this->check_quota('aliases');
			$domain_info = $this->check_quota('mailboxes');

			
		}
	}


	function check_domain_access($domain_id, $destroy=1)
	{
	  if ($this->rights['manage'] != 1){
	    $query = "SELECT domain_id
	    FROM domain_admins
	    WHERE domain_admins.accounts_id = ".$this->data['id']."
	    AND domain_admins.domain_id=$domain_id";
	    
	    $result = $this->db_link->sql_query($query);
	    if ( $result['rows'] == 0){
				if ( $destroy == 1 ){
					session_unset ();
					session_destroy ();
					header ("Location: ../login.php");
					exit;
				}
				return FALSE;
	    }
			return TRUE;
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


  //
	// can_add_domain
	// Action: Check if an admin can have new domain
	// Call: can_add_domain (int number_to_add)
	//
  function can_add_domain($number_to_add = 1){
    if( 
        ( ( $this->data_quota['domain'] - $this->data_managed['domain'] ) >= $number_to_add )
      ||
        ( $this->data_quota['domain'] == -1 )
      ){return TRUE;}
      return FALSE;
  }


  //
	// can_add_item
	// Action: Check if an admin can have new item (mailboexe, domain, aliases...)
	// Call: can_add_item (int number_to_add)
	//
  function can_add_item($number_to_add = 1,$item){
    if( 
        ( ( $this->data_quota[$item] - $this->data_managed[$item] ) >= $number_to_add )
      ||
        ( $this->data_quota[$item] == -1 )
      ){return TRUE;}
      return FALSE;
  }



	
}


?>