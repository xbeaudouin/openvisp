<?php

class DOMAIN
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}

	function fetch_by_domainname ($domain_name)
	{
		$query = "SELECT * FROM domain WHERE domain='$domain_name'";
		$result = $this->db_link->sql_query($query);
		$this->data_domain = $result['result'][0];
		$this->domain_name = $this->data_domain['domain'];
		$this->storhash();
		$this->fetch_quota();
	}

	function fetch_by_domainid ($domain_id)
	{
		$query = "SELECT * FROM domain WHERE id='$domain_id'";
		$result = $this->db_link->sql_query($query);
		$this->data_domain = $result['result'][0];
		$this->domain_name = $this->data_domain['domain'];
		$this->storhash();
		$this->fetch_quota();
	}


	function storhash ()
	{
		$directory = explode('.',$this->domain_name);
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

		$this->hash_storage = $dir."/".$this->domain_name;
	}

	//
	// function quota_status
	// this function set values for consumned domain quota
	//

	function fetch_quota()
	{

		$query = "SELECT COUNT(*) as total_alias
FROM alias, policy
WHERE policy.domain_id = ".$this->data_domain['id']."
AND policy.id=alias.policy_id";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['mail_alias'] = $result['result'][0]['total_alias'];


		$query = "SELECT COUNT(*) as total_mailbox
FROM mailbox
WHERE mailbox.domain_id = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['mailbox'] = $result['result'][0]['total_mailbox'];

		$query = "SELECT COUNT(*) as total_ftpaccount
FROM ftpaccount
WHERE ftpaccount.domain_id = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['ftpaccount'] = $result['result'][0]['total_ftpaccount'];


	 $query = "SELECT COUNT(*) as total_whost
FROM whost
WHERE whost.domain_id = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['http'] = $result['result'][0]['total_whost'];



	 $query = "SELECT COUNT(*) as total_db
FROM dbname
WHERE dbname.domain_id  = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['db'] = $result['result'][0]['total_db'];


	 $query = "SELECT COUNT(*) as total_dbusers
FROM dbusers
WHERE dbusers.domain_id = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['dbusers'] = $result['result'][0]['total_dbusers'];


	 $query = "SELECT id as id
FROM policy
WHERE policy.domain_id = ".$this->data_domain['id']." 
";

		$result = $this->db_link->sql_query($query);
		$this->data_domain['policy_id'] = $result['result'][0]['id'];



	}


}


?>