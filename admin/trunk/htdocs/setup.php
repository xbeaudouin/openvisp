<?php

/* This code is highly inspired from Cacti installation script, than for Cacti
   that allowed me to create a simple install */

/* allow the install script to run as long as it needs to */
ini_set("max_execution_time","0");

/* If we don't have step, then this is the first time we start openvisp admin */
if(empty($_REQUEST["step"])) {
  $_REQUEST["step"] = "1";
} else {
  if($_REQUEST["step"] == "1") {
    $_REQUEST["step"] = "2";
  } elseif ($_REQUEST["step"] == "2") {
    $_REQUEST["step"] = "3";
  } elseif ($_REQUEST["step"] == "3") {
    $_REQUEST["step"] = "4";
  }
}

//
// check_php();
// returns number of errors
function check_php() {
  /* Check for availablilty functions */
  $f_phpversion = function_exists ("phpversion");
  $f_apache_get_version = function_exists ("apache_get_version");
  $f_fileowner = function_exists ("fileowner");
  $f_posix_getpwuid = function_exists ("posix_getpwuid");
  $f_mysql_connect = function_exists ("mysql_connect");
  $f_mysqli_connect = function_exists ("mysqli_connect");
  $f_pg_connect = function_exists ("pg_connect");
  $f_session_start = function_exists ("session_start");
  $f_preg_match = function_exists ("preg_match");
  $f_snmp_get = function_exists("snmpget");

  $file_config = file_exists (realpath ("./config.inc.php"));

  $error = 0;

  /* Check for PHP version */
  if ($f_phpversion == 1)
  {
     if (phpversion() < 5) $phpversion = 4;
     if (phpversion() >= 5) $phpversion = 5;
     print "- PHP version " . phpversion () . "<br />\n";
  }
  else
  {
     print "<li><b>Unable to check for PHP version. (missing function: phpversion())</b><br />\n";
  }
  print "<p />\n";

  /* Check for Apache version */
  if ($f_apache_get_version == 1)
  {
     print "- " . apache_get_version() . "<br /><p />\n";
  }
  else
  {
     print "<li><b>Unable to check for Apache version. (missing function: apache_get_version())</b> maybe you don't use Apache (good !)<br />\n";
  }
  print "<p />\n";


  /* Check owner of index.php */
  if (($f_fileowner == 1) and ($f_posix_getpwuid == 1))
  {
     $check_file = "./index.php";
     $fileowneruid = fileowner ($check_file); 
     $fileownerarray = posix_getpwuid ($fileowneruid); 
     $fileowner = $fileownerarray['name']; 

     if ($fileowner == "www")
     {
        print "- Fileowner is: www - OK<br />\n";
     }
     elseif ($fileowner == "nobody")
     {
        print "- Fileowner is: nobody - OK<br />\n";
     }
     elseif ($fileowner == "www-data")
     {
        print "- Fileowner is: www-data - OK<br />\n";
     }
     else
     {
        print "<li><b>Warning: Files are not owned by 'www', 'www-data-' or 'nobody', please change it.</b><br />\n";
        print "Files are owned by: $fileowner<br />\n";
        print "For example:<br />\n";
        print "<pre>% chown -R www:www ova-0.99</pre>\n";
     }
  }
  else
  {
     print "<li><b>Unable to check for file owner (missing functions: fileowner() / posix_getpwuid())</b><br />\n";
  }
  print "<p />\n";

  /* Check for config.inc.php */
  if ($file_config == 1)
  {
     print "- Depends on: presence config.inc.php - OK<br />\n";
  }
  else
  {
     print "<li><b>Error: Depends on: presence config.inc.php - NOT FOUND</b><br />\n";
     print "Create the file.<br />";
     print "For example:<br />\n";
     print "<pre>% cp config.inc.php.sample config.inc.php</pre>\n";
     $error =+ 1;
  }
  print "<p />\n";

  /* Check if there is support for at least 1 database */
  if (($f_mysql_connect == 0) and ($f_mysqli_connect == 0) and ($f_pg_connect == 0))
  {
     print "<li><b>Error: There is no database support in your PHP setup</b><br />\n";
     print "To install MySQL 3.23 or 4.0 support on FreeBSD:<br />\n";
     print "<pre>% cd /usr/ports/databases/php$phpversion-mysql/\n";
     print "% make clean install\n";
     print " - or with portupgrade -\n";
     print "% portinstall php$phpversion-mysql</pre>\n";
     if ($phpversion >= 5)
     {
        print "To install MySQL 4.1 support on FreeBSD:<br />\n";
        print "<pre>% cd /usr/ports/databases/php5-mysqli/\n";
        print "% make clean install\n";
        print " - or with portupgrade -\n";
        print "% portinstall php5-mysqli</pre>\n";
     }
     print "To install PostgreSQL support on FreeBSD:<br />\n";
     print "<pre>% cd /usr/ports/databases/php$phpversion-pgsql/\n";
     print "% make clean install\n";
     print " - or with portupgrade -\n";
     print "% portinstall php$phpversion-pgsql</pre>\n";
     $error =+ 1;
  }

  /* MySQL 3.23, 4.0 functions */
  if ($f_mysql_connect == 1)
  {
     print "- Depends on: MySQL 3.23, 4.0 - OK<br />\n";
  }
  print "<p />\n";

  /* MySQL 4.1 functions */
  if ($phpversion >= 5)
  {
     if ($f_mysqli_connect == 1)
     {
        print "- Depends on: MySQL 4.1 - OK<br />\n";
     }
  }
  print "<p />\n";

  /* PostgreSQL functions */
  if ($f_pg_connect == 1)
  {
     print "- Depends on: PostgreSQL - OK<br />\n";
  }
  print "<p />\n";

  /* Session functions */
  if ($f_session_start == 1)
  {
     print "- Depends on: session - OK<br />\n";
  }
  else
  {
     print "<li><b>Error: Depends on: session - NOT FOUND</b><br />\n";
     print "To install session support on FreeBSD:<br />\n";
     print "<pre>% cd /usr/ports/www/php$phpversion-session/\n";
     print "% make clean install\n";
     print " - or with portupgrade -\n";
     print "% portinstall php$phpversion-session</pre>\n";
     $error =+ 1;
  }
  print "<p />\n";

  /* PCRE functions */
  if ($f_preg_match == 1)
  {
     print "- Depends on: pcre - OK<br />\n";
  }
  else
  {
     print "<li><b>Error: Depends on: pcre - NOT FOUND</b><br />\n";
     print "To install pcre support on FreeBSD:<br />\n";
     print "<pre>% cd /usr/ports/devel/php$phpversion-pcre/\n";
     print "% make clean install\n";
     print " - or with portupgrade -\n";
     print "% portinstall php$phpversion-pcre</pre>\n";
     $error =+ 1;
  }
  print "<p />\n";

  /* SNMP function */
  if ($f_snmp_get == 1)
  {
     print "- Depends: snmp - OK<br />\n";
  }
  else
  {
     print "<li><b>Error: Depends on: snmp - NOT FOUND</b><br />\n";
     print "To install snmp support on FreeBSD:<br />\n";
     print "<pre>% cd /usr/ports/net-mgmt/php$phpversion-snmp/\n";
     print "% make clean install\n";
     print " - or with portupgrade -\n";
     print "% portinstall php$phpversion-snmp</pre>\n";
     print "You can also install NET-SNMP and use local net-snmp utilities, but this slower then.<br />";
  }

  if ($error == 0)
  {
     print "Everything seems fine... you are ready to rock & roll!</br>\n";
     //print "<b>Make sure you delete this setup.php file!</b><br />\n";
     print "Also check the config.inc.php file for any settings that you might need to change!<br />\n";
  }
  return $error;

}

