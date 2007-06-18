# $Id: uce.pm,v 1.2 2007-06-18 16:13:08 kiwi Exp $
# 
package Kazar::uce;

use Config::Fast;
use Mail::RFC822::Address qw(valid);
use POSIX;
use strict;
use Kazar::db;
use Kazar::dalias;


# Configuration file handled by Config::Fast;
my %cf = fastconfig('/usr/local/openldap/etc/openldap/db.conf');

sub new
{
	my $class = shift;

	my $this = {};
	bless $this, $class;
#        print STDERR "Starting Kazar::uce\n";
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
	my $todo;
        my($row, $mailaddr, $quotedmail, $res, $sth, $path, $searchmail);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;
#        print STDERR "====$filter====\n";

#	print STDERR "==> Want : $todo \n";

	$mailaddr = $filter;

#        print STDERR "Looking for : $mailaddr \n";
	
	if (not ($mailaddr =~ s/^\(uid=(.*)\)$/\1/g)) {
		return(0, ());	# Filtre non authorise.
	}

	if (not valid("<".$mailaddr.">")) {
		# Mail is invalid, try for catchall entries
		if(not valid("<none".$mailaddr.">")) {
			# Doesn't seems to be a valid mail or 
			# even a catchall then throw out
			return(0, ());	# Mail non valide RFC822
		}
	}

	$searchmail = Kazar::dalias::realemail($mailaddr);

	$quotedmail = Kazar::db::quote($searchmail);

#	print STDERR "Search for $quotedmail\n";

	# Checking in mailboxes

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT policy FROM policy ".
                "WHERE address = $quotedmail AND ".
                "active='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
	my $entry = 
		"dn : uid=$mailaddr,ou=uce,$cf{dn}\n".
		"objectClass : top\n".
		"objectClass : kazarPerson\n";

	if ( $row = $sth->fetchrow_hashref) {
		$entry .= "uid : $mailaddr\n";
		$entry .= "mailPolicy : $row->{policy}_restriction\n";
	} else {
	# We didn't get any restriction, so trying to get default value
		my $domain;
		my $quoteddomain;
		# print STDERR " -> Try to get default policy \n";

		$domain = $mailaddr;
		$domain =~ s/(.*)\@(.*)$/\2/g;

		#print STDERR "domaine -> $domain \n";

		$quoteddomain = Kazar::db::quote($domain);

		$sth = Kazar::db::prepare( 
			"SELECT DISTINCT policy FROM default_policy ".
			"WHERE domain = $quoteddomain AND ".
			"active='1'"
		);

		$sth->execute or die "Unable to execute query\n";

		if ($row = $sth->fetchrow_hashref) {
			$entry .= "uid : $mailaddr\n";
			$entry .= "mailPolicy : $row->{policy}_restriction\n";
		}
	}

	push @match_entries, $entry;
	$sth->finish;

	return(0, @match_entries);

}

sub bind 
{
  return 0;
}

1;
