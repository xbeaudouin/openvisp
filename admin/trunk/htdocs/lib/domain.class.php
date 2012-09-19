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
    if ( $result['rows'] == 1 ){
      $this->data_domain = $result['result'][0];
      $this->domain_name = $this->data_domain['domain'];
      $this->domain_status = 1;
      $this->check_user_access();
      $this->storhash();
      $this->fetch_quota();
      $this->fetch_quota_status();
    }
    else{
      $this->domain_status = 0;
    }
	}

  function __destruct() {
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
    if ( $result['rows'] == 1 ){
      $this->data_domain = $result['result'][0];
      $this->domain_name = $this->data_domain['domain'];
      $this->domain_status = 1;
      $this->check_user_access();
      $this->storhash();
      $this->fetch_quota();
      $this->fetch_quota_status();
      $this->fetch_policy_id();
    }
    else{
      $this->domain_status = 0;
    }
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
	  $this->quota['aliases'] = $result['result'][0]['aliases'];
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
	// Action: fetch the quota consumned for current domain
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
    AND alias.active < 9
    ORDER BY alias.address ";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['aliases'] = $result['result'][0]['total_alias'];


		$query = "SELECT COUNT(*) as total_mailbox
		FROM mailbox
		WHERE mailbox.domain_id = ".$this->data_domain['id']."
    AND mailbox.active < 9
    ";

		$result = $this->db_link->sql_query($query);
		$this->used_quota['mailboxes'] = $result['result'][0]['total_mailbox'];

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
    
    // disable mailbox
    $query = "UPDATE mailbox
    SET active=1-active
    WHERE domain_id = ".$this->data_domain['id']."
    AND active < 9
    ";

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
    $sql_query = "SELECT SUM(stats_mail_user.mailbox_size) as total_mbox_size
    FROM stats_mail_user, mailbox
    WHERE mailbox.domain_id = ".$this->data_domain['id']."
    AND mailbox.id=stats_mail_user.mailbox_id
    AND mailbox.active < 9
    GROUP BY stats_mail_user.last_date, mailbox.id
    ORDER BY stats_mail_user.last_date DESC
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
      AND active < 9
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
      AND alias.active < 9
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
	// Action: Get the whole list of mailboxes 
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
    AND mailbox.active < 9
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
	// Call: can_add_mail_alias (int number_to_add)
	//
  function can_add_mail_alias($number_to_add = 1){
    if( 
        ( ( $this->quota['aliases'] - $this->used_quota['aliases'] ) >= $number_to_add )
      ||
        ( $this->quota['aliases'] == -1 )
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
        ( ($this->quota['mailboxes'] - $this->used_quota['mailboxes']) > 0 )
      ||
        ( $this->quota['mailboxes'] == -1 )
      ){return TRUE;}
      return FALSE;
  }

  //
	// fetch_policy
	// Action: fetch the policy of the domain
	// Call: fetch_policy ()
	//
  function fetch_policy(){

    $query = "
    SELECT policy.id
    FROM policy
    WHERE id='".$this->policy_id."'
    ";

    $result = $this->db_link->sql_query($query);
		$this->policy = $result['result'];


  }

	function domain_exist($domain_name){

		$query = "
    SELECT domain
    FROM domain
    WHERE domain='".$domain_name."'
    ";

    $result = $this->db_link->sql_query($query);
		debug_info("$query");
		if ($result['rows'] == 1){
			return TRUE;
		}
		return FALSE;

	}

	function create_domain($domain_array){

		global $CONF;
		global $server_info;
		global $PALANG;
		global $user_info;

		$error = 0;

		if ( $user_info->check_access('domain') ){

			if ( $user_info->can_add_item("1","domains") == FALSE ){
				$array['message'] = sprintf($PALANG['pMassive_import_domain_overquota_part'], sizeof($new_domains), $user_info->data_quota['domains'] - $user_info->data_managed['domains']);
				$array['message'] .= $PALANG['pMassive_import_domain_overquota']."<br/>";
				$error++;
			}

			if ( $user_info->can_add_item($domain_array['mailboxes'],"mailboxes") == FALSE ){
				$array['message'] .= sprintf($PALANG['pMassive_import_mailbox_allocation_overquota_part'], $new['mailboxes'], $user_info->data_quota['mailboxes'] - $user_info->data_managed['mailboxes'] );
				$array['message'] .= $PALANG['pMassive_import_mailbox_allocation_overquota']."<br/>";
				$error++;
			}

			if ( $user_info->can_add_item($domain_array['mailbox_aliases'],"aliases") == FALSE ){
				$array['message'] .= sprintf($PALANG['pMassive_import_aliases_allocation_overquota_part'], $new['aliases'], $user_info->data_quota['aliases'] - $user_info->data_managed['aliases'] );
				$array['message'] .= $PALANG['pMassive_import_aliases_allocation_overquota']."<br/>";
				$error++;
			}
	
			
		}

		if ( $error == 0 ){

			$query = "
      INSERT INTO domain (domain,description,aliases,mailboxes,maxquota,backupmx,antivirus,vrfydomain,vrfysender,greylist,spf,created,modified,active)
      VALUES ('".$domain_array['name']."','".$domain_array['description']."','".$domain_array['mailbox_aliases']."','".$domain_array['mailboxes']."','".$domain_array['maxquota']."','".$domain_array['backupmx']."','".$domain_array['antivirus']."','".$domain_array['vrfydomain']."','".$domain_array['vrfysender']."','".$domain_array['greylisting']."','".$domain_array['spf']."',NOW(),NOW(),'".$domain_array['active']."')";

			$result = $this->db_link->sql_query($query);


			if ($result['rows'] != 1){
				$array['result'] = 1;
				$array['message'] = $PALANG['pAdminCreate_domain_result_error'] . " <b>".$domain_array['name']."</b></br/>";
				$array['message'] .= "SQL : ".$result['sql_log']."</br/>";
			}
			else{
				$array['result'] = 0;
				$array['message'] = $PALANG['pAdminCreate_domain_result_succes'] . " <b>".$domain_array['name']."</b></br/>";
			}


			$this->fetch_by_domainname($domain_array['name']);
			$this->associate_domain_admin();

			if ( $CONF['greylisting'] == "YES" && $array['result'] == 0 ){
 
				if ( $server_info->check_server_role_exist('policy') ){

					$server_info->fetch_server_role_list('policy');

					for ( $i=0; $i < sizeof($server_info->list_server_role); $i++){

						$db_name = $server_info->list_server_role[$i]['instance'];
						$db_user = $server_info->list_server_role[$i]['login'];
						$db_pass = $server_info->list_server_role[$i]['password'];
						$db_port = $server_info->list_server_role[$i]['port'];
						$db_type = "mysql";
						$db_host = $server_info->list_server_role[$i]['ip_public'];
						if ( $server_info->list_server_role[$i]['ip_private'] != "") {$db_host = $server_info->list_server_role[$i]['ip_private'];}

						$policydb = new DB($db_name, $db_type, $db_host, $db_user, $db_pass, $db_port);
						$policy_info = new POLICYD($policydb);
					

						$policy_info_rcpt = $policy_info->add_new_domain_policy($this->data_domain['domain']);
						if ( $policy_info_rcpt['result'] != 1){
							$array['message'] .= $policy_info_rcpt['message'];
						}
						$array['result'] += $policy_info_rcpt['result'];
		
		
						$policy_info_helo = $policy_info->add_forbidden_helo($this->data_domain['domain']);
						if ( $policy_info_helo['result'] != 1){
							$array['message'] .= $policy_info_helo['message'];
						}
						$array['result'] += $policy_info_helo['result'];

					}

				}

			}

		}


		return $array;

	}

	function create_domain_policy($policy_array){
		
		global $CONF;
		global $PALANG;

		$query = "INSERT INTO policy (domain_id, virus_lover, spam_lover, banned_files_lover, bad_header_lover, bypass_virus_checks, bypass_spam_checks, bypass_banned_checks, bypass_header_checks, spam_modifies_subj, virus_quarantine_to, spam_quarantine_to, banned_quarantine_to, bad_header_quarantine_to, spam_tag_level, spam_tag2_level, spam_kill_level, spam_dsn_cutoff_level, addr_extension_virus, addr_extension_spam, addr_extension_banned, addr_extension_bad_header, warnvirusrecip, warnbannedrecip, warnbadhrecip, newvirus_admin, virus_admin, banned_admin, bad_header_admin, spam_admin, spam_subject_tag, spam_subject_tag2, message_size_limit, banned_rulenames)
    VALUES ('".$this->data_domain['id']."','".$policy_array['virus_lover']."','".$policy_array['spam_lover']."','".$policy_array['banned_files_lover']."','".$policy_array['bad_header_lover']."','".$policy_array['antivirus']."','".$policy_array['spam_lover']."','".$policy_array['bypass_banned_checks']."','".$policy_array['bypass_header_checks']."','Y','".$CONF['virus_quarantine_to']."','".$CONF['spam_quarantine_to']."','".$CONF['banned_quarantine_to']."','','".$CONF['sa_tag_level']."','".$CONF['sa_tag2_level'] ."','".$CONF['sa_kill_level'] ."','','','','','','N','N','N','','','','','','".$CONF['spam_subject_tag']."','".$CONF['spam_subject_tag2']."','','')";

		$result = $this->db_link->sql_query($query);

		if ($result['rows'] != 1){
			$array['result'] = 1;
			$array['message'] = $PALANG['pAdminCreate_domain_result_error2'] . " <b>".$this->data_domain['domain']."</b></br/>";
			$array['message'] .= "SQL : ".$result['sql_log']."</br/>";
		}
		else{
			$array['result'] = 0;
			$array['message'] = $PALANG['pAdminCreate_domain_result_succes'] . " <b>".$this->data_domain['domain']."</b></br/>";
		}

		return $array;

	}

  //
	// import_domains_list
	// Action: import a list of domain
	// Call: import_domains_list ($file)
	//
  function import_domains_list($new_domains){

		global $CONF;
		global $PALANG;
		global $mail_info;
		global $user_info;

		$array['message'] = "";

		foreach ($new_domains as $line_num => $line) {
			$info = explode(";", $line);

			$newDomain['name'] = chop($info[0]);
			$newDomain['backupmx'] = ( isset($info[1]) ) ? $info[1] : 0;
			$newDomain['mailboxes'] = ( isset($info[2]) ) ? $info[2] : $CONF['mailboxes'];
			$newDomain['mailbox_aliases'] = ( isset($info[3]) ) ? $info[3] : $CONF['aliases'];
			$newDomain['maxquota'] = ( isset($info[4]) ) ? $info[4]  : $CONF['maxquota'];
			$newDomain['description'] = ( isset($info[5]) ) ? $info[5] : "";
			$newDomain['antivirus'] = ( isset($info[6]) ) ? $info[6]  : 0;
			$newDomain['spamass'] = ( isset($info[7]) ) ? $info[7] : 0;
			$newDomain['vrfysender'] = ( isset($info[8]) ) ? $info[8] : 0;
			$newDomain['vrfydomain'] = ( isset($info[9]) ) ? $info[9] : 0;
			$newDomain['greylisting'] = ( isset($info[10]) ) ? $info[10] : 0;
			$newDomain['spf'] = ( isset($info[11]) ) ? $info[11] : 0;
			$newDomain['active'] = ( isset($info[12]) ) ? $info[12] : 0;
			$newDomain['default_aliases'] = ( isset($info[13]) ) ? $info[13] : 0;

			if ( $this->domain_exist($newDomain['name']) ){
				$array['result'] = 1;
				$array['message'] .= $newDomain['name'] . " : " . $PALANG['pAdminCreate_domain_domain_text_error'] . "<br/>";
				return $array;
			}
			else {

				if ($newDomain['backupmx'] == "1")
					{
						$newDomain['mailbox_aliases'] = 0;
						$newDomain['mailboxes'] = 0;
						$newDomain['maxquota'] = 0;
					}

				$result = $this->create_domain($newDomain);

				$array['message'] .= $result['message'];

				if ($result['result'] > 0){
					$array['result'] = $result['result'];
					return $array;
				}

				if ($newDomain['antivirus'] == 0 ){
					$amavis['antivirus'] = 'N';
					$amavis['virus_lover'] = 'Y';
					$amavis['banned_files_lover'] = 'Y';
					$amavis['bad_header_lover'] = 'Y';
					$amavis['bypass_banned_checks'] = 'Y';
					$amavis['bypass_header_checks'] = 'Y';
				}
				else{ 
					$amavis['antivirus'] = 'Y';
					$amavis['virus_lover'] = 'N';
					$amavis['banned_files_lover'] = 'N';
					$amavis['bad_header_lover'] = 'N';
					$amavis['bypass_banned_checks'] = 'N';
					$amavis['bypass_header_checks'] = 'N';
				}

				$amavis['spam_lover'] = ( $newDomain['spamass'] == 0 ) ? 'Y' : 'N';


				$result = $this->create_domain_policy($amavis);
				$array['message'] .= $result['message'];

				if ($result['result'] > 0){
					$array['result'] += $result['result'];
					return $array;
				}
				else
					{
						$this->fetch_policy_id();

						if ($newDomain['backupmx'] == 1)
							{
								$domain_addr = "@" . $newDomain['name'];
								$mail_info->add_mail_alias($domain_addr,$domain_addr);
							}
						if ( $newDomain['default_aliases'] == "1")
							{
								foreach ($CONF['default_aliases'] as $address=>$goto)
									{
										$address = $address . "@" . $newDomain['name'];
										$mail_info->add_mail_alias($address, $goto);
									}
							}
						$tMessage = $PALANG['pAdminCreate_domain_result_succes'] . "<br/>(".$newDomain['name'].")<br/>";
					}
			}

		}

		print $tMessage."<br />";
		return $array;


  }
  
	function associate_domain_admin(){

		global $PALANG;
		global $user_info;

		$query = "
    INSERT INTO domain_admins (accounts_id, domain_id, active)
    VALUES (".$user_info->data['id'].", ".$this->data_domain['id']." , 1 )
    ";

    $result = $this->db_link->sql_query($query);

		$array['message'] = "";

		if ($result['result'] > 0){
			$array['message'] = $PALANG['pAssociate_domain_admin_failed'];
			$array['message'] .= $result['sql_log'];
		}
		$array['result'] = $result['result'];

		return $array;
	
	}
  
	function delete_domain(){

		global $PALANG;
		global $CONF;
		global $server_info;
		global $user_info;

		$array['message'] = "";

		if ( $CONF['greylisting'] == "YES" ){
 
			if ( $server_info->check_server_role_exist('policy') ){

				$server_info->fetch_server_role_list('policy');

				for ( $i=0; $i < sizeof($server_info->list_server_role); $i++){

					$db_name = $server_info->list_server_role[$i]['instance'];
					$db_user = $server_info->list_server_role[$i]['login'];
					$db_pass = $server_info->list_server_role[$i]['password'];
					$db_port = $server_info->list_server_role[$i]['port'];
					$db_type = "mysql";
					$db_host = $server_info->list_server_role[$i]['ip_public'];
					if ( $server_info->list_server_role[$i]['ip_private'] != "") {$db_host = $server_info->list_server_role[$i]['ip_private'];}

					$policydb = new DB($db_name, $db_type, $db_host, $db_user, $db_pass, $db_port);
					$policy_info = new POLICYD($policydb);

					$policy_info_rcpt = $policy_info->remove_domain_policy($this->data_domain['domain']);
					if ( $policy_info_rcpt['result'] != 1){
						$array['message'] .= $policy_info_rcpt['message'];
					}
					$array['result'] = $policy_info_rcpt['result'];
		
		
					$policy_info_helo = $policy_info->remove_forbidden_helo($this->data_domain['domain']);
					if ( $policy_info_helo['result'] != 1){
						$array['message'] .= $policy_info_helo['message'];
					}
					$array['result'] += $policy_info_helo['result'];

				}

			}

		}


		$this->policy_message = $array['message'];

		$query = "DELETE FROM domain WHERE id=".$this->data_domain['id'];
    $this->sql_result = $this->db_link->sql_query($query,2);



	}

	//
	// generate_path
	// Action: Generates the path to mailbox, ftp...
	// Call: generate_path ()
	//
	function generate_path (){
		global $CONF;

		$directory = $this->data_domain['domain'][0] . "/" . $this->data_domain['domain'][1] . "/" . $this->data_domain['domain'][2];

			
		return $directory;

	}


  //
  //  fetch_domain_alias
  //  Action: fetch informations about domain alias
  //  Call: fetch_domain_alias (domain alias name)
  function fetch_domain_alias ($domain_alias_name) {

    $query = "
    SELECT *
    FROM domain_alias
    WHERE domain_id='".$this->data_domain['id']."'
      AND dalias='".$domain_alias_name."'
    ";

    $result = $this->db_link->sql_query($query);
    $this->domain_alias_status = 0;

    if ( $result['rows'] == 1 ){
      $this->domain_alias = $result['result'][0];
      $this->domain_alias_status = 1;
    }


  }


  //
  //  domain_alias_exist
  //  Action: check if a domain alias exist
  //  Call: domain_alias_exist (domain alias name)
  function domain_alias_exist ($domain_alias_name) {

    $query = "
    SELECT dalias
    FROM domain_alias
    WHERE domain_id='".$this->data_domain['id']."'
      AND dalias='".$domain_alias_name."'
    ";

    $result = $this->db_link->sql_query($query);
    
    if ( $result['rows'] == 1 ){
      return TRUE;
    }
    else{
      return FALSE;
    }


  }


  //
  //  add_domain_alias
  //  Action: add a new domain_alias
  //  Call: add_domain_alias (domain alias name)
  function add_domain_alias ($domain_alias_name) {

    global $PALANG;

    $domain_alias_name = strtolower($domain_alias_name);

    if ( empty($domain_alias_name) ){
      $this->msg['status'] = 0;
      $this->msg['text'] = $PALANG['pCreate_domain_alias_text_error1'];
      return false;
    }

    if ( ($this->domain_exist($domain_alias_name)) ){
      $this->msg['status'] = 0;
      $this->msg['text'] = $PALANG['pCreate_domain_alias_text_error2']."($domain_alias_name)";
      return FALSE;
    }

    if ( ($this->domain_alias_exist($domain_alias_name)) ){
      $this->msg['status'] = 0;
      $this->msg['text'] = $PALANG['pCreate_domain_alias_text_error3'];
      return false;
    }


    $query = "
    INSERT INTO domain_alias (dalias,domain_id,active,created)
    VALUES ('".$domain_alias_name."',".$this->data_domain['id'].",1,NOW())
    ";

    $result = $this->db_link->sql_query($query);
    if ( $result['rows'] == 1 ){
      $this->msg['status'] = 1;
      $this->msg['text'] = $PALANG['pCreate_domain_alias_result_success'];
    }
    else{
      $this->msg['status'] = 0;
      $this->msg['text'] = $PALANG['DB_Error']."//".$this->msg['status']; 
      return;
    }

    $query = "
    INSERT INTO alias (address,goto,policy_id,created,active)
    VALUES ('@".$domain_alias_name."','@".$this->data_domain['domain']."','".$this->data['policy_id']."',NOW(),'1')
    ";

    $result = $this->db_link->sql_query($query,2);

    if ( $result['rows'] == 1 ){
      $this->msg['status'] = 1;
      $this->msg['text'] = $PALANG['pCreate_domain_alias_result_success'];
      $this->db_link->db_log("create domain alias", "$domain_alias_name -> ".$this->data_domain['domain'],"'.$this->data_domain['domain'].'");
    }
    else{

      $query = "
      DELETE FROM domain_alias
      WHERE dalias = '".$domain_alias_name."'
        AND domain_id = ".$this->data_domain['id']."
      ";
      $result = $this->db_link->sql_query($query);

      $this->msg['status'] = 0;
      $this->msg['text'] = $PALANG['DB_Error']; 
    }

    return;

  }

  /**
   * This method check if the requested domain is under control of the user
   * 
   * @param int $destroy destroy the session if value = 1
   *
   */

  function check_user_access($destroy = 1){

    global $user_info;

    if ($user_info->rights['manage'] != 1){
      $query = "SELECT domain_id
      FROM domain_admins
      WHERE domain_admins.accounts_id = ".$user_info->data['id']."
      AND domain_admins.domain_id='".$this->data_domain['domain_id']."'";
      
      $result = $this->db_link->sql_query($query);
      if ( $result['rows'] == 0){
        if ( $destroy == 1 ){
          session_unset ();
          session_destroy ();
          header ("Location: ../login.php");
          exit;
        }
      }
    }
    

  }

}


?>