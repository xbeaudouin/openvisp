# $Id: vacation.pm,v 1.2 2007-06-18 16:13:08 kiwi Exp $
# 
package Kazar::vacation;

use Config::Fast;
use Mail::RFC822::Address qw(valid);
use POSIX;
use strict;
use Kazar::db;


# Configuration file handled by Config::Fast;
my %cf = fastconfig('/usr/local/openldap/etc/openldap/db.conf');

sub new
{
	my $class = shift;

	my $this = {};
	bless $this, $class;
#        print STDERR "Starting Kazar::vacation\n";
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
        my($row, $mailaddr, $quotedmail, $res, $sth, $path);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;

	$mailaddr = $filter;

#        print STDERR "Looking for : $mailaddr \n";
	
	if (not ($mailaddr =~ s/^\(mail=(.*)\)$/\1/g)) {
		return(0, ());	# Filtre non authorise.
	}

	if (not valid("<".$mailaddr.">")) {
		return(0, ());	# Mail non valide RFC822
	}

	$quotedmail = Kazar::db::quote($mailaddr);

#	print STDERR "Search for $quotedmail\n";

	# Checking in mailboxes

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT vacation.subject as subject,vacation.body as body,mailbox.name as name ".
		"FROM vacation,mailbox ".
                "WHERE vacation.email = $quotedmail AND ".
		"vacation.email=mailbox.username AND ".
                "mailbox.active='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
#	print STDERR "We get some results...\n";
	my $entry = 
		"dn : mail=$mailaddr,ou=vacation,$cf{dn}\n".
		"objectClass : top\n".
		"objectClass : kazarPerson\n";

	if ( $row = $sth->fetchrow_hashref) {
		my $message = $row->{body};
		$message =~ s/\n//g;
		$entry .= "uid : $mailaddr\n";
		$entry .= "cn : $row->{name}\n";
		$entry .= Kazar::db::latin1_to_utf8 ("description : $row->{subject}\n");
		$entry .= Kazar::db::latin1_to_utf8 ("vacationText : $message\n");
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
