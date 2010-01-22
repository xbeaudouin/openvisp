#!/usr/bin/perl

# $Id: update_filter.pl,v 1.0 Nicolas GORALSKI

$| = 1;

use DBI;
use File::stat;
use IO::File;

# Remove the # in front of this line when you have 
# edited the variables below.
$has_edited_source = 1;
#
# Set up the proper variables to permit database access
#
$serverName = "";
$serverPort = "3306";
$serverUser = "";
$serverPass = "";
$serverDb   = "";

$mail_base_dir = "/var/spool/mail/vhosts";


if (!defined($has_edited_source)) {
	print "Please edit this file and configure it first.\n";
	print "This program is $0\n";
	exit 1;
}



sub home_dir
{
  my ($email) = @_;
  my $ldbh = DBI->connect("DBI:mysql:database=$serverDb;host=$serverName;port=$serverPort",$serverUser,$serverPass);
  if (not $ldbh) {
	die ("Unable to connect to openvisp database.");
  }
  my $lrecords = $ldbh->prepare("SELECT DISTINCT maildir FROM mailbox WHERE username = '$email'");
  $lrecords->execute;

  while (@ldata  = $lrecords->fetchrow_array) {
     return $ldata[0];
  }
  $lrecords->finish;
  return 0;
}


$dbh = DBI->connect("DBI:mysql:database=$serverDb;host=$serverName;port=$serverPort",$serverUser,$serverPass);
if (not $dbh) {
	die "Unable to connect to the database.  Please check your connection variables. (Bad password? Incorrect perms?)";
}

# Look for updated user email filter.

$records = $dbh->prepare("SELECT username, id, maildir FROM mailbox WHERE update_filter = '1' and active = '1' ORDER BY username");
$records->execute;
if (not $records) {
	exit (0);
}

#
# Pull out the data row by row and format it
#

while ($data = $records->fetchrow_hashref) {

	my $home = $mail_base_dir . "/" . $data->{'maildir'} . "Maildir/";

	if ( $data->{'update_filter'} == 2 ) {
		if ( -e $home."filter" ) {
			unlink $home."filter";
		}
		$updatefilter = $dbh->prepare("update mailbox set update_filter = '0' where username = '$data->{'username'}' ");
		$updatefilter->execute;

	}

	else {

		$filterrecords = $dbh->prepare("SELECT filter.*, filter_field.fieldname, filteraction_field.actionname FROM filter, filter_field, filteraction_field WHERE filter.mailbox_id ='".$data->{'id'}."' and filter.active='1' and filter.filter_field_id=filter_field.id and filter.filteraction_id=filteraction_field.id"); 
		$filterrecords->execute;

 		open ( FILTER, "> ".$home."filter");
		
		while ( $filter = $filterrecords->fetchrow_hashref ) {
			print FILTER "# Filtername : ".$filter->{'filtername'}." \n";
			print FILTER "$filter->{'fieldname'}: /$filter->{'fieldvalue'}/ $filter->{'actionname'} $filter->{'destination'}\n";
			print FILTER "\n";
		}


	}

	close (FILTER);	



#	print "update mailbox set update_filter = '0' where username = '$data->{'username'}' \n";
# 	$updatefilter = $dbh->prepare("update mailbox set update_filter = '0' where username = '$data->{'username'}' ");
# 	$updatefilter->execute;

	
}

#
$records->finish;
