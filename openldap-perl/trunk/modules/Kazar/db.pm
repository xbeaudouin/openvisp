# $Id: db.pm,v 1.1 2006-06-26 22:28:58 kiwi Exp $
# 
package Kazar::db;

use Config::Fast;
use POSIX;
use DBI;
use strict;

# Configuration file handled by Config::Fast;
my %cf = fastconfig('/usr/local/openldap/etc/openldap/db.conf');

# Locate variables
my $dbh;	# The Database handler.
my $db_access;	# The last time the db was used.

# DB handler to handle persistant connection and disconnections.
sub connect
{
	my $count = $cf{max_connects};
	my $old_db_access = $db_access;

	$db_access = time();
	#print STDERR "DB : connection in $db_access\n";

	if ($db_access > $old_db_access+$cf{db_max} ) {
		#print STDERR "DB : connection is too old\n";
		if($old_db_access > 0) {
			$dbh->disconnect
				or warn "DB : Failed to disconnect: ", $dbh->errstr(), "\n";
		}

		until (
#			$dbh = DBI->connect("DBI:mysql:$cf{db_name}", "$cf{db_user}", 
			$dbh = DBI->connect("DBI:mysql:database=$cf{db_name};host=$cf{db_host};port=$cf{db_port}", "$cf{db_user}", 
				    "$cf{db_pass}", { RaiseError => 1 })
		) {
			#print STDERR "DB : Can't connect to DB trying again\n";
			$count = $count - 1;
			if($count == 0) {
				die "DB : Can't connect to DB\n";
			}
		}
	}
	return 0;
}

sub quote
{
	my ($name) = @_;
	Kazar::db::connect();
	return $dbh->quote($name);
}

sub prepare
{
	Kazar::db::connect();
	return $dbh->prepare(@_);
}

sub errstr
{
	return $dbh->errstr;
}

# Usefull tools for encoding
sub latin1_to_utf8
{
   ( $_ ) = @_;
   s/([\x80-\xFF])/chr(0xC0|ord($1)>>6).chr(0x80|ord($1)&0x3F)/eg;
   return($_);
}

sub utf8_to_latin1
{
    ( $_ ) = @_;
    s/([\xC0-\xDF])([\x80-\xBF])/chr(ord($1)<<6&0xC0|ord($2)&0x3F)/eg;
    return ($_);
}

1;
