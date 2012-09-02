<?php

class DB
{

	private $connect;
	private $data;
	private $debug_text;
	private $debug;
	private $db_host;
	private $db_port;
	private $db_user;
	private $db_pass;
	private $database;


	//
	// DB
	//
	// Action make a connection to the database with information supplied in config.inc.php
	// if other information are supplied in parameter, it will override defaults one.
	//
	function __construct ($database="", $db_type="", $db_host="", $db_user="", $db_pass="", $db_port="")
	{

		global $CONF;
		global $DEBUG_TEXT;
		global $DEBUG_TEXT_TXT;

		$this->debug_text = $DEBUG_TEXT;
		$this->debug_text_txt = $DEBUG_TEXT_TXT;

		$this->debug = $CONF['SQL_DEBUG'];

		if ( $db_host == "" ) { $db_host = $CONF['database_host']; }
		if ( $db_port == "" ) { $db_port = $CONF['database_port']; }
		if ( $db_user == "" ) { $db_user = $CONF['database_user']; }
		if ( $db_pass == "" ) { $db_pass = $CONF['database_password']; }
		if ( $db_type == "" ) { $db_type = $CONF['database_type']; }
		if ( $database == "" ) { $database = $CONF['database_name']; }

		if ( $database == "" )
			{
				$database = $CONF['database_name'];
				$db_host = $CONF['database_host'];
				$db_user = $CONF['database_user'];
				$db_pass = $CONF['database_password'];
				$db_type = $CONF['database_type'];
			}

		$this->sql_param['db_type'] = $db_type;
		$this->sql_param['db_host'] = $db_host;
		$this->sql_param['db_name'] = $database;

		$this->connect =& MDB2::factory($db_type."://".$db_user.":".$db_pass."@".$db_host.":".$db_port."/".$database."?new_link=true");
		$this->connect->setFetchMode(MDB2_FETCHMODE_ASSOC);

		if (PEAR::isError($this->connect))
			{
				die($this->connect->getMessage());
			}
	 
	}


	//
	// sql_query
	// Action: Sends a query to the database and returns query result as a row and number of rows
	// Call: db_query (string query, int die, pear:mdb2 link)
	// int die should have
	//    0= don't die on error 
	//    1= die on error and send debug test (DEFAULT)
	//    2= don't die, but throw error
	//

	function sql_query ($query, $die_on_error=1)
	{


		$result = "";
		$number_rows = "";
		$row_results = "";
		$sql_log = "";
		$return_code = "200";

		if ( $this->debug == "YES" ) { 	file_put_contents('php://stderr', "SQL DEBUG OVA \n\n$query \n\n"); }

		if (eregi ("^select", $query)){
			$result =& $this->connect->query($query);
		}
		else{
			$result =& $this->connect->exec($query);
		}

		if ( $die_on_error == 1 && PEAR::isError($result) )
			{
			 die ("<p />DEBUG INFORMATION:<br />Invalid query: " . $result->getMessage() . " // " . $result->getDebugInfo() . "<br/>Query on (".$this->sql_param['db_name']."/".$this->sql_param['db_host'].")<b>\"$query\"</b><br/>".$this->debug_text );
			}

		if ( $die_on_error == 2 && PEAR::isError($result) )
			{
				$return_code = "500"; 
				//print ("<p />SQL Query Failed <br /> query: " . $result->getMessage() . "<br/>Query <b>\"$query\"</b><br/>".$this->debug_text );
				$sql_log = "SQL Query Failed\n" . $result->getMessage() . "\n<br/>Query :\n\"$query\"\n<br/>".$this->debug_text_txt."<br/>";
				$sql_log .= "DB TYPE : ".$this->sql_param['db_type']."<br/>";
				$sql_log .= "DB HOST : ".$this->sql_param['db_host']."<br/>";
				$sql_log .= "DB NAME : ".$this->sql_param['db_name']."<br/>";

			}

		if (eregi ("^select", $query))
			{ 
				while (($row = $result->fetchRow()))
					{	$row_results[] = $row; }
				$number_rows = $result->numRows();
			}
		else
			{ 
				$row_results[] = "";
				$number_rows = $result;
			}
		

		if ( $this->debug == "YES" ) { 	file_put_contents('php://stderr', "SQL DEBUG OVA ".$number_rows." result(s) \n"); }
		
		$return = array (
										 "result" => $row_results,
										 "rows" => $number_rows,
										 "sql_log" => $sql_log, 
										 "return_code" => $return_code
										 );
		return $return;

	}

	function log ($domain_id, $domain, $userinfo, $text, $data="")
	{

		global $CONF;

		if ( $CONF['logging'] == 'YES' )
			{
				$query = "INSERT INTO log (accounts_id, domain_id, domain_name, ip, action, data)
VALUES ('".$userinfo->data['id']."','$domain_id', '$domain', '".$userinfo->remote_host."','$text','$data')";
				
				$result = $this->sql_query($query);

				if ( PEAR::isError($result) )
					{
						die("Impossible d'ajouter la log");
					}

		}


	}


	//
	// update_record
	// Action: Update a result array from a db->sql_query object with new value
	// before the update we fetch the old value from the table_from with the id.
	// All column's data that are different are used to generate the update sql order.
	// Call: update_record ( array newdata, string table_from)
	//

	function update_record ( $newdata, $table_from )
	{

		$old_record_obj = $this->sql_query("SELECT * FROM $table_from WHERE ID='".$newdata['id']."'");
		$old_record = $old_record_obj['result'][0];


		$update_query = "UPDATE $table_from SET";
		$update_num = 0;

		foreach ($newdata as $key => $value)
			{

				if ( $key != 'id' &&  ( $newdata[$key] != $old_record[$key] ) )
					{
						if ( $update_num > 0 ){ $update_query .= ", "; }
						$update_query .= " $key='".$newdata[$key]."'";
						$update_num++;
					}
			}

		$update_query .= " WHERE id='".$newdata['id']."'";

		$result =& $this->connect->exec($update_query);
		if (PEAR::isError($result)) {
    	return ($result->getMessage());
		}
		return "1";
	}

	function __destruct() {
	}


}

?>