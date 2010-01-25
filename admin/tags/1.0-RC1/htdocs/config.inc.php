<?php
//
// File: config.inc.php
//
if (ereg ("config.inc.php", $_SERVER['PHP_SELF']))
{
   header ("Location: login.php");
   exit;
}

include("config.inc.php.sample");

// Language config
// Language files are located in './languages'.
$CONF['default_language'] = 'fr';

// Backend configuration
// Select if you want use DB backend or Perl backend
$CONF['slapd_perl'] = 1;

// Database Config
// 'database_type' is one of the following
// mysql  = MySQL 3.23.x  or 4.0.x
// mysqli = MySQL 4.1+ with PHP 5.+
// pgsql  = PostGresSQL
$CONF['database_type'] = 'mysql';
$CONF['database_host'] = 'localhost';
$CONF['database_user'] = 'openvispadmin';
$CONF['database_password'] = 'openvispadmin';
$CONF['database_name'] = 'openvispadmin';

// Site Admin
// Define the Site Admins email address below.
// This will be used to send emails from to create mailboxes.
$CONF['admin_email'] = 'kiwi@oav.net';

// Mail Server
// Hostname (FQDN) of your mail server.
// This is used to send email to Postfix in order to create mailboxes.
$CONF['smtp_server'] = "localhost";
$CONF['smtp_port'] = "25";

// Encrypt
// In what way do you want the passwords to be crypted?
// md5crypt = internal postfix admin md5
// system = whatever you have set as your PHP system default
// cleartext = clear text passwords (ouch!)
$CONF['encrypt'] = 'cleartext';

// Generate Password
// Generate a random password for a mailbox and display it.
// If you want to automagically generate paswords set this to 'YES'.
$CONF['generate_password'] = 'YES';

// Alternative password generator
#$CONF['password_generator'] = '/usr/local/bin/mkpwd -t 6 -l -n 10 -m 10';
$CONF['password_generator'] = '/usr/local/bin/pwgen -acn 11 1';

// Page Size
// Set the number of entries that you would like to see
// in one page.
$CONF['page_size'] = '10';

// Default Aliases
// The default aliases that need to be created for all domains.
$CONF['default_aliases'] = array (
	'abuse' => 'abuse@domain.tld',
	'hostmaster' => 'hostmaster@domain.tld',
	'postmaster' => 'postmaster@domain.tld',
	'webmaster' => 'webmaster@domain.tld'
);

// Mailboxes
// If you want to store the mailboxes per domain set this to 'YES'.
// Example: /usr/local/virtual/domain.tld/username@domain.tld
$CONF['domain_path'] = 'NO';
// If you don't want to have the domain in your mailbox set this to 'NO'.
// Example: /usr/local/virtual/domain.tld/username
$CONF['domain_in_mailbox'] = 'YES';

// Default Domain Values
// Specify your default values below. Quota in MB.
$CONF['aliases'] = '10';
$CONF['mailboxes'] = '150';
$CONF['maxquota'] = '150';

// Definition of Spam Assassin Rules per domain
// Here the level required to put spam header information
$CONF['sa_tag_level'] = '-999';      
// Here the Subject added when the sa_tag_level is reached
$CONF['spam_subject_tag'] = '*** SPAM ***';
// Here the 2nd level required to modify the subject
$CONF['sa_tag2_level'] = '6.9';
// Here the Subject added when the sa_tag_level2 is reached
$CONF['spam_subject_tag2'] = '*** SPAM ***';
// Here the level required to put in quarantine the mail
$CONF['sa_kill_level'] = '999';

// Amavis Quarantine definition
$CONF['virus_quarantine_to'] = 'virus-quarantine';
$CONF['spam_quarantine_to'] = 'spam-quarantine';
$CONF['banned_quarantine_to'] = 'banned-quarantine';


// Quota
// When you want to enforce quota for your mailbox users set this to 'YES'.
$CONF['quota'] = 'YES';
// You can use '1024000' or '1048576'
$CONF['quota_multiplier'] = '1024000';

// Virtual Vacation
// If you want to use virtual vacation for you mailbox users set this to 'YES'.
// NOTE: Make sure that you install the vacation module. http://high5.net/postfixadmin/
$CONF['vacation'] = 'YES';

// Alias Control
// OpenVISP Admin inserts an alias in the alias table for every mailbox it creates.
// The reason for this is that when you want catch-all and normal mailboxes
// to work you need to have the mailbox replicated in the alias table.
// If you want to take control of these aliases as well set this to 'YES'.
$CONF['alias_control'] = 'NO';


// Directory where to store the temporary file uploaded
$CONF['uploaddir'] = '/tmp';

