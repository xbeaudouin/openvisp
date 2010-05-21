<?php

class SERVER
{

	protected $db_link;

	function __construct ($db_link)
	{
		$this->db_link = $db_link;
	}

	function check_server_role_exist($role_name){

		$query = "SELECT count(*) as total_server
FROM server_jobmodel, server_job
WHERE server_jobmodel.role='".$role_name."'
AND server_jobmodel.id=server_job.server_jobmodel_id
AND server_job.active = 1";

		$result = $this->db_link->sql_query($query);
		if ( $result['result'][0]['total_server'] > 0 ){
			return TRUE;
		}
		return FALSE;

	}

	function fetch_server_role_list($role_name){

		$query = "SELECT server.name, server_job.login, server_job.password, server_job.port, server_ip.public as ip_public, server_ip.private as ip_private, server_apps.apps, server_job.instance
FROM server_jobmodel, server_job, server_ip, server_apps, server
WHERE server_jobmodel.role='".$role_name."'
AND server_jobmodel.id=server_job.server_jobmodel_id
AND server_job.active = 1
AND server_job.server_ip_id = server_ip.id
AND server_job.server_apps_id = server_apps.id
AND server_job.server_id = server.id";

		$result = $this->db_link->sql_query($query);

		$this->list_server_role = $result['result'];

	}



}

?>