$unix_socketname = "$MYHOME/amavisd.sock";

$inet_socket_port = [10024,9998]; 

@inet_acl = qw(127.0.0.0/8 [::1] 192.168.202.11);

$inet_socket_bind = undef;

$DO_SYSLOG = 1;                   # (defaults to 0)

$LOGFILE = "$MYHOME/amavis.log";  # (defaults to empty, no log)

$log_level = 5;		  # (defaults to 0)

$log_recip_templ = undef;  # undef disables by-recipient level-0 log entries


$final_virus_destiny      = D_DISCARD;  # (defaults to D_DISCARD)
$final_banned_destiny     = D_DISCARD;  # (defaults to D_BOUNCE)
$final_spam_destiny       = D_DISCARD;  # (defaults to D_BOUNCE)
$final_bad_header_destiny = D_PASS;  # (defaults to D_PASS), D_BOUNCE suggested


$warnbannedrecip = 1;	# (defaults to false (undef))

$virus_admin = "virusalert\@$mydomain";

$mailfrom_notify_admin     = "virusalert\@$mydomain";
$mailfrom_notify_recip     = "virusalert\@$mydomain";
$mailfrom_notify_spamadmin = "spam.police\@$mydomain";


$mailfrom_to_quarantine = '';   # override sender address with null return path

$QUARANTINEDIR = '/var/virusmails';

$quarantine_subdir_levels = 2;  # add level of subdirs to disperse quarantine


$virus_quarantine_method          = 'local:virus/%m';
$spam_quarantine_method           = 'local:spam/%m.gz';
$banned_files_quarantine_method   = 'local:banned/%m';
$bad_header_quarantine_method     = 'local:badh/badh-%m';


$virus_quarantine_to  = 'virus-quarantine';    # traditional local quarantine

$banned_quarantine_to     = 'banned-quarantine';     # local quarantine
$bad_header_quarantine_to = 'bad-header-quarantine'; # local quarantine
$spam_quarantine_to       = 'spam-quarantine';       # local quarantine



$X_HEADER_TAG = 'X-Virus-Scanned';	# (default: 'X-Virus-Scanned')

$X_HEADER_LINE = "$myproduct_name at $mydomain";

$undecipherable_subject_tag = '***UNCHECKED*** ';  # undef disables it

$remove_existing_x_scanned_headers = 0; # leave existing X-Virus-Scanned alone
					# (defaults to false)
$remove_existing_spam_headers  = 1;     # remove existing spam headers if
					# spam scanning is enabled (default)


@lookup_sql_dsn =
   ( ['DBI:mysql:database=dbopenvisp;host=;port=3306', 'phpuser', 'phppassword'] );

@storage_sql_dsn = @lookup_sql_dsn;  # none, same, or separate database

        $sql_select_policy = 'SELECT *,alias.address FROM alias,policy'.
        ' WHERE (alias.policy_id = policy.id) AND (alias.address IN (%k))';

$sql_select_white_black_list = undef; 


$localpart_is_case_sensitive = 0;	# (default is false)

 $policy_bank{'AM.PDP'} = {
   log_level => 3,
   protocol=>'AM.PDP', # Amavis policy delegation protocol (new milter helper)
   inet_acl => 	[qw( 127.0.0.1 [::1] )],
 };


$interface_policy{'9998'} = 'AM.PDP';
