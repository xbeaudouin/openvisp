#!/usr/bin/perl -w


###################################################
#  File  : mailbox_size.pl
#  Author: Nicolas GORALSKI
#         mailto :nicolas.goralski@fox-services.com
#  Date  : 2006-05-19
###################################################


# Version : 1.0
#
# Objectif :
# Recuperation de la liste des utilisateurs de la messagerie
# et calcul de la taille de leur dossier de messagerie
# Le resultat sera depose dans la base de donnee SQL
#
# Parametres : aucun
#
#
# Goal :
# Get the list of email users and the size of their mail folder
# All the result will be publish in the database
#
# Parameters : none


use strict;
use DBI;


#
# Global definition
#

my $db_user_name = 'dbuser';
my $db_password = 'dbpassword';
my $db_name = 'DataBase Name';
my $db_host = 'DataBase host';
my $virtualhost_folder = "/var/spool/mail/vhosts/";
my ($id, $password);
my $dirsize = 0;

#
# Program Body
#

# Make a connection to the Mysql DATABASE.
my $dsn = "DBI:mysql:$db_name:$db_host";
my $dbh = DBI->connect($dsn, $db_user_name, $db_password);

# Get today time and date
my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime time;
$year += 1900;
$mday = "0".$mday if ($mday < 10);
$mon ++;
$mon = "0".$mon if ($mon < 10);
$hour = "0".$hour if ($hour < 10);
$min = "0".$min if ($min < 10);
$sec = "0".$sec if ($sec < 10);


# SQL Request to have the list of users in the database
my $sth = $dbh->prepare(qq{
select username, maildir,id
from mailbox
where active=1
});

# Execution of the Request
$sth->execute();


# Prepare the request to insert mailbox size into the table
my $sql2 = qq{ INSERT INTO stats_mailbox(mailbox_id,size,date) VALUES ( ?, ?, ?) };
my $sth2 = $dbh->prepare( $sql2 );

# For each username and maildir we found.
while (my ($username, $maildir, $id) = $sth->fetchrow_array() ){

	# Compose the maildir name
	my $fullmaildir = $virtualhost_folder.$maildir."Maildir";

	# Test if the maildir exists.
	if ( -e $virtualhost_folder.$maildir."Maildir" ){
		# Get the size of the maildir.
		$dirsize = `du -sk $fullmaildir`;
		chomp $dirsize;
		# We need only the numerical size nothing else.
		$dirsize =~ s/([0-9].*)\s.*/$1/;
	}
	else{
		$dirsize = 0;
	}

	# Insert into the database the result.
	$sth2->execute($id, $dirsize,"$year-$mon-$mday $hour:$min:$sec" );

}

# Close the connection.
$sth->finish();
$sth2->finish()

