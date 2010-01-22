<?php
//
// File: apc.inc.php
//
// Do APC MasterSwitch basic stuff

if (ereg ("apc.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: login.php");
   exit;
}

require("snmp.php");

// APC OID
// Old APC (model AP9210I or AP9606 for example)
$OID['1']['sysUpTime']			= '.1.3.6.1.2.1.1.3.0';
$OID['1']['ipAdEntNetMask']		= '.1.3.6.1.2.1.4.20.1.3.';	# Add IP at the end
$OID['1']['ifPhysAddress']		= '.1.3.6.1.2.1.2.2.1.6.1';
$OID['1']['sPDUIdentSerialNumber']	= '.1.3.6.1.4.1.318.1.1.4.1.5.0';
$OID['1']['sPDUIdentModelNumber']	= '.1.3.6.1.4.1.318.1.1.4.1.4.0';
$OID['1']['sPDUIdentDateOfManufacture'] = '.1.3.6.1.4.1.318.1.1.4.1.3.0';
$OID['1']['sPDUIdentFirmwareRev']	= '.1.3.6.1.4.1.318.1.1.4.1.2.0';
$OID['1']['sPDUIdentHardwareRev']	= '.1.3.6.1.4.1.318.1.1.4.1.1.0';
$OID['1']['sPDUOutletName']		= '.1.3.6.1.4.1.318.1.1.4.4.2.1.4';
$OID['1']['sPDUOutletCtl']		= '.1.3.6.1.4.1.318.1.1.4.4.2.1.3';

// New APC 
$OID['2']['sysUpTime']			= '.1.3.6.1.2.1.1.3.0';
$OID['2']['ipAdEntNetMask']		= '.1.3.6.1.2.1.4.20.1.3.';	# Add IP at the end
$OID['2']['ifPhysAddress']		= '.1.3.6.1.2.1.2.2.1.6.2';
$OID['2']['sPDUIdentSerialNumber']	= '.1.3.6.1.4.1.318.1.1.12.1.6.0';
$OID['2']['sPDUIdentModelNumber']	= '.1.3.6.1.4.1.318.1.1.12.1.5.0';
$OID['2']['sPDUIdentDateOfManufacture'] = '.1.3.6.1.4.1.318.1.1.12.1.4.0';
$OID['2']['sPDUIdentFirmwareRev']	= '.1.3.6.1.4.1.318.1.1.12.1.3.0';
$OID['2']['sPDUIdentHardwareRev']	= '.1.3.6.1.4.1.318.1.1.12.1.2.0';
$OID['2']['rPDUNumOfOutlets']		= '.1.3.6.1.4.1.318.1.1.12.1.8.0';
$OID['2']['rPDUCurrentAmps']		= '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.1';
$OID['2']['sPDUOutletName']		= '.1.3.6.1.4.1.318.1.1.12.3.3.1.1.2';
$OID['2']['sPDUOutletCtl']		= '.1.3.6.1.4.1.318.1.1.12.3.3.1.1.4';

// aapcfind_model
// Call: apc_find_model(string ip)
// Return: The model name of the APC according to $OID
function apc_find_model($ip) {
  global $CONF;
  global $OID;

  $list = array();
  
  for ($i = 1; $i <= count($OID); $i++) {
    $list['sPDUIdentModelNumber'.$i] = snmp_get($ip, $CONF['apc_ro_com'], $OID[$i]['sPDUIdentModelNumber']);
    $list['sPDUIdentFirmwareRev'.$i] = snmp_get($ip, $CONF['apc_ro_com'], $OID[$i]['sPDUIdentFirmwareRev']);
    $list['sPDUIdentHardwareRev'.$i] = snmp_get($ip, $CONF['apc_ro_com'], $OID[$i]['sPDUIdentHardwareRev']);
  }

//  print_r($list);

  // Find what model it is and get the right way to communicate with it.
  if       (array_search_recursive("AP9210I", $list)) {
	return 1;
  } elseif (array_search_recursive("AP92606", $list)) {
	return 1;
  } elseif (array_search_recursive("AP7920", $list)) {
	return 2;
  } else {
	return 1;	// Default model
  }
}

// array_search_recursive
// Call: array_search_recursive($needle, $haystack)
// Retunr: true if found
function array_search_recursive($needle, $haystack) {
    foreach($haystack as $id => $val)
    {
         $path2=$path;
         $path2[] = $id;
 
         if($val === $needle)
              return $path2;
         else if(is_array($val))
              if($ret = array_search_recursive($needle, $val, $path2))
                   return $ret;
      }
      return false;
}

