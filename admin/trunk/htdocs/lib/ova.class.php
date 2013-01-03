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
				
				if ( (preg_match('/([0-9]*)\.sql$/',$file,$result_match)) && (preg_replace("/.sql/","",$result_match[0]) > $this->running_db) ) $this->sql_files[]=$result_match[0];

			}
		sort($this->sql_files);
		$this->sql_files[]="fk.sql";

	}

	function show_latest_sql(){

		foreach($this->sql_files as $file)
			{
				print 'File : '.$file.'<br/>';
			}

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
								$buf = preg_replace('~[[:cntrl:]]~', '', fgets($fp, 4096));
								
								// Ajout ligne si non commentaire
								if ((! eregi('^--',$buf)) && (! eregi('^#',$buf)) && (! eregi('^ ',$buf)) && (! eregi('^\n\r',$buf))){
									$buffer .= $buf." ";
								}
								//          print $buf.'<br>';
								
								
								if (eregi(';',$buffer))
									{
										// Found new request
										$arraysql[]=trim($buffer);
										$buffer='';
									}
							}
						if ($buffer && !empty($buffer)) $arraysql[]=trim($buffer);
						fclose($fp);
					}

				foreach($arraysql as $sql)
					{

						$this->sql_result = $this->db_link->sql_query($sql,2);
						if ( $this->sql_result['return_code'] != 200 ){
							print('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
							die('upgrade: Failed to execute SQL request : '.$sql."\n<br/><br/>");
						}
					}

				if ( ($this->sql_query['return_code'] != 200) && ($file != "fk.sql") ){
					$this->update_version(preg_replace("/.sql/","",$file));
				}

			}

	}


	function do_log ($domain_id,$action,$data, $domain2="")
	{
		global $CONF;
		global $user_info;

		$REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];


		if ( $CONF['logging'] == 'YES' ) {

				$query = "INSERT INTO log (accounts_id, domain_id, domain_name, ip, action, data)
        VALUES ('".$user_info->data['id']."','".$domain_id."', '$domain2', '$REMOTE_ADDR','$action','$data')";
				$result = $this->db_link->sql_query($query);
	 
		}

	}


	//
	// generate_password
	// Action: Generates a random password
	// Call: generate_password ()
	//
	function generate_password (){
		global $CONF;

		if ($CONF['generate_password'] == "YES"){
			if ($CONF['password_generator'] == ""){	$password = substr (md5 (mt_rand ()), 0, $CONF['generate_password_length']);	}
			else {	$password = exec($CONF['password_generator']); }
		}

		return $password;

	}


}