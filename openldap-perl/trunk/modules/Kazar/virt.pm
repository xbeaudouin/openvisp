# $Id: virt.pm,v 1.3 2009-05-24 11:20:22 kiwi Exp $
# 
package Kazar::virt;

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
#        print STDERR "Starting Kazar::virt\n";
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
        my(@row, $mailaddr, $quotedmail, $res, $sth, $searchmail, $searchmail);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;
#        print STDERR "====$filter====\n";
	
	$mailaddr = $filter;
	if (not ($mailaddr =~ s/^\(mail=(.*)\)$/\1/g)) {
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

	#print STDERR " We got a mail $mailaddr, checking if this an domain alias\n";
	$searchmail = Kazar::dalias::realemail($mailaddr);

	#print STDERR " Realmail $searchmail \n";

	$quotedmail = Kazar::db::quote($searchmail);

#	print STDERR "Search for $quotedmail\n";

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT goto FROM alias ".
                "WHERE address = $quotedmail AND ".
                "active='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
	my $entry = 
		"dn: uid=$mailaddr,ou=virt,$cf{dn}\n".
		"objectClass: top\n".
		"objectClass: kazarPerson\n".
		"uid: $mailaddr\n";

	my $i = 0;
	@row = ();
	while($row[$i] = $sth->fetchrow_array) {
		my $key;
		foreach $key (split(',',$row[$i])) {
			$entry .= "mail: $key\n";
		}
		$i++;
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
