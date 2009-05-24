# $Id: pool.pm,v 1.3 2009-05-24 11:20:22 kiwi Exp $
# 
package Kazar::pool;

use Config::Fast;
use Mail::RFC822::Address qw(valid);
use POSIX;
use strict;
use Kazar::hash;
use Kazar::db;


# Configuration file handled by Config::Fast;
my %cf = fastconfig('/usr/local/openldap/etc/openldap/db.conf');

sub new
{
	my $class = shift;

	my $this = {};
	bless $this, $class;
#        print STDERR "Starting Kazar::pool\n";
	return $this;
}

sub init
{
	Kazar::db::connect();
	return 0;
}

sub search
{
	my $this = shift;
	my @match_entries = ();
        my($row, $uid, $quoteduid, $res, $sth, $path);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;

	$uid = $filter;

#        print STDERR "Looking for : $uid \n";
	
	if (not ($uid =~ s/^\(uid=(.*)\)$/\1/g)) {
		return(0, ());	# Filtre non authorise.
	}

	if (not valid("<".$uid.">")) {
		# non RFC char exiting
		return (0, @match_entries);
	}

	$quoteduid = Kazar::db::quote($uid);

#	print STDERR "Search for $quoteduid\n";

	# Checking in mailboxes

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT id FROM passwd ".
                "WHERE id = $quoteduid "
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
#	print STDERR "We get some results...\n";
	my $entry = 
		"dn: uid=$uid,ou=whosting,$cf{dn}\n".
		"objectClass: top\n".
		"objectClass: perditionPopmap\n".
		"uid: $uid\n";

	if ( $row = $sth->fetchrow_hashref) {
		$entry .= "mailhost: 192.168.0.4\n";
	} else {
		$entry .= "mailhost: 192.168.0.5\n";
	}

#	print STDERR "Sending -> $entry\n";

	push @match_entries, $entry;
	$sth->finish;

	return(0, @match_entries);

}

sub bind 
{
  return 0;
}

1;
