<?php
if (ereg ("snmp.php", $_SERVER['PHP_SELF']))
{
   header ("Location: ../login.php");
   exit;
}

if (function_exists("snmpget")) {
  require("snmp_php.php");
} else {
  require("snmp_unix.php");
}

?>
