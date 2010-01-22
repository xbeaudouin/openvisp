#!/usr/bin/perl -w


###################################################
#  File  : ftp_log_analyse.pl
#  Author: Nicolas GORALSKI
#         mailto :nicolas.goralski@fox-services.com
#  Date  : 2007-07-11
###################################################


# Version : 1.0
#
# Objectif :
# Analyser les logs de pureftpd et ajouter dans mysql
# les informations sur les download et upload
#
# Parametres : aucun
#
#
# Goal :
# Get the total upload / download per day of account from
# log file
#
# Parameters : none


use strict;
use DBI;


#
# Global definition
#

my $db_user_name = 'root';
my $db_password = '';
my $db_name = '';
my $db_host = '';
my $dirsize = 0;

#
# Program Body
#

# Make a connection to the Mysql DATABASE.
my $dsn = 'DBI:mysql:'.$db_name.':'.$db_host;
my $dbh = DBI->connect($dsn, $db_user_name, $db_password);

my %tab_month = ('Jan','01', 'Feb','02', 'Mar','03', 'Apr','04', 'May','05', 'Jun','06', 'Jul','07', 'Aug','08', 'Sep','09', 'Oct','10', 'Nov','11', 'Dec','12');

my $servername="";

my $line = "";
open (FILE, $ARGV[0]);
my %tableau_get;
my %tableau_put;
my %tableau;
my %tab_date;

my $sql2 = qq{ INSERT INTO ftptransfert_stat(ftpaccount,upload,download,server,date) VALUES ( ?, ?, ?, ?, ?) };
my $sth2 = $dbh->prepare( $sql2 );

while ($line = <FILE>){
	if ( ($line =~ /\] "GET /) || ($line =~ /\] "PUT /) ){

		$line =~ s/  / /g;
		my @info = split / /, $line;
		$info[3] =~ s/^\[//g;

		my @date_full = split /:/, $info[3];
		my ($day, $month, $year) = split /\//, $date_full[0];

		my $month2 = $tab_month{$month};
		my $date = "$year-$month2-$day";
		$tableau{$info[2]} = "";
		if ( $line =~ /\] "GET /) {
			$tableau_get{$info[2]}{$date} += $info[8];
		}
		if ($line =~ /\] "PUT /) {
			$tableau_put{$info[2]}{$date} += $info[8];
		}
		$tab_date{$date} = "";
	}
}

close (FILE);

foreach my $login (keys %tableau) {
	foreach my $date ( keys %tab_date){
		if ( ($tableau_put{$login}{$date}) || ($tableau_get{$login}{$date}) ){
			if ( !($tableau_put{$login}{$date})){ $tableau_put{$login}{$date} = 0; }
			if ( !($tableau_get{$login}{$date})){ $tableau_get{$login}{$date} = 0; }
			$sth2->execute($login, $tableau_put{$login}{$date}, $tableau_get{$login}{$date}, $servername, $date);
		}
		
	}
}

$sth2->finish();

exit;




