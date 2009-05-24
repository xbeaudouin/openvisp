# $Id: ftp.pm,v 1.3 2009-05-24 11:20:22 kiwi Exp $
# 
package Kazar::ftp;

use Config::Fast;
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
#        print STDERR "Starting Kazar::ftp\n";
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
        my($row, $ftpaddr, $quotedftp, $res, $sth, $path);
	my($base, $scope, $deref, $sizeLim, $timeLim, $filter, $attrOnly, @attrs ) = @_;

	$ftpaddr = $filter;

        print STDERR "Looking for : $ftpaddr \n";
	
	if (not ($ftpaddr =~ s/^\(uid=(.*)\)$/\1/g)) {
		return(0, ());	# Filtre non authorise.
	}

	# Checking if we have a normal ftp account
	# eg. that gets only 7bits char : [0-9], [A-Z], [a-z], '-', '.'
	if($ftpaddr =~ /[^[:alnum:]%.-]/) {
		# non RFC char exiting
		return (0, @match_entries);
	}

	$quotedftp = Kazar::db::quote($ftpaddr);

	print STDERR "Search for $quotedftp\n";

	# Checking in mailboxes

	$sth = Kazar::db::prepare(
		"SELECT DISTINCT Password,Uid,Gid,ULBandwidth,DLBandwidth,".
		"comment,QuotaSize,QuotaFiles,FType,FQDN,Dir,status FROM ftpd ".
                "WHERE User = $quotedftp"
#		"comment,QuotaSize,QuotaFiles,FType,FQDN,Dir FROM ftpd ".
#                "WHERE User = $quotedftp AND ".
#                "status='1'"
	);
	$sth->execute or die "Unable to execute query\n";
	
	# We get data, then format output... :)
	#print STDERR "We get some results...\n";
	my $entry = 
		"dn: uid=$ftpaddr,ou=ftp,$cf{dn}\n".
		"objectClass: top\n".
		"objectClass: kazarPerson\n".
		"objectClass: PureFTPdUser\n";

	if ( $row = $sth->fetchrow_hashref) {
		$entry .= "uid: $ftpaddr\n";
		$entry .= Kazar::db::latin1_to_utf8 ("description: $row->{comment}\n");
		$entry .= "uidNumber: $row->{Uid}\n";
		$entry .= "gidNumber: $row->{Gid}\n";
		$entry .= "FTPuid: $row->{Uid}\n";
		$entry .= "FTPgid: $row->{Gid}\n";
		if ( $row->{status} == "1") {
			$entry .= "FTPstatus: enabled\n";
		} else {
			$entry .= "FTPstatus: disabled\n";
		}
		$entry .= "userPassword: $row->{Password}\n";
		if($cf{hdhash} == "1") {
                	$path .= Kazar::hash::hashed($row->{Dir});
		}
		else {
                	$path .= $row->{Dir};
		}
		$entry .= "homeDirectory: $cf{ftphomeroot}/$row->{FType}/$path/\n";
		if ($row->{QuotaFiles} != "0") {
			$entry .= "FTPQuotaFiles: $row->{QuotaFiles}\n";
		}
		if ($row->{QuotaSize} != "0") {
			$entry .= "FTPQuotaMBytes: $row->{QuotaSize}\n";
		}
		if ($row->{ULBandwith} != "0") {
			$entry .= "FTPUploadBandwidth: $row->{ULBandwidth}\n";
		}
		if ($row->{DLBandwith} != "0") {
			$entry .= "FTPDownloadBandwidth: $row->{DLBandwidth}\n";
		}
		if ($row->{FType} == "http") {
			$entry .= "associatedDomain: $row->{FQDN}\n";
		}
	}

	#print STDERR "Sending -> $entry\n";

	push @match_entries, $entry;
	$sth->finish;

	return(0, @match_entries);

}

sub bind 
{
  return 0;
}

1;
