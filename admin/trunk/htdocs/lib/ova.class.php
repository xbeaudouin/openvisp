<?php

class OVA
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}


	//
	// fetch_running_version
	// Action: fetch from database the OVA version
	// Call: fetch_running_version()
	//

	function fetch_running_version(){
		$query = "SELECT * FROM ovavers";
		$this->sql_result = $this->db_link->sql_query($query);
		$this->running_version = $this->sql_result['result'][0]['ova'];
		$this->running_db = $this->sql_result['result'][0]['query'];
	}


	// fetch_latest_version
	// Action: fetch the latest version installed on disk
	// Call: fetch_latest_version

	function fetch_latest_version(){
		$fp = fopen($this->directory_update."latest_version.txt","r");
		if ($fp)
			{
				$buf = "";
				while (!feof ($fp))
					{
						$buf .= fgets($fp, 4096);
					}
				fclose($fp);
				$this->latest_version = chop($buf);
			}
	}

	function fetch_latest_sql(){

		$handle = opendir($this->directory_update);
		$this->sql_files = array();
		while (($file = readdir($handle)) !== false) 
			{
				
				if ( (eregi ('\.sql$',$file)) && (preg_replace("/.sql/","",$file) > $this->running_db) ) $this->sql_files[]=$file;

			}
		sort($this->sql_files);


	}


	function update_version($query){
		$query = "UPDATE ovavers SET query=$query";
		$this->sql_result = $this->db_link->sql_query($query);
	}


	function apply_latest_sql(){

		foreach($this->sql_files as $file)
			{
				print 'File : '.$file.'<br/>';
	
				$name = substr($file, 0, strlen($file) - 4);
				$buffer = '';
				$arraysql = Array();
				$fp = fopen($this->directory_update.$file,"r");
				if ($fp)
					{
						while (!feof ($fp))
							{
								$buf = fgets($fp, 4096);
								
								// Ajout ligne si non commentaire
								if ((! eregi('^--',$buf)) && (! eregi('^#',$buf)))  $buffer .= $buf;
								//          print $buf.'<br>';
								
								if (eregi(';',$buffer))
									{
										// Found new request
										$arraysql[]=trim($buffer);
										$buffer='';
									}
							}
						
						if ($buffer) $arraysql[]=trim($buffer);
						fclose($fp);
					}

				foreach($arraysql as $sql)
					{

						debug_info("UPG : $sql");
						$this->sql_result = $this->db_link->sql_query($sql,2);
						if ( $this->sql_result['return_code'] != 200 ){
							print('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
							die('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
						}
					}

				if ( $this->sql_query['return_code'] != 200 ){
					$this->update_version(preg_replace("/.sql/","",$file));
				}

			}

	}


}