// Shall we display the user password ?
$CONF['showpassword'] = 'YES';



// In wich Order do you want to display your email listing ?
// name or username (username = email) 
$CONF['order_display'] = 'name';

// Logging
// If you don't want logging set this to 'NO';
$CONF['logging'] = 'YES';

// SMTPAUTH
$CONF['smtpauth'] = 'YES';


// This two parameter can be in the database.
// Allow the user to manage his password
$CONF['usermanagepwd'] = 'YES';
// Allow the user to manage his forward
$CONF['usermanagefwd'] = 'YES';


// Header
// Some header configuration.
// If you don't want the OpenVISP Admin logo to appear set this to 'NO'.
$CONF['logo'] = 'NO';
$CONF['header_text'] = ':: Welcome to OpenVISP Admin ::';

// Footer
// Below information will be on all pages.
// If you don't want the footer information to appear set this to 'NO'.
$CONF['show_footer_text'] = 'YES';
$CONF['footer_text'] = 'Return to OpenVISP Admin';
$CONF['footer_link'] = 'http://www.oav.net/projects/openvisp-admin/';

// More functionalities
// Note: they are not finished so for production I strongly recommand
//       to set them to NO until they are finished

// SNMP configuration
// Nb of SNMP retries
$CONF['snmp_retries'] = 3;
// Where is located snmpget (only used when you don't have any php-snmp support)
$CONF['path_snmpget'] = '/usr/bin/snmpget';
// Where is located snmpset
$CONF['path_snmpset'] = '/usr/bin/snmpset';
// Default community for APC Masterswitch (r/w community)
$CONF['apc_ro_com'] = 'read2oav';
$CONF['apc_rw_com'] = 'write2oav';

// Webhosting functionality 
$CONF['webhosting'] = 'YES';

// Define storage for websites and FTP
$CONF['storage'] = '/somewhere/storage';

// To manage or not ftp ratio
$CONF['ftp_ratio'] = 'NO';

// Datacenter functionality
$CONF['datacenter'] = 'YES';

// Email to send datacenter requests
$CONF['datacenter_request_to'] = 'postmaster@nowhere.com';
$CONF['datacenter_request_bcc'] = 'postmaster@nowhere.com';
$CONF['datacenter_request_subject'] = 'HELP for OpenVISP Admin Users';
$CONF['datacenter_request_body'] = "Hello\n The following user needs for help from your team : ";
$CONF['datacenter_request_reboot'] = 'Reboot the server';
$CONF['datacenter_request_led'] = 'Report LED activity of the server';
$CONF['datacenter_request_screen'] = 'Report LED activity of the server';
$CONF['datacenter_request_ping'] = 'Report the PING accessiblity of the server. IP address is in Comment';
$CONF['datacenter_request_others'] = "Other : \n";
$CONF['datacenter_request_comment'] = "Comment : \n";
// Signature will be NOT shown to end use. Usefull to add some secret or
// anything that can help you to authenticate the mail
$CONF['datacenter_request_signature'] = "\n\nSignature \n--\nfoo";
// physical access to datacenter
$CONF['datacenter_request_access_to'] = 'kiwi@nowhere.com';
$CONF['datacenter_request_access_subject'] = 'Physical Access to DataCenter';
$CONF['datacenter_request_access_body'] = "Hello\nA OpenVISP Admin user need to have physical access to DataCenter. Please find in the following mail the complete informations about this user :";
$CONF['datacenter_request_access_email'] = "Email of user :\n\n";
$CONF['datacenter_request_access_type'] = "Type of work / Acces :\n\n";
$CONF['datacenter_request_access_datetime'] = "Date and time of wished access :\n\n";
$CONF['datacenter_request_access_names'] = "Names of accessing people :\n\n";
$CONF['datacenter_request_access_comment'] = "Comments :\n\n";
$CONF['datacenter_request_access_signature'] = "\n\nSignature\n--\n\nfoo";
// This text will be displayed on screen (so there will NO translation !) 
// when a user request for a admin access 
$CONF['datacenter_request_access_notice'] = "<b>Please don't forget to have an ID card when accessing to datacenter.</b>";
// Email to send APC status messages.
$CONF['datacenter_apc_status_to'] = 'devnull@null.com';
$CONF['datacenter_apc_status_subject'] = 'APC Status changed';
$CONF['datacenter_apc_status_from'] = 'devnull@null.com';
$CONF['datacenter_apc_status_body'] = "Hello\n\nThe followin APC has been asked to be changed.\nUsername\tAPC\tPort\tDescription\tOld Status\tNew Status\n";
// URL /  Path where is routers2 CGI ?
$CONF['datacenter_routers2_url'] = 'http://devnull.img.com/routers2.cgi';

?>
