#!/usr/bin/perl -w


###################################################
#  File  : mailbox_size.pl
#  Author: Nicolas GORALSKI
#         mailto :nicolas@goralski.fr
#  Date  : Fri Aug  8, 2008 11:48 AM
###################################################


# Version : 1.0
#
# Object :
# This tool calculate mailbox statistics (mail in, out, size, spams...)
#
# Parameters : None
#
#

use strict;
use Switch;


#
# Definition des variables globales
#
use DBI;


#
# Fonctions
#



#
# Programme Principal
#

my $db_user_name = 'dbuser';
my $db_password = 'dbpassword';
my $db_name = 'DataBase Name';
my $db_host = 'DataBase host';
my $virtualhost_folder = "/var/spool/mail/vhosts/";
my ($id, $password);
my $dirsize = 0;

# Make a connection to the Mysql DATABASE.
my $dsn = "DBI:mysql:$db_name:$db_host";
my $dbh = DBI->connect($dsn, $db_user_name, $db_password);


my ($id, $password);
my $dbh = DBI->connect($dsn, $db_user_name, $db_password);

my $sth = $dbh->prepare(qq{
SELECT mailbox.id, mailbox.username, domain.domain, mailbox.quota, mailbox.maildir
FROM mailbox, domain
WHERE mailbox.domain_id=domain.id
});

$sth->execute();

my $dirsize = 0;


my $prev_username = "";

while (my ($user_id, $username, $domain, $quota, $maildir) = $sth->fetchrow_array() ){

	my $fullmaildir = "/data/mail/vhosts/".$maildir."Maildir";
	if ( -e "/data/mail/vhosts/".$maildir."Maildir" ){
		$dirsize = `du -sk $fullmaildir`;
		chomp $dirsize;
		$dirsize =~ s/([0-9].*)\s.*/$1/;
	}
	else{
		$dirsize = 0;
	}


	my $last_date_updated = $dbh->prepare(qq{
SELECT last_date, date
FROM stats_mail_user
WHERE mailbox_id = '$user_id'
ORDER BY last_date DESC
LIMIT 1
});

	$last_date_updated->execute();
	my ($last_ts, $last_date) = $last_date_updated->fetchrow_array();
	$last_date = 0 if ( ! $last_date );
	$last_ts = 0 if ( ! $last_ts );

	my $alias_list = $dbh->prepare(qq{
SELECT address
FROM alias
WHERE goto like '%$username%'
AND address != "$username"
});

	my $info_sup = "";
	my @tab_email_alias = ();
	$alias_list->execute();
	while ( my ($alias) = $alias_list->fetchrow_array() ){
		$info_sup .= " OR mailto.email = '$alias' OR mailfrom.email = '$alias'";
		push (@tab_email_alias, $alias);
	}


	my $requete_mail_info = "
SELECT msgs.size, msgs.content, DATE(FROM_UNIXTIME(msgs.time_num)), msgs.time_num, mailto.email as emailto, mailfrom.email as emailfrom
FROM msgs, maddr as mailfrom , maddr as mailto, msgrcpt
WHERE msgs.mail_id = msgrcpt.mail_id
AND   msgs.time_num > '$last_ts'
AND   msgs.sid = mailfrom.id
AND   msgrcpt.rid = mailto.id
AND   ( mailto.email = '$username' OR mailfrom.email = '$username' $info_sup )
";

	my $mail_info = $dbh->prepare(qq{
$requete_mail_info
});

	$mail_info->execute();

	my $full_date = 0;

	my %user=();


	# Foreach mail in the database

	while ( my ( $size, $content, $time_num, $timestamp, $emailto, $emailfrom ) = $mail_info->fetchrow_array() ){

		my ($year, $month, $day_of_month ) = split /-/, $time_num;
		$month = "0".$month if ( $month < 10 );
		$full_date = $month.$day_of_month.$year;

		if ( $emailfrom eq $username || (grep(/$emailfrom/, @tab_email_alias) ) ){
			$user{$full_date}{'bout'}  += $size;
			$user{$full_date}{mail_out}++;
		}
		if ( $emailto eq $username || (grep(/$emailto/, @tab_email_alias) ) ){
			$user{$full_date}{'bin'} += $size;
			$user{$full_date}{mail_in}++;
		}

		$user{$full_date}{'timestamp'} = $timestamp;

		switch ($content) {
		  case /S/i { $user{$full_date}{'spam'}++ }
		  case /V/ {  $user{$full_date}{'virus'}++ }
		  case /B/ {  $user{$full_date}{'blocked'}++ }
		  case /C/ {  $user{$full_date}{'clean'}++ }
			else {      $user{$full_date}{'malformed'}++ }
		}
	}

	my $requete;

	foreach my $date (sort(keys %user)) {

		my @tab_champ = ('timestamp','bin','bout','spam','virus','clean','blocked','malformed','mail_in','mail_out');
		foreach my $champ (@tab_champ){
			$user{$date}{$champ} = 0 if ( ! $user{$date}{$champ} );
		}


		if ( $date == $last_date) {

			$requete  = "UPDATE stats_mail_user SET last_date='".$user{$date}{'timestamp'}."', bytes_in = bytes_in + ".$user{$date}{'bin'}.", bytes_out = bytes_out + ".$user{$date}{'bout'}.", ";
			$requete .= "spam = spam + ".$user{$date}{'spam'}.",	virus = virus + ".$user{$date}{'virus'}.", clean = clean + ".$user{$date}{'clean'}.", blocked = blocked + ".$user{$date}{'blocked'}.", ";
			$requete .= "malformed = malformed + ".$user{$date}{'malformed'}.", mail_in = mail_in + ".$user{$date}{'mail_in'}.", mail_out = mail_out + ".$user{$date}{'mail_out'}.", mailbox_size='$dirsize', mailbox_quota='$quota' WHERE date='".$last_date."' AND mailbox_id='$user_id'";

		}
		else{

			$requete = "INSERT INTO stats_mail_user VALUES ('$user_id', '$date', '".$user{$date}{'timestamp'}."', '".$user{$date}{'bin'}."', '".$user{$date}{'bout'}."', '".$user{$date}{'mail_in'}."', '".$user{$date}{'mail_out'}."', '".$user{$date}{'spam'}."', '".$user{$date}{'virus'}."', '".$user{$date}{'clean'}."', '".$user{$date}{'blocked'}."', '".$user{$date}{'malformed'}."', mailbox_size='$dirsize', mailbox_quota='$quota' )";

		}

		my $requete_add =  $dbh->prepare(qq{$requete});
		$requete_add->execute();

	}


}

$sth->finish();
