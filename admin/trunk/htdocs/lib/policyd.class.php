<?php

class POLICYD
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}

	function add_new_policy ($alias, $greylisting, $priority="50"){

		GLOBAL $PALANG;

		$query = "INSERT INTO policy(_rcpt,_optin,_priority)
VALUES ('".$alias."','".$greylisting."','".$priority."')";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 1){
			$array['status'] = 1;
			$array['message'] = $PALANG['pCreate_alias_policy_fail'] . "($alias) ".$result['sql_log']."<br/><br/>\n";
			return $array;
		}

		$array['status'] = 0;
		$array['message'] = $PALANG['pCreate_alias_policy_ok'] . "($alias)<br/><br/>\n";
		return $array;

	}

	function remove_policy ($alias){

		GLOBAL $PALANG;

		$query = "DELETE FROM policy WHERE _rcpt = '".$alias."'";

		$result = $this->db_link->sql_query($query,2);

		if ( $result['rows'] != 1){
			$array['status'] = 1;
			$array['message'] = $PALANG['pDelete_alias_policy_fail'] . "($alias) ".$result['sql_log']."<br/><br/>\n";
			return $array;
		}

		$array['status'] = 0;
		$array['message'] = $PALANG['pDelete_alias_policy_ok'] . "($alias)<br/><br/>\n";
		return $array;

	}

	function search_policy ($alias){

		$query = "SELECT _rcpt FROM policy WHERE _rcpt = '".$alias."'";
		$result = $this->db_link->sql_query($query,2);
		return $result;

	}





}

?>