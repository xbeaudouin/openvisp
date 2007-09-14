# $Id: mailboxes.pm,v 1.3 2007-09-14 14:26:54 kiwi Exp $
# 
package Kazar::mailboxes;

use Config::Fast;
use Mail::RFC822::Address qw(valid);
use POSIX;
use strict;
use Kazar::hash;
use Kazar::db;
use Kazar::dalias;


# Configuration file handled by Config::Fast;
my %cf = fastconfig('/usr/local/openldap/etc/openldap/db.conf');

sub new
{
	my $class = shift;

	my $this = {};
	bless $this, $class;
#        print STDERR "Starting Kazar::mailboxes\n";
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

	# Checking about what looking for 
	# mail= foo -> postfix / pll VDA delivery
	# uid= foo  -> pop3/imap that supports @ -> %
	$todo = $filter;
	$todo =~ s/^\((.*)=.*\)$/\1/g;

	#print STDERR "==> Want : $todo \n";

	$mailaddr = $filter;

	if ($todo =~ "uid") {
		# replacing % -> @
		#print STDERR "We get a requestion for UID\n";
		$mailaddr =~ s/\%/\@/g;
	}

        #print STDERR "Looking for : $mailaddr \n";
	
	if (not ($mailaddr =~ s/^\(uid=(.*)\)$/\1/g)) {
		if(not ($mailaddr =~ s/^\(mail=(.*)\)$/\1/g)) {
			return(0, ());	# Filtre non authorise.
		}
	}

	if (not valid("<".$mailaddr.">")) {
		return(0, ());	# Mail non valide RFC822
	}
	
	#print STDERR "Looking alias for $mailaddr\n";

	$searchmail = Kazar::dalias::realemail($mailaddr);

	#print STDERR "Got real mail $searchmail\n";

	$quotedmail = Kazar::db::quote($searchmail);

	#print STDERR "Search for $quotedmail\n";

	# Checking in mailboxes

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT username,password,name,maildir,domain,quota FROM mailbox ".
                "WHERE username = $quotedmail AND ".
                "active='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
	#print STDERR "We get some results...\n";
	my $entry = 
		"dn : uid=$mailaddr,ou=mailboxes,$cf{dn}\n".
		"objectClass : top\n".
		"objectClass : kazarPerson\n";

	if ( $row = $sth->fetchrow_hashref) {
		$entry .= "uid : $mailaddr\n";
		$entry .= "cn : $row->{name}\n";
		$entry .= Kazar::db::latin1_to_utf8 ("description : $row->{name}\n");
		$entry .= "uidNumber : $cf{defaultuid}\n";
		$entry .= "gidNumber : $cf{defaultgid}\n";
		$entry .= "userPassword : $row->{password}\n";
		if($cf{domainprepend} == "1") {
			if($cf{dhash} == "1") {
				$path = Kazar::hash::hashed($row->{domain});
			}
			else {
				$path = $row->{domain};
			}
                   	$path .= "/";
		}
		if($cf{hdhash} == "1") {
                	$path .= Kazar::hash::hashed($row->{maildir});
		}
		else {
                	$path .= $row->{maildir};
		}
		$entry .= "homeDirectory : $path\n";
		$entry .= "mailhomeDirectory : $cf{mailhomeroot}/$path\n";
		$entry .= "CouriermailhomeDirectory : $cf{mailhomeroot}/$path/Maildir/\n";
		$entry .= "mailQuota : $row->{quota}\n";
		my $quotaKB = $row->{quota} / 1000;
		$entry .= "mailQuotaKB : $quotaKB\n";
		$entry .= "CouriermailQuota : $row->{quota}S\n";
		$entry .= "associatedDomain : $row->{domain}\n";
	}

	#print STDERR "Sending -> $entry\n";

	push @match_entries, $entry;
	$sth->finish;

	return(0, @match_entries);

}

sub compare
{
  return 0;
}

sub modify 
{
  return 0;
}

sub add
{
  return 0;
}

sub modrdn
{
  return 0;
}

sub delete
{
  return 0;
}

sub config 
{
  return 0;
}

sub bind 
{
  return 0;
}

1;

