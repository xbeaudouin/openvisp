<?php
/*
 +-------------------------------------------------------------------------+
 | Copyright (C) 2004 Ian Berry                                            |
 |                                                                         |
 | This program is free software; you can redistribute it and/or           |
 | modify it under the terms of the GNU General Public License             |
 | as published by the Free Software Foundation; either version 2          |
 | of the License, or (at your option) any later version.                  |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 +-------------------------------------------------------------------------+
 | cacti: a php-based graphing solution                                    |
 +-------------------------------------------------------------------------+
 | Most of this code has been designed, written and is maintained by       |
 | Ian Berry. See about.php for specific developer credit. Any questions   |
 | or comments regarding this code should be directed to:                  |
 | - iberry@raxnet.net                                                     |
 +-------------------------------------------------------------------------+
 | - raXnet - http://www.raxnet.net/                                       |
 +-------------------------------------------------------------------------+
*/

if (ereg ("snmp_php.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../login.php");
   exit;
}

require("snmp_common.php");

function snmp_get($hostname, $community, $oid, $port = 161, $timeout = 500) {
	global $CONF;

	$retries = 3;

	/* make sure snmp* is verbose so we can see what types of data
	we are getting back */
	snmp_set_quick_print(0);

	$snmp_value = @snmpget("$hostname:$port", $community, $oid, ($timeout * 1000), $retries);

	if (isset($snmp_value)) {
		/* fix for multi-line snmp output */
		if (is_array($snmp_value)) {
			$snmp_value = implode(" ", $snmp_value);
		}

		/* strip out non-snmp data */
		$snmp_value = format_snmp_string($snmp_value);

		return $snmp_value;
	}
}

function snmp_set($hostname, $community, $oid, $type, $value, $port = 161, $timeout = 500) {
	global $CONF;

	$retries = 3;

	/* make sure snmp* is verbose so we can see what types of data
	we are getting back */
	snmp_set_quick_print(0);

	$snmp_value = @snmpset("$hostname:$port", $community, $oid, $type, $value, ($timeout * 1000), $retries);

	if (isset($snmp_value)) {
		/* fix for multi-line snmp output */
		if (is_array($snmp_value)) {
			$snmp_value = implode(" ", $snmp_value);
		}

		/* strip out non-snmp data */
		$snmp_value = format_snmp_string($snmp_value);

		return $snmp_value;
	}
}


?>
