<?php

class MAIL
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
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


	function alias_change_active_status($value){
		$query = "UPDATE alias
    SET active=$value
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function alias_delete(){
		$query = "DELETE FROM alias
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	//
	// antispam_en_disable
	// Action: enable or disable antispam on mail alias
	// Call: antispam_en_disable (int domain_policy_id)
	//
	function antispam_en_disable($domain_policy_id){

		if ( isset($this->data_alias['policy_id']) && ($this->data_alias['policy_id'] == $domain_policy_id) ){
			
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
		$this->sql_result = $this->db_link->sql_query($query,2);
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
		$query = "DELETE FROM maibox
    WHERE username='".$this->data_mailbox['username']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function mailbox_fetch_quota_used(){

		$query = "SELECT * FROM stats_mailbox
    WHERE mailbox_id=".$this->data_mailbox['id']."
    ORDER BY date DESC
    LIMIT 1";
		$result = $this->db_link->sql_query($query,2);
		$this->mailbox_quota_used = 0;
		if ( $result['rows'] > 0 ){
			$this->mailbox_quota_used = $result['result'][0]['size'];
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
	// add_mail_alias
	// Action: create a new mail alias
	// Call: add_mail_alias (string alias, string email_to, int greylisting)
	//
  function add_mail_alias($alias, $email_to, $greylisting=2){
    
    GLOBAL $CONF;
    GLOBAL $PALANG;

    $domain_policy = get_domain_policy($domain);
    $message = "";
    
    if ( check_policyhosting() ){
      
      if ( $greylisting == 2 && $CONF['greylisting'] == 'YES' )
        { $greylisting = 1;}
      else 
			{ $greylisting = 0;}
		}
		
		$error = 0;
		

    if (!preg_match ('/@/',$to)) $to = $to . "@" . $domain;
    if (!preg_match ('/@/',$from)) $from = $from . "@" . $domain;
    
    if (empty ($from) or !check_email ($from))
    {
      $error = 1;
      $message .= $PALANG['pCreate_alias_address_text_error1']." $from";
    }
    
    if (preg_match ('/^\*@(.*)$/', $to, $match)) $to = "@" . $match[1];
    
    
    if (empty ($to) or !check_email ($to))
    {
      $error = 1;
      $message .= $PALANG['pCreate_alias_goto_text_error']. " $to";
    }
    
    
    
    if ( $error == 0 ){
      
      if ( check_policyhosting() &&  (!preg_match ('/^@/',$from)) ){
        $result = db_query("INSERT INTO policy(_rcpt,_optin,_priority) VALUES ('".$from."','".$greylisting."','50')","1","policyd");
        if ($result['rows'] != 1){
          $message .= $PALANG['pCreate_alias_policy_fail'] . "<br />($from)<br />\n";
        }
        else{
          $message .= $PALANG['pCreate_alias_policy_ok'] . "<br />($from)<br />\n";
        }
        
      }
      
      
      $result = db_query ("SELECT * FROM alias WHERE address='$from' AND goto='$to' AND policy_id='".$domain_policy['id']."'");
      if ($result['rows'] == 0 )
      {
        
        $result = db_query ("INSERT INTO alias (address,goto,policy_id,created,active) VALUES ('$from','$to','".$domain_policy['id']."',NOW(),'1')");
        if ($result['rows'] != 1)
        {
          $error = 1;
          $message .= "<br />" . $PALANG['pCreate_alias_result_error'] . "<br />($from -> $to)</br />";
        }
        else
        {
          $error = 0;
          $message .= "<br />" . $PALANG['pCreate_alias_result_succes'] . "<br />($from -> $to)</br />";
          db_log ($SESSID_USERNAME, "$domain", "create alias", "$from -> $to");
        }
      }
      else
      {
        $error = 1;
        $message .= "<br />" . $PALANG['pCreate_alias_result_error_exist'] . "<br />($from -> $to)</br />";
      }
      
    }
    $array['status'] = $error;
    $array['message'] = $message;
    
    return $array;

  }


}

?>