<?php

class DOMAIN
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}

	//
	// fetch_by_domainname
	// Action: create the object by the domain name
	// Call: fetch_by_domainname ( string domain)
	//
	function fetch_by_domainname ($domain_name)
	{
		$query = "SELECT * FROM domain WHERE domain='$domain_name'";
		$result = $this->db_link->sql_query($query);
		$this->data_domain = $result['result'][0];
		$this->domain_name = $this->data_domain['domain'];
		$this->storhash();
		$this->fetch_policy_id();
		$this->fetch_quota();
		$this->fetch_quota_status();
	}

	//
	// fetch_by_domainid
	// Action: create the object by the domain id
	// Call: fetch_by_domainid ( int domain)
	//
	function fetch_by_domainid ($domain_id)
	{
		$query = "SELECT * FROM domain WHERE id='$domain_id'";
		$result = $this->db_link->sql_query($query);
		$this->data_domain = $result['result'][0];
		$this->domain_name = $this->data_domain['domain'];
		$this->storhash();
    $this->fetch_policy_id();
		$this->fetch_quota();
		$this->fetch_quota_status();
	}

	//
	// fetch_policy_id
	// Action: fetch the policy id of the domain
	// Call: fetch_policy_id ()
	//
	function fetch_policy_id()
	{
	  	
		$query = "SELECT id as id
		FROM policy
		WHERE policy.domain_id = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->data['policy_id'] = $result['result'][0]['id'];
		
	}

	//
	// storhash
	// Action: create the storage path with hash
	// Call: storhash ()
	//
	function storhash ()
	{
		$dir = "";
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
	// fetch_quota
	// Action: fetch the defined quota for a domain
	// Call: fetch_quota ()
	//
	function fetch_quota()
	{
	  $query = "SELECT aliases, mailboxes, ftp_account, db_count, db_users, db_quota, whost_quota, maxquota
	  FROM domain
	  WHERE id = ".$this->data_domain['id'];
	  
	  $result = $this->db_link->sql_query($query);
	  $this->quota['mail_aliases'] = $result['result'][0]['aliases'];
	  $this->quota['mailboxes'] = $result['result'][0]['mailboxes'];
	  $this->quota['ftp_account'] = $result['result'][0]['ftp_account'];
	  $this->quota['db_count'] = $result['result'][0]['db_count'];
	  $this->quota['db_users'] = $result['result'][0]['db_users'];
	  $this->quota['db_quota'] = $result['result'][0]['db_quota'];
	  $this->quota['whost_quota'] = $result['result'][0]['whost_quota'];
	  $this->quota['maxquota'] = $result['result'][0]['maxquota'];
	}
	
	//
	// fetch_quota_status
	// Action: fetch the quota consumned for a domain
	// Call: fetch_quota_status ()
	//
	function fetch_quota_status()
	{

		$query = "SELECT COUNT(*) as total_alias
    FROM  alias
    LEFT JOIN mailbox ON alias.address=mailbox.username
    WHERE alias.domain_id=".$this->data_domain['id']."
    AND mailbox.maildir IS NULL
    AND alias.address NOT LIKE '@%'
    ORDER BY alias.address ";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['mail_alias'] = $result['result'][0]['total_alias'];


		$query = "SELECT COUNT(*) as total_mailbox
		FROM mailbox
		WHERE mailbox.domain_id = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->used_quota['mailbox'] = $result['result'][0]['total_mailbox'];

		$query = "SELECT COUNT(*) as total_ftpaccount
		FROM ftpaccount
		WHERE ftpaccount.domain_id = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->used_quota['ftpaccount'] = $result['result'][0]['total_ftpaccount'];


	 $query = "SELECT COUNT(*) as total_whost
	 FROM whost
	 WHERE whost.domain_id = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->used_quota['http'] = $result['result'][0]['total_whost'];



	 $query = "SELECT COUNT(*) as total_db
	 FROM dbname
	 WHERE dbname.domain_id  = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->used_quota['db'] = $result['result'][0]['total_db'];


	 $query = "SELECT COUNT(*) as total_dbusers
	 FROM dbusers
	 WHERE dbusers.domain_id = ".$this->data_domain['id'];

		$result = $this->db_link->sql_query($query);
		$this->used_quota['dbusers'] = $result['result'][0]['total_dbusers'];

	}

	//
	// en_disable
	// Action: enable or disable a domain and the whole object of the domain
	// Call: en_disable ()
	//
	function en_disable(){

	  $query = "UPDATE domain 
	  SET active=1-active
	  WHERE id = ".$this->data_domain['id'];

    $result = $this->db_link->sql_query($query);
    
	  $query = "UPDATE domain_alias
	  SET active=1-active
	  WHERE domain_id = ".$this->data_domain['id'];
    
    $result = $this->db_link->sql_query($query);

    // disable ftpaccount
    $query = "UPDATE ftpaccount
    SET active=1-active
    WHERE domain_id = ".$this->data_domain['id'];
    
    $result = $this->db_link->sql_query($query);
    
    // disable ftpaccount
    $query = "UPDATE mailbox
    SET active=1-active
    WHERE domain_id = ".$this->data_domain['id'];

    $result = $this->db_link->sql_query($query);

    // disable wwwhost
    $query = "UPDATE whost
    SET active=1-active
    WHERE domain_id = ".$this->data_domain['id'];

    $result = $this->db_link->sql_query($query);


    // TODO : add into table alias a new column id (domain_id) to disable it

	}


	//
	// total_diskspace_used_mailboxes
	// Action: Get the sum of all diskspace used by mailboxes
	// Call: total_diskspace_used_mailboxes ()
	//
  function total_diskspace_used_mailboxes ()
  {
    $sql_query = "SELECT SUM(stats_mailbox.size) as total_mbox_size
    FROM stats_mailbox, mailbox
    WHERE mailbox.domain_id = ".$this->data_domain['id']."
    AND mailbox.id=stats_mailbox.mailbox_id
    GROUP BY date, mailbox.id
    ORDER BY date DESC
    LIMIT 1";

    $result = $this->db_link->sql_query($sql_query);
    if ( $result['rows'] == 0 ){
      $this->data['total_diskspace_used_mailboxes'] = 0;
    }
    else {
      $this->data['total_diskspace_used_mailboxes'] = $result['result'][0]['total_mbox_size']; 
    }

  }

  //
	// fetch_mail_aliases
	// Action: Get the whole list of mailboxes aliases
	// Call: fetch_mail_aliases (string search_param, string result_limit, string order_by_field, string order_dir)
	//
  function fetch_mail_aliases ($search_param = NULL, $result_limit = NULL, $order_by_field = NULL, $order_dir = NULL)
  {
    global $CONF;
    
    if ($CONF['alias_control'] == "YES")
    {
      $query = "SELECT alias.address, alias.goto, alias.modified, alias.policy_id, alias.active
      FROM alias
      WHERE alias.domain='".$this->data_domain['domain']."'
      ORDER BY alias.address ";
    }
    else
    {
      $query = "SELECT alias.address, alias.goto, alias.modified, alias.policy_id, alias.active
      FROM  alias
      LEFT JOIN mailbox ON alias.address=mailbox.username
      WHERE alias.domain_id=".$this->data_domain['id']."
      AND mailbox.maildir IS NULL
      AND alias.address NOT LIKE '@%'
      ORDER BY alias.address ";
    }

		if ( $result_limit != NULL ){
			$query .= "LIMIT $result_limit ";
		}
    
    $result = $this->db_link->sql_query($query);
		$this->list_mail_aliases = $result['result'];
    
  }

  //
	// fetch_mailboxes
	// Action: Get the whole list of mailboxes aliases
	// Call: fetch_mailboxes (string search_param, string result_limit, string order_by_field, string order_dir)
	//
  function fetch_mailboxes ($search_param = NULL, $result_limit = NULL, $order_by_field = NULL, $order_dir = NULL)
  {

		GLOBAL $CONF;

		if ( $order_by_field == NULL ) { $order_by_field = $CONF['order_display']; }
		if ( $order_dir == NULL ) { $order_dir = "ASC"; }

    $query = "SELECT mailbox.*, alias.policy_id, spamreport.*, vacation.active as vacation_active
    FROM alias, mailbox
    LEFT OUTER JOIN spamreport ON ( mailbox.id = spamreport.mailbox_id )
    LEFT OUTER JOIN vacation ON ( mailbox.id = vacation.mailbox_id )
    WHERE mailbox.domain_id=".$this->data_domain['id']."
    AND mailbox.username=alias.address
    ORDER BY ".$order_by_field." $order_dir ";

		if ( $result_limit != NULL ){
			$query .= "LIMIT $result_limit ";
		}


    $result = $this->db_link->sql_query($query);
		$this->list_mailboxes = $result['result'];

  }

  //
	// can_add_mail_alias
	// Action: Check if a domain can have new mail aliases
	// Call: can_add_mail_alias ()
	//
  function can_add_mail_alias(){
    if( 
        ( ($this->used_quota['mail_alias'] - $this->quota['mail_aliases']) > 0 )
      ||
        ( $this->quota['mail_aliases'] == -1 )
      ){return TRUE;}
      return FALSE;
  }

  //
	// can_add_mailbox
	// Action: Check if a domain can have new mailboxes
	// Call: can_add_mailbox ()
	//
  function can_add_mailbox(){
    if( 
        ( ($this->used_quota['mailbox'] - $this->quota['mailboxes']) > 0 )
      ||
        ( $this->quota['mailboxes'] == -1 )
      ){return TRUE;}
      return FALSE;
  }
  

}


?>