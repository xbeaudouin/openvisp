#! /usr/bin/perl
#
# Copyright (c) 2003 SuSE Linux AG Nuernberg, Germany.  All rights reserved.
#
#
# $Id: amavis-prepare.pl,v 1.1 2005/04/13 21:30:18 kiwi Exp $
#

use strict;
use POSIX;
use Net::LDAP;
use Net::LDAP::Util qw( ldap_error_text);

my $base     = "<YOUR_BASEDN_HERE>";
my $rootdn   = "uid=cyrus,".$base;
my $rootpw   = "<YOUR_PASSWORD_HERE>";

my $ldap = Net::LDAP->new("localhost", version => 3);
die "unable to contact LDAP server" if ! defined $ldap;

my $mesg = $ldap->bind ( dn => "$rootdn",
			 password => "$rootpw" );
    
die "unable to bind LDAP server: ".ldap_error_text($mesg->code) if $mesg->code != 0;

$mesg = $ldap->search( base => $base,
		       scope => "one",
		       filter=> "(&(objectclass=posixaccount)(uid=*)(!(uid=*\$)))",
		       attrs=> [ "spamcheck", "amavisBypassSpamChecks", "uid" ] );
die "unable to search LDAP server: ".ldap_error_text($mesg->code) if $mesg->count <= 0;

foreach my $e ( $mesg->all_entries ) {
    my $uid       = join(//,$e->get_value("uid"));
    my $spamcheck = join(//,$e->get_value("spamcheck"));
    my $abypass   = join(//,$e->get_value("amavisBypassSpamChecks"));
    #print "<$uid> <$spamcheck> <$abypass>\n";
    if( $spamcheck eq "on" ) {
	print "activating amavisd-new spamchecking for user <$uid>\n";
	if( defined $abypass && $abypass ne "" && $abypass ne "FALSE" ) {
	    $e->replace( "amavisBypassSpamChecks" => "FALSE" );
	    $mesg = $e->update($ldap);
	    die "(spamcheck on) unable to modify: ".ldap_error_text($mesg->code) if $mesg->code;
	} elsif( (! defined $abypass) || $abypass eq "" ) {
	    $e->add( "amavisBypassSpamChecks" => "FALSE" );
	    $mesg = $e->update($ldap);
	    die "(spamcheck on) unable to modify: ".ldap_error_text($mesg->code) if $mesg->code;
	}
    } else {
	if( defined $abypass && $abypass ne "" && $abypass ne "TRUE" ) {
	    $e->replace( "amavisBypassSpamChecks" => "TRUE" );
	    $mesg = $e->update($ldap);
	    die "(spamcheck off) unable to modify: ".ldap_error_text($mesg->code) if $mesg->code;
	} elsif( (! defined $abypass) || $abypass eq "") {
	    $e->add( "amavisBypassSpamChecks" => "TRUE" );
	    $mesg = $e->update($ldap);
	    die "(spamcheck off) unable to modify: ".ldap_error_text($mesg->code) if $mesg->code;
	}
    }
}



