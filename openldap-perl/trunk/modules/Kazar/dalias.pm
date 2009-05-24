# $Id: dalias.pm,v 1.2 2009-05-24 11:20:22 kiwi Exp $
# 
# Function to get the real email / domain to lookup 
# case of domains aliases
package Kazar::dalias;

use POSIX;
use strict;
use Kazar::db;

sub realemail
{
        my ($email) = @_;
	my $domain;	# The domain name to check
	my $lmail;	# Left part of the email (before @)
	my ($quoteddomain, $quotedemail);
	my ($sth, $row);

	$domain = $email;
	$lmail = $email;
	$domain =~ s/(.*)\@(.*)$/\2/g;
	$lmail  =~ s/(.*)\@(.*)$/\1/g;

	#print STDERR "Looking from domain $domain\n";

	$quoteddomain = Kazar::db::quote($domain);

	#print STDERR "Looking from quoteddomain $quoteddomain\n";

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT domain FROM domain_alias ".
		"WHERE dalias = $quoteddomain AND ".
		"active='1'"
	);	

	$sth->execute or die "Unable to execute DALIAS query\n";

	if ($row = $sth->fetchrow_hashref) {
		return "$lmail\@$row->{domain}";

	}

        return $email;
}

1;
