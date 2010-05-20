<?php

class MAIL
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
		$this->message = "";
	}


	function fetch_alias_info($alias){
		$query = "SELECT * FROM alias WHERE address='$alias'";
		$result = $this->db_link->sql_query($query,2);
		$this->data_alias = $result['result'][0];
	}

	function alias_en_disable(){
		$query = "UPDATE alias
    SET active=1-active
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function check_alias_not_exist($alias){
		$query = "SELECT * FROM alias WHERE address='$alias'";
		$result = $this->db_link->sql_query($query,2);
		if ( $result['rows'] == 0){
			return TRUE;
		}
		return FALSE;
	}


	function alias_change_active_status($value){
		$query = "UPDATE alias
    SET active=$value
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function alias_delete(){

    global $CONF;
    global $PALANG;
		global $user_info;
		global $domain_info;
		global $ova;
		global $server_info;


		$query = "UPDATE alias SET active = 9
    WHERE address='".$this->data_alias['address']."'";
		$result = $this->db_link->sql_query($query,2);

		$array['status'] = $result['rows'];
		$array['message']="";

		if ($result['rows'] == 0){
			$array['message'] .= $PALANG['pDelete_alias_fail'];

		}
		else{
			$array['message'] .= $PALANG['pDelete_alias_ok'];
			
					if ( $CONF['greylisting'] == "YES" ){
 
						if ( $server_info->check_server_role_exist('policy') && (!preg_match ('/^@/',$this->data_alias['address'])) ){

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
								$policy_server = new POLICYD($policydb);
								$policy_search_result = $policy_server->search_policy($this->data_alias['address']);

								if ( $policy_search_result['rows'] == 1 ){
									$policy_result = $policy_server->remove_policy($this->data_alias['address']);
									if ( $policy_result['status'] == 0 ){
										$array['status'] = $policy_result['status'];
										$array['message'] .= $policy_result['message'];
										return $array;
									}

									$array['message'] .= $policy_result['message'];
								}
							}

						}

					}

		}

	}

	//
	// antispam_en_disable
	// Action: enable or disable antispam on mail alias
	// Call: antispam_en_disable (int domain_policy_id)
	//
	function antispam_en_disable($domain_policy_id){

		if ( $this->data_alias['policy_id'] == $domain_policy_id) {
			
			$policy_id_value = 1;
		}
		else{
			$policy_id_value = $domain_policy_id;
		}

		if ( isset($this->data_alias['address']) ){
			$alias_id = $this->data_alias['address'];
		}
		else{
			$alias_id = $this->data_mailbox['username'];
		}

		$query = "UPDATE alias
    SET policy_id = $policy_id_value
    WHERE address='".$alias_id."'";
		debug_info("AS MB : $query");
		$this->sql_result = $this->db_link->sql_query($query);
	}



	function fetch_mailbox_info($mailbox){
		$query = "SELECT mailbox.*, alias.policy_id as policy_id
FROM mailbox, alias
WHERE username='$mailbox'
AND mailbox.username=alias.address
";
		$result = $this->db_link->sql_query($query);
		$this->data_mailbox = $result['result'][0];
	
	}

	function mailbox_en_disable(){
		$query = "UPDATE mailbox
    SET active=1-active
    WHERE username='".$this->data_mailbox['username']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function mailbox_paid(){
		$query = "UPDATE mailbox
    SET paid=1-paid
    WHERE username='".$this->data_mailbox['username']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}


	function mailbox_change_active_status($value){
		$query = "UPDATE mailbox
    SET active=$value
    WHERE username='".$this->data_mailbox['username']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function mailbox_delete(){

		// Deletion of the associated alias
		$this->fetch_alias_info($this->data_mailbox['username']);
		$this->alias_delete();

		$query = "UPDATE mailbox SET active = 9
    WHERE username='".$this->data_mailbox['username']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function mailbox_fetch_quota_used(){

		$query = "SELECT mailbox_size FROM stats_mail_user
    WHERE mailbox_id=".$this->data_mailbox['id']."
    ORDER BY last_date DESC
    LIMIT 1";
		$result = $this->db_link->sql_query($query,2);
		$this->mailbox_quota_used = 0;
		if ( $result['rows'] > 0 ){
			$this->mailbox_quota_used = $result['result'][0]['mailbox_size'];
		}

	}


	function fetch_spam_key(){
		$query = "SELECT spamreport.id, spamreport.key2, spamreport.created
    FROM spamreport
    WHERE spamreport.mailbox_id='".$this->data_mailbox['id']."'";
		$result = $this->db_link->sql_query($query);
		if ( $result['rows'] == 1 ){
			$this->data_mailbox['spam_key'] = $result['result'][0]['id'];
			$this->data_mailbox['spam_key2'] = $result['result'][0]['key2'];
		}
		else{
			$this->data_mailbox['spam_key'] = NULL;
			$this->data_mailbox['spam_key2'] = NULL; 
		}
	}

	function fetch_vacation_info(){
		$query = "SELECT vacation.*
    FROM vacation, mailbox
    WHERE vacation.mailbox_id=".$this->data_mailbox['id']." AND vacation.active = 1 ";
		$result = $this->db_link->sql_query($query);
		if ( $result['rows'] == 1 ){
			$this->data_mailbox['vacation_status'] = $result['result'][0]['active'];
			$this->data_mailbox['vacation_date_modified'] = $result['result'][0]['modified'];
			$this->data_mailbox['vacation_subject'] = $result['result'][0]['subject'];
			$this->data_mailbox['vacation_body'] = $result['result'][0]['body'];
		}
		else{
			$this->data_mailbox['vacation_status'] = "";
			$this->data_mailbox['vacation_date_modified'] = "";
			$this->data_mailbox['vacation_subject'] = "";
			$this->data_mailbox['vacation_body'] = "";
		}
	}

	function vacation_en_disable(){
		$query = "UPDATE vacation
    SET active=1-active
    WHERE vacation.mailbox_id=".$this->data_mailbox['id']."";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function fetch_forward_state(){
		$query = 	"SELECT *
    FROM alias
    WHERE address='".$this->data_mailbox['username']."'
    AND goto='".$this->data_mailbox['username']."'
    AND active='1'";
		$result = $this->db_link->sql_query($query);
		if ($result['rows'] == 1){
			$this->data_mailbox['forwarded'] = 0;
		}
		else{
			$this->data_mailbox['forwarded'] = 1;
		}

	}

	//
	// check_email_struct
	// Action: check if email is valid and returns TRUE if this is the case.
	// Call: check_email (string email)
	//
	function check_email_struct ($email)
	{

		GLOBAL $PALANG;

		$regexp  = '/';
		$regexp .= '^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4}$';
		$regexp .= '|';
		$regexp .= '^[-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4}';
		$regexp .= '(, [-!#$%&\'*+\\.\/0-9=?A-Z^_{|}~]+' . '@' . '([-0-9A-Z]+\.)+' . '([0-9A-Z]){2,4})+';
		$regexp .= '$';
		$regexp .= '/i';
		if (preg_match ( $regexp, trim ($email)))
			{
				return TRUE;
			}
		else
			{
				return FALSE;
			}
	}


  //
	// add_mail_alias
	// Action: create a new mail alias
	// Call: add_mail_alias (string alias, string email_to, int greylisting)
	//
  function add_mail_alias($alias, $email_to, $greylisting=1){
    
    global $CONF;
    global $PALANG;
		global $user_info;
		global $domain_info;
		global $ova;
		global $server_info;

    $message = "";
    
		$error = 0;
		

    if (!preg_match ('/@/',$email_to)) $email_to = $email_to . "@" . $domain_info->data_domain['domain'];
    if (!preg_match ('/@/',$alias)) $alias = $alias . "@" . $domain_info->data_domain['domain'];
    
    if (empty ($alias) or !($this->check_email_struct ($alias)) )
    {
      $error = 1;
      $array['message'] .= $PALANG['pCreate_alias_address_text_error1']." $alias";
    }
    
    if (preg_match ('/^\*@(.*)$/', $email_to, $match)) $email_to = "@" . $match[1];
    
    
    if (empty ($email_to) or !($this->check_email_struct ($email_to)) )
    {
      $error = 1;
      $array['message'] .= $PALANG['pCreate_alias_goto_text_error']. " $email_to";
    }


    if ( $error == 0 ){

			if ( $this->check_alias_not_exist($alias) ){

				$query = "INSERT INTO alias(address,goto,policy_id,domain_id,created,active)
        VALUES ('$alias','$email_to','".$domain_info->data['policy_id']."',".$domain_info->data_domain['id'].",NOW(),'1')";
				$result = $this->db_link->sql_query($query);

        if ($result['rows'] != 1){
          $error = 1;
          $array['message'] .= "<br/>" . $PALANG['pCreate_alias_result_error'] . "<br/>($alias -> $email_to)</br/>";
          $array['message'] .= "SQL : ".$result['sql_log']."// ".$result['rows']."</br/>";
        }
        else {

					$array['message'] .= "<br/>" . $PALANG['pCreate_alias_result_succes'] . "<br />($alias -> $email_to)</br />";
          $ova->do_log ($domain_info->data_domain['id'], "create alias", "$alias -> $email_to");

					if ( $CONF['greylisting'] == "YES" ){
 
						if ( $server_info->check_server_role_exist('policy') && (!preg_match ('/^@/',$alias)) ){

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
								$policy_server = new POLICYD($policydb);
								$policy_result = $policy_server->add_new_policy($alias, $greylisting);

								if ( $policy_result['status'] == 1 ){
									$array['status'] = $policy_result['status'];
									$array['message'] .= $policy_result['message'];
									return $array;
								}

								$array['message'] .= $policy_result['message'];

							}

						}

					}

        }

			}
			else{
        $error = 1;
        $array['message'] .= "<br />" . $PALANG['pCreate_alias_result_error_exist'] . "<br />($alias -> $email_to)</br />";
			}
      

      
    }
    $array['status'] = $error;
    $array['message'] = $message;
    
    return $array;

  }


}

?>