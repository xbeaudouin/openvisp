<?php
//
// File: datacenter.inc.php
//
// Functions for datacenter work
// To be added after function.inc.php
//

if (ereg ("datacenter.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../login.php");
   exit;
}

//
// check_datacenter_session
// Action: Check if an admin session already exists. If not redirect to login.php
//         if it is not logged, otherwise logou the user.
// Call: check_datacenter_session()
function check_datacenter_session() {
  $SESSID_USERNAME = check_user_session();
  // check datacenter admin is in function.inc.php because of the need of homepage
  if (!check_datacenter_admin($SESSID_USERNAME)) {
    header ("Location: logout.php");
    exit;
  }

  return $SESSID_USERNAME;
}

// 
// check_datacenter_full_session
// Action: Check if an admin has full access to datacenter part (eg big admin). If not redirect
//         to logout.php
// Call: check_datacenter_full_session()
function check_datacenter_full_session() {
  $SESSID_USERNAME = check_user_session();
  // check datacenter admin is in function.inc.php because of the need of homepage
  if (!check_datacenter_admin($SESSID_USERNAME) && !is_data_center_full($SESSID_USERNAME)) {
    header ("Location: logout.php");
    exit;
  }

  return $SESSID_USERNAME;
}

//
// is_datacenter_full
// Action: check if an admin has full access to datacenter section.
// Call: is_datacenter_full(string admin)
function is_datacenter_full($username) {
	global $CONF;
	if ($CONF['datacenter'] == 'YES') {
		$result = db_query ("SELECT rights.datacenter,rights.datacenter_manage FROM rights 
		                    LEFT JOIN accounts ON rights.accounts_id=accounts.id 
		                    WHERE accounts.username='$username' AND accounts.enabled='1'");
		$row = db_array($result['result']);
		if (($row['datacenter'] == 1 ) && ($row['datacenter_manage'] == 1)) {
			return true;
		} else {
			return false;
		}
	}
	else {
		return false;
	}
}

//
// get_apc_id
// Action: Get the APC Id 
// Call: get_apc_id(string $ip)
//
function get_apc_id($ip) {
  $out = '0';
  $result = db_query("SELECT id FROM apc WHERE ip='$ip'");
  if ($result['rows'] == 1)
  {
      $row = db_array ($result['result']);
      $out = $row['id'];
   }
   return $out;
}

//
// get_apc_id_from_name
// Action: Get the APC Id 
// Call: get_apc_id_from_name(string $name)
//
function get_apc_id_from_name($name) {
  $out = '0';
  $result = db_query("SELECT id FROM apc WHERE name='$name'");
  if ($result['rows'] == 1)
  {
      $row = db_array ($result['result']);
      $out = $row['id'];
   }
   return $out;
}

//
// get_apc_name_from_id
// Action: Return the name of apc from an APC Id
// Call: get_apc_name_from_if(int $id)
//
function get_apc_name_from_id($id) {
  $out = '';
  $result = db_query("SELECT name FROM apc WHERE id='$id' LIMIT 1");
  if ($result['rows'] == 1)
  {
      $row = db_array ($result['result']);
      $out = $row['name'];
   }
   return $out;
}

//
// get_apc_nbpdu
// Action: Get the number of PDU for an APC
// Call: get_apc_nbpdu(string $ip)
//
function get_apc_nbpdu($ip) {
  $out = '0';
  $result = db_query("SELECT nbports FROM apc WHERE ip='$ip'");
  if ($result['rows'] == 1)
  {
      $row = db_array ($result['result']);
      $out = $row['nbports'];
   }
   return $out;
}

//
// get_apc_type
// Action: Get the APC type (see apc.inc.php)
// Call: get_apc_type(string $ip)
//
function get_apc_type($ip) {
  $out = '1';
  $result = db_query("SELECT apctype FROM apc WHERE ip='$ip'");
  if ($result['rows'] == 1)
  {
      $row = db_array ($result['result']);
      $out = $row['apctype'];
   }
   return $out;
}

//
// list_apc
// Action: Lists all APC
// Call: list_apc
//
function list_apc() {
  $list = "";
  $result = db_query("SELECT name FROM apc ORDER BY name");
  if ($result['rows'] >= 1)
  {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
         $list[$i] = $row['name'];
         $i++;
      }
   }
   return $list;
}

//
// list_apc_ports
// Action: List all APC and ports in format "<apcname> :: <portnb> (portname)"
// Call: list_apc_ports
//
function list_apc_ports() {
  $list = "";
  $apc = list_apc();
  $i = 0;
  
  if (is_array($apc)) { 
    foreach($apc as $apc_got) {
      $apc_id = get_apc_id_from_name($apc_got);
      $result = db_query("SELECT port,descr FROM apc_ports WHERE apc='$apc_id' AND active='1' ORDER BY port");
      if ($result['rows'] > 0)
      {
        while ($row = db_array ($result['result']))
        {
          $list[$i] = apc_to_string($apc_got,$row['port'],$row['descr'] );
  	  $i++;
        }
      }
    }
  }
  return $list;
}

