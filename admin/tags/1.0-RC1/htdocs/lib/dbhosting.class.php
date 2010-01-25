<?php

class DBHOSTING
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}


	function fetch_info_by_id ($db_id)
	{
		$query = "SELECT * FROM dbname WHERE id='$db_id'";
		$result = $this->db_link->sql_query($query);
		$this->data = $result['result'][0];
		$this->total_row = $result['rows'];
		$this->table_from = "dbname";
	}




}



?>