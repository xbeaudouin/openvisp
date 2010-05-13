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

	function en_disable(){
		$query = "UPDATE alias
    SET active=1-active
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}


	function change_active_status($value){
		$query = "UPDATE alias
    SET active=$value
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
	}

	function delete(){
		$query = "DELETE FROM alias
    WHERE address='".$this->data_alias['address']."'";
		$this->sql_result = $this->db_link->sql_query($query,2);
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