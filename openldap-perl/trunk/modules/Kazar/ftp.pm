# $Id: ftp.pm,v 1.1 2006-06-26 22:28:58 kiwi Exp $
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
	print STDERR "We get some results...\n";
	my $entry = 
		"dn : uid=$ftpaddr,ou=ftp,$cf{dn}\n\t".
		"objectClass : top\n\t".
		"objectClass : kazarPerson\n\t".
		"objectClass : PureFTPdUser\n\t";

	if ( $row = $sth->fetchrow_hashref) {
		$entry .= "uid : $ftpaddr\n\t";
		$entry .= Kazar::db::latin1_to_utf8 ("description : $row->{comment}\n\t");
		$entry .= "uidNumber : $row->{Uid}\n\t";
		$entry .= "gidNumber : $row->{Gid}\n\t";
		$entry .= "FTPuid : $row->{Uid}\n\t";
		$entry .= "FTPgid : $row->{Gid}\n\t";
		if ( $row->{status} == "1") {
			$entry .= "FTPstatus : enabled\n\t";
		} else {
			$entry .= "FTPstatus : disabled\n\t";
		}
		$entry .= "userPassword : $row->{Password}\n\t";
		if($cf{hdhash} == "1") {
                	$path .= Kazar::hash::hashed($row->{Dir});
		}
		else {
                	$path .= $row->{Dir};
		}
		$entry .= "homeDirectory : $cf{ftphomeroot}/$row->{FType}/$path/\n\t";
		if ($row->{QuotaFiles} != "0") {
			$entry .= "FTPQuotaFiles : $row->{QuotaFiles}\n\t";
		}
		if ($row->{QuotaSize} != "0") {
			$entry .= "FTPQuotaMBytes : $row->{QuotaSize}\n\t";
		}
		if ($row->{ULBandwith} != "0") {
			$entry .= "FTPUploadBandwidth : $row->{ULBandwidth}\n\t";
		}
		if ($row->{DLBandwith} != "0") {
			$entry .= "FTPDownloadBandwidth : $row->{DLBandwidth}\n\t";
		}
		if ($row->{FType} == "http") {
			$entry .= "associatedDomain : $row->{FQDN}\n\t";
		}
	}

	print STDERR "Sending -> $entry\n";

	push @match_entries, $entry;
	$sth->finish;

	return(0, @match_entries);

}

sub bind 
{
  return 0;
}

1;