//
// list_apc_ports_for_admin
// Action: Lists all APC and ports in "form" format for specified admin
// Call: list_apc_ports_for_admin (string $admin)
//
function list_apc_ports_for_admin($admin) {
  $list = "";
  $i = 0;
  if (is_datacenter_full($admin)) {
    $result = db_query("SELECT apc,port,descr FROM apc_ports WHERE active='1' ORDER BY apc,port");
    if ($result['rows'] > 0) 
    {
      while ($row = db_array ($result['result']))
      { 
        $list[$i] = apc_to_string(get_apc_name_from_id($row['apc']),$row['port'],$row['descr'] );
        $i++;
      }
    }
  } else {
    $admin_id = get_datacenter_id($admin);
    $result = db_query("SELECT apc_admins.apc AS apc,apc_admins.port AS port,apc_ports.descr AS descr 
                       FROM apc_admins,apc_ports WHERE apc_admins.id='$admin_id' AND 
                       apc_admins.apc=apc_ports.apc AND apc_admins.port=apc_ports.port AND apc_admins.active='1'");
    if ($result['rows'] > 0)
    {
      while ($row = db_array ($result['result']))
      { 
        $list[$i] = apc_to_string(get_apc_name_from_id($row['apc']),$row['port'],$row['descr'] );
        $i++;
      }
    }
  }
  return $list;
}

// 
// is_apc_admin
// Action: check if the apc and port given is managed for the user given
// Call: is_apc_admin(string $admin, string $apcname, int $port) 
function is_apc_admin($admin, $apc, $port) {
   $admin_id = get_datacenter_id ($admin);
   $apc_id = get_apc_id_from_name ($apc);
   //print "admin_id $admin_id, apc_id $apc_id";
   $result = db_query("SELECT id FROM apc_admins WHERE id='$admin_id' AND apc='$apc_id' AND port='$port' AND active='1'");
   if ($result['rows'] == 1) {
     return true;
   } else {
     return false;
   }
}

//
// delete_apc_admin
// Action: delete an Apc admin from database
// Call: delete_apc_admin(int $id)
//
function delete_apc_admin($id) {
   $result = db_query("DELETE FROM apc_admins WHERE id='$id'");
   if ($result['rows'] >= 1) {
     return true;
   } else {
     return false;
   }
}

//
// add_apc_right_to_admin
// Action: Add rights to an admin specified
// Call: add_apc_right_to_admin(int $admin_id, int $apc_id, int $apc_port)
//
function add_apc_right_to_admin($admin_id, $apc_id, $apc_port) {
   $result = db_query("INSERT INTO apc_admins (id,apc,port,created,active) VALUES ('$admin_id','$apc_id','$apc_port',NOW(),'1')");
   if ($result['rows'] == 1) {
     return true;
   } else {
     return false;
   }
}

//
// list_stats_for_admin
// Action: Lists all stats for the given admin
// Call: list_stats_for_admin (string $admin)
//
function list_stats_for_admin($admin) {
  $list = "";
  $i = 0;
  if (is_datacenter_full($admin)) {
    // TODO: Fix
    $result = db_query ("SELECT DISTINCT graphid FROM stats_admin WHERE active='1' ORDER BY graphid");
    if ($result['rows'] > 0)
    {
      while ($row = db_array ($result['result']))
      { 
        $list[$i] = $row['graphid'];
        $i++;
      }
    }
  } else {
   // TODO: fix
   $result = db_query ("SELECT DISTINCT graphid FROM stats_admin WHERE active='1' AND username='$admin' ORDER BY graphid");
    if ($result['rows'] > 0)
    {
      while ($row = db_array ($result['result']))
      { 
        $list[$i] = $row['graphid'];
        $i++;
      }
    }
  } 
  return $list;
}

//
// apc_to_string
// Action: Convert Apc name, portnumber and description into string
// Call : apc_to_string(string $apc, int $port, string $descr)
//
function apc_to_string($apc, $port, $descr) {
  return $apc . " :: " . $port . " ( " . $descr . " )";
}

//
// string_to_apc
// Action: Convert the string given into APC name (used for forms)
// Call: string_to_apc(string $foo)
//
function string_to_apc($foo) {
  $stuff = explode(" :: ", $foo);
  if (count($stuff) == 2) {
    return $stuff[0];
  }
  return "";
}

//
// string_to_apc_outlet
// Action: Convert the string given into APC outlet number (used for forms)
// Call: string_to_apc_outlet(string $foo)
//
function string_to_apc_outlet($foo) {
  $stuff = explode(" :: ", $foo);
  if (count($stuff) == 2) {
    $what = $stuff[1];
    $stuff2 = explode (" (", $what);
    if (is_numeric($stuff2[0])) {
      return $stuff2[0];
    }
  }
  return 0;
}

//
// get_apc_properties
// Action: Get all the APC properties.
// Call: get_apc_properties (string apc)
function get_apc_properties ($apc)
{
   $list = "";
   
   $result = db_query ("SELECT id,ip,nbports,created,modified,active FROM apc WHERE name='$apc'");
   $row = db_array ($result['result']);
   $list['id']       = $row['id'];
   $list['ip']       = $row['ip'];
   $list['nbports']  = $row['nbports'];
   $list['created']  = $row['created'];
   $list['modified'] = $row['modified'];
   $list['active']   = $row['active'];

   return $list;
}

//
// ip_check
// Action: check if ip given is valid or not
// Call: ip_check(string ip)
//
function ip_check($ip)
{
  $ip_array = explode(".", $ip);

  if (count($ip_array) <> 4)
  {
    return false;
  }

  for ($loop = 0; $loop < 4; $loop++)
  {
    if (is_numeric($ip_array[$loop]) != true)
    {
      return false;
    }

    if (strlen($ip_array[$loop]) > 3)
    {
      return false;
    }

    if ($ip_array[$loop] > 255)
    {
      return false;
    }

    if (($loop == 0) OR ($loop == 3))
    {
      if ($ip_array[$loop] < 0)
      {
        return false;
      }
    }
    else
    {
      if ($ip_array[$loop] < 0)
      {
        return false;
      }
    }
  
  }
  return true;
}

//
// apc_exist
// Action: Checks if the apc already exists (name OR IP)
// Call: apc_exist (string name, string ip)
//
function apc_exist ($name, $ip)
{
   $result = db_query ("SELECT name FROM apc WHERE name='$name' OR ip='$ip'");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// apc_rename
// Action: Change the name of the given APC
// Call: apc_rename (int id_apc, string name)
//
function apc_rename ($id_apc, $name)
{
   $result = db_query ("UPDATE apc SET name='$name' WHERE id='$id_apc' LIMIT 1");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// apc_rename_outlet
// Action: Change the outlet name of the given APC
// Call: apc_rename (int id_apc, int outlet, string name)
//
function apc_rename_outlet ($id_apc, $outlet, $name)
{
   $result = db_query ("UPDATE apc_ports SET descr='$name',modified=NOW() WHERE port='$outlet' AND apc='$id_apc' LIMIT 1");
   if ($result['rows'] != 1)
   {
      return false;
   }
   else
   {
      return true;
   }
}

//
// list_datacenter_admins
// Action: Lists all the datacenter admins
// Call: list_datacenter_admins ()
//
function list_datacenter_admins ()
{
   $list = "";

   $result = db_query ("SELECT DISTINCT accounts.username AS user FROM accounts,rights 
                       WHERE accounts.id = rights.accounts_id AND rights.datacenter = 1 AND 
                       rights.datacenter_manage = 1 ORDER BY accounts.username");
   if ($result['rows'] > 0)
   {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
         $list[$i] = $row['user'];
         $i++;
      }
   }
   return $list;
}

//
// list_avail_datacenter_admins
// Action: Lists all the available admins
// Call: list_avail_datacenter_admins ()
//
function list_avail_datacenter_admins ()
{
   $list_active = list_datacenter_admins ();

   $list = "";

   $result = db_query ("SELECT DISTINCT accounts.username AS user FROM accounts,rights 
                       WHERE rights.datacenter = 1 ORDER BY accounts.username");
   if ($result['rows'] > 0)
   {
      $i = 0;
      while ($row = db_array ($result['result']))
      {
         if (!in_array($row['user'], $list_active)) {
           $list[$i] = $row['user'];
           $i++;
         }
      }
   }
   return $list;
}

//
// get_datacenter_admin_properties
// Action: Get all the admin properties.
// Call: get_datacenter_admin_properties (string admin)
function get_datacenter_admin_properties ($username)
{
   $list = "";
   
   $result = db_query ("SELECT rights.datacenter AS datacenter,rights.datacenter_manage AS full 
                       FROM accounts,rights WHERE rights.accounts_id=accounts.id AND accounts.username='$username'");
   $row = db_array ($result['result']);
   $list['full'] = $row['full'];

   if ($list['full'] == 1) {
     $result = db_query ("SELECT COUNT(port) FROM apc_ports");
   } else {
     $result = db_query ("SELECT COUNT(apc_admins.apc) FROM apc_admins,accounts,rights 
                         WHERE apc_admins.id=accounts.id AND rights.accounts_id=accounts.id AND accounts.username='$username'");
   }

   $row = db_row ($result['result']);
   $list['apc_port_count'] = $row[0];

   return $list;
}

// 
// get_datacenter_id
// Action: return the user id from a email
// Call: get_datacenter_id(string admin)
//
function get_datacenter_id($username)
{
   $result = db_query ("SELECT accounts.id AS id FROM accounts,rights 
                       WHERE accounts.id=rights.accounts_id AND rights.datacenter = 1 AND accounts.username='$username'");
   if ($result['rows'] == 1)
   {
      $row = db_array($result['result']);
      return $row['id'];
   }
   else
   {
      return NULL;
   }
}

?>
