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
				$query = "SELECT domain.domain, domain.id
FROM domain
WHERE domain.domain != 'ova.local'
";
			}
		else
			{
				$query = "SELECT domain.domain, domain.id
FROM domain, domain_admins
WHERE domain_admins.accounts_id = ".$this->data['id']."
AND domain_admins.domain_id=domain.id
";
			}
		$query .= "
ORDER BY domain.domain
";


		$result = $this->db_link->sql_query($query);

		$this->data_managed_domain = $result['result'];
		$this->total_managed_domain = $result['rows'];

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




}


?>