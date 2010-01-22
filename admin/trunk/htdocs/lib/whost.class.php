<?php

require_once ("domain.class.php");

class WHOST extends DOMAIN
{

	function __construct($db_link,$domain_name)
	{
		$this->db_link = $db_link;
		$this->fetch_by_domainname($domain_name);
	}

	function fetch_by_whost($whost)
	{
		$query = "SELECT * FROM whost WHERE domain_id='".$this->data_domain['id']."' AND vhost='$whost'";
		$result = $this->db_link->sql_query($query);

		$this->data_whost_row = $result['rows'];

		if ( $this->data_whost_row > 0 )
			{
				$this->data_whost = $result['result'][0];
			}
		else
			{
				$this->data_whost = "";
			}

	}

	function add_whost($userinfo, $vhost)
	{
		global $CONF;

		//		$query = "SELECT * FROM whost WHERE domain_id='$domain_id' AND id='$whost'";

		if ( $this->used_quota['total_whost'] >= $this->data_domain['whost_quota'] ){
			throw new Exception ("Sorry no more Webhosting quota left");
		}

		$query = "INSERT INTO whost(vhost, domain_id, DocumentRoot, active, paid,created)
VALUES ('$vhost','".$this->data_domain['id']."','".$CONF['storage']."/".$this->hash_storage."/".$vhost."',1,1,NOW())
;";

		$result = $this->db_link->sql_query($query);

		if ( PEAR::isError($result) )
			{
				$this->addstatus = 0;
			}
		else
			{
				$this->addstatus = 1;
				$this->db_link->log($this->data_domain['id'],$this->data_domain['domain'], $userinfo,"Website $vhost.".$this->domain_name." has been added");
			}



	}


}


?>