// apc_get_model
// Call: apc_get_model(string ip)
// Return: the style of APC : new: 1 / old: 0
function apc_get_model($ip) {
  global $CONF;

  $model = "";

  $sPDUIdentModelNumber       = '.1.3.6.1.4.1.318.1.1.4.1.4.0';
  $sPDUIdentModelNumber2      = '.1.3.6.1.4.1.318.1.1.12.1.5.0';
  $model0 = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentModelNumber);
  $model1 = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentModelNumber2);

  if ((substr($model0,0,2) == 'AP') || (substr($model1,0,2) == 'AP'))
  {
      return 1;
  }
  return 0;
}

// apc_get_snmp_basic_stuff
// Call: apc_get_snmp_basic_stuff(string ip)
// Return: array with ip, netmask, phyaddres, serialnumber, etc...
function apc_get_snmp_basic_stuff($ip) {
  global $CONF;
  global $OID;
   
 # print "Nb model : ".count($OID)."<br/>"; # Nb de modèles

  $list = "";

  $sysUpTime                  = '.1.3.6.1.2.1.1.3.0';
  $ipAdEntNetMask             = '.1.3.6.1.2.1.4.20.1.3.'.$ip;
  $ifPhysAddress              = '.1.3.6.1.2.1.2.2.1.6.1';
  $sPDUIdentSerialNumber      = '.1.3.6.1.4.1.318.1.1.4.1.5.0';
  $sPDUIdentModelNumber       = '.1.3.6.1.4.1.318.1.1.4.1.4.0';
  $sPDUIdentDateOfManufacture = '.1.3.6.1.4.1.318.1.1.4.1.3.0';
  $sPDUIdentFirmwareRev       = '.1.3.6.1.4.1.318.1.1.4.1.2.0';
  $sPDUIdentHardwareRev       = '.1.3.6.1.4.1.318.1.1.4.1.1.0';

  // AP7921 etc
  $sPDUIdentModelNumber2      = '.1.3.6.1.4.1.318.1.1.12.1.5.0';
  $sPDUIdentSerialNumber2     = '.1.3.6.1.4.1.318.1.1.12.1.6.0';
  $sPDUIdentFirmwareRev2      = '.1.3.6.1.4.1.318.1.1.12.1.3.0';
  $sPDUIdentHardwareRev2      = '.1.3.6.1.4.1.318.1.1.12.1.2.0';
  $sPDUIdentDateOfManufacture2= '.1.3.6.1.4.1.318.1.1.12.1.4.0';
  $ifPhysAddress2             = '.1.3.6.1.2.1.2.2.1.6.2';
  $rPDUNumOfOutlets           = '.1.3.6.1.4.1.318.1.1.12.1.8.0';
  $rPDUCurrentAmps            = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.1';

  $zemodel = apc_find_model($ip);
  print "Ze model =".$zemodel."<br/>";

  $list['ipaddress'] = $ip;
  $list['uptime']   = snmp_get($ip, $CONF['apc_ro_com'], $OID['1']['sysUpTime']);
  $list['netmask']  = snmp_get($ip, $CONF['apc_ro_com'], $ipAdEntNetMask);
  $list['model']    = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentModelNumber);
  $apc_model = $list['model'];
  $apc_model1  = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentModelNumber2);
  $apc_serial0 = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentSerialNumber);
  $apc_serial1 = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentSerialNumber2);

//  print "model $apc_model";
//  $zzfoo = substr($apc_model,0,2);

//  print " zzfoo : $zzfoo ";
// if (substr($apc_model,0,2) != 'AP')
// print "Serial : $apc_serial0 / $apc_serial1 / $apc_model / $apc_model1";

  // TODO: Change apc_get_model($ip) if it is better