?>
<html>
<head>
<title>OpenVISP Installation Guide</title>
<style>
<!--
BODY,TABLE,TR,TD
{
   font-size: 10pt;
   font-family: Verdana, Arial, sans-serif;
}

.code
{
   font-family: Courier New, Courier;
}

.header-text
{
   color: white;
   font-weight: bold;
}

-->
</style>
</head>
<body>
<form method="post" action="setup.php">
<table width="550" align="center" cellpadding="1" cellspacing="0" border="0" bgcolor="#777777">
        <tr bgcolor="#FFFFFF" height="10">
                <td>&nbsp;</td>
        </tr>
        <tr>
                <td width="100%">
                        <table cellpadding="3" cellspacing="0" border="0" bgcolor="#eeeeee" width="100%">
                                <tr>
                                        <td bgcolor="#777777" class="header-text">OpenVISP Installation Guide</td>
                                </tr>
                                <tr>
                                        <td width="100%" style="font-size: 12px;">
					  	<?php if($_REQUEST["step"] == "1") { ?>
						<p>Thank for taking the time to download and install OpenVISP Admin.</p>
						<p>Before you can start to create mail accounts, and manage your whole
						   datacenter and hosting solutions, this setup.php will help you install
						   and configure the minimalist user to access to OpenVISP Admin.</p>
						<p>OpenVISP Admin is licencied under MPL 1.1, you must agree to its
						   provisions before continuing :
						<p class="code">The contents of this file are subject to the Mozilla Public License
     						Version 1.1 (the "License"); you may not use this file except in
     						compliance with the License. You may obtain a copy of the License at
     						<a href="http://www.mozilla.org/MPL/">http://www.mozilla.org/MPL/</a>.</p>

						<p class="code">Software distributed under the License is distributed on an "AS IS"
						basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
						License for the specific language governing rights and limitations
						under the License.</p>

						<?php } elseif ($_REQUEST["step"] == "2") { ?>
						<p>Please make sure you have configured you copy of OpenVSIP Admin. If
						you didn't have configured it yet, please take a moment to do the following
						UNIX commands before click on "Next" button :</p>
						<p class="code">$ cd /usr/local/directory/where/is/openvisp-admin<br/>
						$ cp config.inc.php.sample config.inc.php</p>
						<p>Edit the file :</p>
						<p class="code">$ vi config.inc.php</p>
						<p>with your favourite editor (in this case <i>vi</i>),
						and update the configuration according to what you would like.</p>
						<p>And : </p>
						<p>Also you have to create the Database using doc/DATABASE.TXT <b>
						before</b> continuing next step.</B>
						<p>When this is done, click on <b>Next</b> button.</p>
						<?php } elseif ($_REQUEST["step"] == "3") { ?>
						<p>PHP and OpenVISP Admin configuration tests:</p>
						<?php
							$status = check_php();
							if ($status == 0) { ?>
								<p>Your PHP install is successfull, you can continue the
								installation by pressing <b>Next</b> to install default user
								and password.</b>
								<p>Note that will create a <i>big administrator</i> that
								will have full rights on ALL sections of OpenVISP Admin.
								So don't forget to change the password of this user if
								OpenVISP Admin is reachable from WWW</p>
								<p>Also please note the this tool will create the password
								of this user according to <i>"Encrypt"</i> method selected
								in config.inc.php. There <b>NO</b> methods to migrate from
								md5crypt, system to cleartext method so double check that
								the method used is the right one.</p>
							<?php } else { 
								$_REQUEST["step"] = "2"; ?>
								<p>Your PHP install is lacking some <b>required</b> modules
								or you didn't configured OpenVISP Admin. Please read the 
								information shown in this screen and press Next when you have
								updated your system.</p>
							<?php } ?>
						<?php } elseif ($_REQUEST["step"] == "4") { ?>
						<p>Installing default User...</p>
						<p>Please wait for Complete loading of this page. This can take a while...</p>
						<?php
							require("./variables.inc.php");
							require("./config.inc.php");
							require("./lib/functions.inc.php");
						  require("./lib/accounts.inc.php");

							$password = pacrypt("admin");

							$result = db_query("INSERT INTO accounts (username,password,created,tech,enabled) VALUES('admin@ova.local','$password',NOW(),'1','1')");
              $username_id = $result['inserted_id'];
							$result = db_query("INSERT INTO domain (domain,created,modified,active) VALUES('ova.local',NOW(),NOW(),'1')");
              $domain_id = $result['inserted_id'];
              $result = db_query("INSERT INTO domain_admins (domain_id,accounts_id,created,active) VALUES('".$domain_id."','".$username_id."',NOW(),'1')");
              //$result .= $associate['message'];

							// $result = db_query("INSERT INTO datacenter_admins (id,full,created,modified,active) VALUES('1','1',NOW(),NOW(),'1')");

              $result = db_query("INSERT INTO policy(domain_id, virus_lover, spam_lover, banned_files_lover, bad_header_lover, bypass_virus_checks, bypass_spam_checks, bypass_banned_checks, bypass_header_checks, spam_modifies_subj, virus_quarantine_to, spam_tag_level, spam_tag2_level, spam_kill_level, spam_dsn_cutoff_level, addr_extension_virus, addr_extension_spam, addr_extension_banned, addr_extension_bad_header, warnvirusrecip, warnbannedrecip, warnbadhrecip, newvirus_admin, virus_admin, banned_admin, bad_header_admin, spam_admin, spam_subject_tag, spam_subject_tag2, message_size_limit, banned_rulenames, modified) VALUES('".$domain_id."','N','N','N','N','N','N','N','N','N','N','0','3','7','10','N','N','N','N','N','N','N','N','N','N','N','N','N','N','N','0','N')");

							$result = db_query("INSERT INTO rights (accounts_id,mail,datacenter,ftp,http,domain,mysql,postgresql,manage) VALUES ('".$username_id."','1','1','1','1','1','1','1','1')");

							$result = db_query("INSERT INTO quota VALUES ('".$username_id."', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1',NOW(), NOW())");

							$result = db_query("INSERT INTO ovavers (ova,query) VALUES ('".$version."','336')");

						?>
						<p>You get now a default Admin : </p>
						<p class="code"><br/>
						Login   : admin@ova.local<br/>
						Password: admin</p>
						<p><b>WARNING:</b> DO NOT FORGET to REMOVE setup.php after you have finished this installation.</p>
						<p>Don't forget to CHANGE the password of this user and create another admins</p>
						<p><a href="login.php">Click here to login</a></p>
						<?php }
						 if ($_REQUEST["step"] != "4") {?>
						<p align="right"><input type="submit" name="submit" value="Next &gt;&gt;"></p>
						<?php } ?>	
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<input type="hidden" name="step" value="<?php print $_REQUEST["step"]; ?>">
</form>
</body>
</html>
