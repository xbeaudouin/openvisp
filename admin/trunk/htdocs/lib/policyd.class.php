<?php

class POLICYD
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}

	function add_new_domain_policy ($domain_name, $greylisting=1){
		global $PALANG;

		$query = "INSERT INTO policy(_rcpt,_optin,_priority)
VALUES ('".$domain_name."','".$greylisting."','10')";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 1){
			$array['return'] = 1;
			$array['message'] = $PALANG['pCreate_domain_policy_fail'] . "($domain_name) ".$result['sql_log']."<br/><br/>\n";
			return $array;
		}

		$array['result'] = 0;
		$array['message'] = $PALANG['pCreate_domain_policy_ok'] . "($domain_name)<br/><br/>\n";
		return $array;


	}

	function add_new_policy ($alias, $greylisting=1, $priority="50"){

		global $PALANG;

		$query = "INSERT INTO policy(_rcpt,_optin,_priority)
VALUES ('".$alias."','".$greylisting."','".$priority."')";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 1){
			$array['result'] = 1;
			$array['message'] = $PALANG['pCreate_alias_policy_fail'] . "($alias) ".$result['sql_log']."<br/><br/>\n";
			return $array;
		}

		$array['result'] = 0;
		$array['message'] = $PALANG['pCreate_alias_policy_ok'] . "($alias)<br/><br/>\n";
		return $array;

	}

	function remove_policy ($alias){

		GLOBAL $PALANG;

		$query = "DELETE FROM policy WHERE _rcpt = '".$alias."'";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 1){
			$array['status'] = 1;
			$array['message'] = $PALANG['pDelete_alias_policy_fail'] . "($alias) ".$result['sql_log']."<br/>\n";
			return $array;
		}

		$array['status'] = 0;
		$array['message'] = $PALANG['pDelete_alias_policy_ok'] . "($alias)<br/>\n";
		return $array;

	}

	function search_policy ($alias){

		$query = "SELECT _rcpt FROM policy WHERE _rcpt = '".$alias."'";
		$result = $this->db_link->sql_query($query,2);
		return $result;

	}


	function remove_domain_policy($domain_name){

		GLOBAL $PALANG;

		$query = "SELECT COUNT(_rcpt) as total_rcpt FROM policy WHERE _rcpt like '%".$domain_name."'";
		$result_rcpt = $this->db_link->sql_query($query);

		$array['result'] = 0;
		$array['message'] = "";

		if ( $result_rcpt['rows'] > 0 ){

			$query = "DELETE FROM policy WHERE _rcpt like '%".$domain_name."'";

			$result = $this->db_link->sql_query($query,2);

			if ( $result['rows'] != $result_rcpt['result'][0]['total_rcpt']){
				$array['result'] = 1;
				$array['message'] = $PALANG['pDelete_domain_policy_fail'] . "($domain_name) ".$result['sql_log']."<br/>\n";
				return $array;
			}

			$array['result'] = 0;
			$array['message'] = $PALANG['pDelete_domain_policy_ok'] . "($domain_name)<br/>\n";

		}

		return $array;


	}

	function remove_forbidden_helo($domain_name){

		GLOBAL $PALANG;

		$query = "SELECT COUNT(_helo) as total_helo FROM blacklist_helo WHERE _helo like '%".$domain_name."'";
		$result_helo = $this->db_link->sql_query($query);

		$array['result'] = 0;
		$array['message'] = "";

		if ( $result_helo['rows'] > 0 ){

			$query = "DELETE FROM blacklist_helo WHERE _helo like '%".$domain_name."'";

			$result = $this->db_link->sql_query($query,2);

			if ( $result['rows'] != $result_helo['result'][0]['total_helo']){
				$array['result'] = 1;
				$array['message'] = $PALANG['pPolicyd_remove_helo_failed'] . "($domain_name) ".$result['sql_log']."<br/>\n";
				return $array;
			}

			$array['result'] = 0;
			$array['message'] = $PALANG['pPolicyd_remove_helo_success'] . "($domain_name)<br/>\n";
		}

		
		return $array;

		
	}

	function add_forbidden_helo($domain_name){

		GLOBAL $PALANG;

		$query = "INSERT INTO blacklist_helo(_helo)
VALUES ('".$domain_name."'),('pop.".$domain_name."'),('smtp.".$domain_name."'),
('mail.".$domain_name."'),('imap.".$domain_name."')";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 5){
			$array['result'] = 1;
			$array['message'] = $PALANG['pPolicyd_insert_helo_failed'] . "($domain_name) ".$result['sql_log']."<br/>\n";
			return $array;
		}

		$array['result'] = 0;
		$array['message'] = $PALANG['pPolicyd_insert_helo_success'] . "($domain_name)<br/>\n";
		
		return $array;

	}

   function __destruct() {
	 }

}

?>