//  if ((substr($apc_model,0,2) == 'AP') || (substr($apc_model1,0,2) == 'AP'))
  if (!apc_get_model($ip))
  {
    // We have extended APC so get the new MIBS
    $list['model']    = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentModelNumber2);
    $list['serial']   = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentSerialNumber2);
    $list['bornon']   = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentDateOfManufacture2);
    $list['firmware'] = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentFirmwareRev2);
    $list['hardware'] = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentHardwareRev2);
    $list['mac']      = snmp_get($ip, $CONF['apc_ro_com'], $ifPhysAddress2);
    // Check in case of a mac that is not found
    $apc_mac = $list['mac'];
    if (!(substr($apc_mac,2,1) == ':')) {
      $list['mac']      = snmp_get($ip, $CONF['apc_ro_com'], $ifPhysAddress);
    }
    $list['numpdu']   = snmp_get($ip, $CONF['apc_ro_com'], $rPDUNumOfOutlets);
    $list['amps']     = (snmp_get($ip, $CONF['apc_ro_com'], $rPDUCurrentAmps)) / 10;
    $list['is_new']   = '1';
  } else {
    $list['mac']      = snmp_get($ip, $CONF['apc_ro_com'], $ifPhysAddress);
    $list['serial']   = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentSerialNumber);
    $list['bornon']   = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentDateOfManufacture);
    $list['firmware'] = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentFirmwareRev);
    $list['hardware'] = snmp_get($ip, $CONF['apc_ro_com'], $sPDUIdentHardwareRev);
    $list['numpdu']   = '8';
    $list['amps']     = 'n/a';
    $list['is_new']   = '0';
  }

  return $list;
}

// apc_get_outlet_stuff
// Call: apc_get_outlet_stuff(string ip, string outlet)
// Return an array with label and status of each ports
function apc_get_outlet_stuff($ip, $outlet) {
  global $CONF;

  $list = "";

  if (apc_get_model($ip)) 
  {
    $sPDUOutletName = '.1.3.6.1.4.1.318.1.1.12.3.3.1.1.2';
    $sPDUOutletCtl  = '.1.3.6.1.4.1.318.1.1.12.3.3.1.1.4';
  } else {
    $sPDUOutletName = '.1.3.6.1.4.1.318.1.1.4.4.2.1.4';
    $sPDUOutletCtl  = '.1.3.6.1.4.1.318.1.1.4.4.2.1.3';
  }
  $list['label']  = snmp_get($ip, $CONF['apc_ro_com'], $sPDUOutletName.".".$outlet);
  $list['status'] = snmp_get($ip, $CONF['apc_ro_com'], $sPDUOutletCtl.".".$outlet);
  return $list;
}

// apc_outletstatus2human
// Call : apc_outletstatus2human(int status)
// Return : return an human readable string within the outlet status 
//          returned by apc_get_outlet_stuff()
function apc_outletstatus2human($status) {
  $val = array (1=>'On', 2=>'Off', 3=>'Reboot');

  return $val[$status];
}

// apc_set_outlet_label
// Call : apc_set_outlet_label(string ip, string outlet, string value) 
// Return : nothing, just set label to the correct value
function apc_set_outlet_label($ip, $outlet, $value) {
  global $CONF;

  if (apc_get_model($ip)) 
  {
    $sPDUOutletsName = '.1.3.6.1.4.1.318.1.1.12.3.4.1.1.2';
  } else {
    $sPDUOutletsName = '.1.3.6.1.4.1.318.1.1.4.5.2.1.3';
  }
  snmp_set($ip, $CONF['apc_rw_com'], $sPDUOutletsName.'.'.$outlet, "s", "\"".$value."\"");
}

// apc_set_outlet_off
// Call : apc_set_outlet_off(string ip, string outlet)
// Return: Switch off the outlet
function apc_set_outlet_off($ip, $outlet) {
  apc_set_outlet_what($ip, $outlet, '2');
}

// apc_set_outlet_on
// Call : apc_set_outlet_on(string ip, string outlet)
// Return ; Switch on the outlet
function apc_set_outlet_on($ip, $outlet) {
  apc_set_outlet_what($ip, $outlet, '1');
}

// apc_set_outlet_reboot
// Call : apc_set_outlet_reboot(string ip, string outlet)
// Return: reboot the outlet
function apc_set_outlet_reboot($ip, $outlet) {
  apc_set_outlet_what($ip, $outlet, '3');
}

// apc_set_outlet_what
// Call : apc_set_outlet_what(string ip, string outlet, string what)
// Return: Switch on / off / reboot according to the value of what (0, 1, 2)
function apc_set_outlet_what($ip, $outlet, $what) {
  global $CONF;

  if (apc_get_model($ip)) 
  {
    $sPDUOutletCtl = '.1.3.6.1.4.1.318.1.1.12.3.3.1.1.4';
  } else {
    $sPDUOutletCtl = '.1.3.6.1.4.1.318.1.1.4.4.2.1.3';
  }
  snmp_set($ip, $CONF['apc_rw_com'], $sPDUOutletCtl.'.'.$outlet, "i", $what);
}

?>
