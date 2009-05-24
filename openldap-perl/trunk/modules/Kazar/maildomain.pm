# $Id: maildomain.pm,v 1.3 2009-05-24 11:20:22 kiwi Exp $
# 
package Kazar::maildomain;

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
#        print STDERR "Starting Kazar::maildomain\n";
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
	my $entry;
	my ($domain, $quoteddomain, @row, $sth);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;
#        print STDERR "====$filter====\n";
	
        $filter =~ s/\(|\)//g;	# removing '(' and ')'
	$_ = $domain = $filter;

	if(/^associatedDomain=.*/) {
		$domain =~ s/^associatedDomain=(.*)/\1/g;
		$domain =~ s/\*/%/g;
	}
	elsif (/^objectClass=\*$/) {
		$domain = "%";
	}
	else {
		# unknown filter, going away
		return (0, @match_entries);
	}

	# Checking now if what we are looking for is a normal domain
	# eg. that gets only RFC char : [0-9], [A-Z], [a-z], '-', '.'
	# with keeping pattern matching ("%").

	if($domain =~ /[^[:alnum:]%.-]/) {
		# non RFC char exiting
		return (0, @match_entries);
	}
        #print STDERR "====$domain====\n";


	$quoteddomain = Kazar::db::quote($domain);

	#print STDERR "Search for $quoteddomain\n";

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT domain FROM domain ".
                "WHERE domain LIKE $quoteddomain AND ".
                "active='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
	$entry = 
		"dn: associatedDomain=$domain,ou=maildomain,$cf{dn}\n".
		"objectClass: top\n".
		"objectClass: kazarPerson\n";

	my $i = 0;
	@row = ();
	while($row[$i] = $sth->fetchrow_array) {
		$entry .= "associatedDomain: $row[$i]\n";
		$i++;
	}
	if ($i >= 1) {
		$entry .= "MXTransport: $cf{mxtransport}\n";
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
