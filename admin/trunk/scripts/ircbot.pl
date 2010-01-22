#!/usr/bin/perl

# modobot is an IRC bot which allow you to validate
# VHFFS objects through IRC
# Written by Florent Bayle and Sylvain Rochet (this is very important to add my name here, to become famous very soon)

use strict;
use utf8;
use POSIX qw(locale_h);
use locale;
use warnings;
use Locale::gettext;
use Encode;

use lib '/usr/share/vhffs/api';
use Vhffs::User;
use Vhffs::Group;
use Vhffs::Main;
use Vhffs::Constants;
use Vhffs::Object;
use Vhffs::ObjectFactory;
use Vhffs::Stats;

use Net::IRC;
use Net::DNS;
use Text::Wrapper;

binmode STDOUT, ':utf8';

my $bot;
my $cmpt = 0;

my %oldobjects = ();

my $irc=new Net::IRC;

# Connections to servers

my $vhffs = init Vhffs::Main;
exit 1 unless $vhffs;

my $configirc = $vhffs->get_config->get_irc;
my $chan = $configirc->{modobot_channel};

my $conn=$irc->newconn(Nick     =>  $configirc->{modobot_name},
                       Server   =>  $configirc->{modobot_server},
                       Port     =>  $configirc->{modobot_port},
                       Username =>  'modobot',
                       Ircname  =>  'VHFFS Moderation bot' );
exit 2 unless $conn;


sub deletenl
{
    ($_) = @_;
    $_ =~ s/\n/ /g;
    $_ =~ s/\r//g;
    return $_;
}


sub get_moderation_list
{
	my $seq = shift;  # set that to 1 in order to display only new entries
	my @return;
	my $objects = Vhffs::Object::getall( $vhffs, undef, Vhffs::Constants::WAITING_FOR_VALIDATION );
	if( defined $objects )  {
		foreach my $obj ( @{$objects} ) {
			next if( $seq && exists( ${%oldobjects}{$obj->get_oid} ) );
				push(@return, $obj->get_oid);
			${%oldobjects}{$obj->get_oid} = '';
		}
	}
	return @return;
}

sub list_moderation
{
	my $seq = shift;  # set that to 1 in order to display only new entries

	my $objects = Vhffs::Object::getall( $vhffs, undef, Vhffs::Constants::WAITING_FOR_VALIDATION );
	if( defined $objects )  {
		foreach my $obj ( @{$objects} ) {
			next if( $seq && exists( ${%oldobjects}{$obj->get_oid} ) );

			my $user = $obj->get_user;
			my $group = $obj->get_group;
			my $object = Vhffs::ObjectFactory::fetch_object( $vhffs , $obj->{object_id} );

			my $msg = Vhffs::Functions::type_string_from_type_id( $obj->{type} ).':   '.$obj->get_oid.'   '.$user->get_username;
			$msg .= ' ('.$user->get_note.')' if( $vhffs->get_config->get_users->{'use_notation'} eq 'yes' );
			$msg .= ' ['.$user->get_lang.']   '.$group->get_groupname.'   '.$object->get_label.'   '.$obj->get_description;
			irc_msg( $msg );

			${%oldobjects}{$obj->get_oid} = '';
		}
	}
}

sub moderate
{
	my $oid = shift;
	my $status = shift; # 0 = refuse , 1 = accept
	my $reason = shift;

	my $object = Vhffs::ObjectFactory::fetch_object( $vhffs , $oid );

	unless( defined $object )
	{
		irc_msg ('Error : Cannot fetch object');
		return ( -1 );
	}
	elsif( $object->get_status != Vhffs::Constants::WAITING_FOR_VALIDATION )
	{
		irc_msg ('Error : Object is not waiting for validation');
		return ( -2 );
	}
	else
	{
		if( $status == 1 )  {
			if( $object->moderate_accept < 0 )  {
				irc_msg('Error while committing changes');
			}
			else {
				irc_msg( 'Object '.$oid.' accepted' );
			}
		}
		else  {
			my $wfreason;
			my $charset;
			eval { $wfreason = Encode::decode_utf8( $reason , Encode::FB_CROAK ); };
			if( $@ )  {
				#decoding from utf8 failed, falling back to iso
				$wfreason = Encode::decode('iso-8859-1', $reason);
				$charset = 'ISO';
			}
			else  {
				$charset = 'UTF-8';
			}

			if( $object->moderate_refuse( $wfreason ) < 0 )  {
				irc_msg('Error while committing changes');
			}
			else {
				irc_msg( 'Object '.$oid.' refused ('.$charset.' detected)' );
			}
		}
		delete ${%oldobjects}{$oid};
	}
	return 0;
}



sub on_ping {
    my ($self, $event) = @_;
    my $nick = $event->nick;
    my $timestamp = $event->{'args'}[0];

    $self->ctcp_reply($nick, 'PING ' . $timestamp);
    print "[ping-from]  : {$nick}\n"
} # on_ping

sub on_ping_reply {
    my ($self, $event) = @_;
    my ($args) = ($event->args)[1];
    my ($nick) = $event->nick;

    $args = time - $args;
    print "[ping-rsp]  : from $nick: $args sec.\n";
} # on_ping_reply

sub on_cversion {
    my ($self, $event) = @_;
    my ($nick, $mynick) = ($event->nick, $self->nick);
    my $reply = "Vhffs Bot";
    print "[ctcp-version] : <$nick>\n";
    $self->ctcp_reply($nick, join(' ', ($event->args), $reply));
} # on_cversion

sub on_connect {
    my $self=shift;
    $bot=$self;
    $self->join($chan);
    irc_msg ("--> $configirc->{modobot_name} started");
    &CatchAlrm();
} # on_connect

sub is_modo
{
    my $user = Vhffs::User::get_by_username( $vhffs, shift );
    return ( (defined $user) && ($user->is_moderator == 1 || $user->is_admin == 1) );
} # is_modo

sub get_desc
{
    my $name = shift;
    my $group;
    if (! defined ($group= Vhffs::Group::get_by_groupname( $vhffs , $name ) ) )
    {
        irc_msg ("$name : No such group");
    }
    else
    {
        irc_msg ("$name : " . $group->get_description );
    }
}

sub get_gall
{
    my $name = shift;
    my $group;
    if (! defined ($group= Vhffs::Group::get_by_groupname( $vhffs , $name ) ) )
    {
       irc_msg ("$name : No such group");
    }
    else
    {
        my $group = Vhffs::Group::getall( $vhffs , $name );
	irc_msg( $group );
    }
}

sub find_group_from_web
{
    my $name = shift;
    my $web;
    if (! defined ($web = Vhffs::Services::Web::get_by_servername( $vhffs , $name)))
   {
       irc_msg ("$name : No such website");
   }
   else
   {
       irc_msg ("$name => group  ".Vhffs::Group::get_name_by_gid($vhffs,$web->get_owner_gid));
   }

}

sub owner_info
{
  my $groupname = shift;
  my $group;
  if (! defined ($group = Vhffs::Group::get_by_groupname( $vhffs , $groupname)))
  {
    irc_msg ("$groupname : No such groupname");
  }
  else
  {
    my $user = Vhffs::User::get_by_uid($vhffs, $group->get_owner_uid);
    if ( defined $user )
    {
      irc_msg ($groupname.' is owned by '.$user->get_username.' - '.$user->get_firstname.' '.$user->get_lastname.' <'.$user->get_mail.'>' );
    }
    else
    {
      irc_msg ($groupname." : error fetching user");}
    }
}

sub fetch_usergroup
{
	my $groupname = shift;
	my $group = Vhffs::Group::get_by_groupname( $vhffs , $groupname );
	unless( defined $group )
	{
		irc_msg ($groupname.' : No such group');
		return;
	}

	my $users = Vhffs::Group::get_users( $group );
	my $list = '';

	foreach ( @{$users} )
	{
		$list .= '@' if( $_->get_username eq $group->get_owner_username );
		$list .= $_->get_username.' ';
	}

	irc_msg( $list );
}


sub quotacheck
{
 my $limit = shift;
 my $list = Vhffs::Group::getall_quotalimit($vhffs,$limit);
 my $temp;
 foreach $temp ( @{$list} )
  {
  irc_msg("Group ".$temp->get_groupname.": ".$temp->get_quota_used." / ".$temp->get_quota);
  }
}

sub get_quota
{
        my $groupname = shift;
        my $group = Vhffs::Group::get_by_groupname( $vhffs , $groupname );
        unless( defined $group )
        {
                irc_msg ($groupname.' : No such group');
                return;
        }
  irc_msg("Group ".$groupname.": ".$group->get_quota_used." / ".$group->get_quota);

}

sub set_quota
{
        my $groupname = shift;
        my $quotavalue = shift;
        my $group = Vhffs::Group::get_by_groupname( $vhffs , $groupname );
        unless( defined $group )
        {
                irc_msg ($groupname.' : No such group');
                return;
        }
my $old_quota = $group->get_quota;
$group->set_quota($quotavalue);
irc_msg("Group ".$groupname.": Setting quota from ".$old_quota." to ".$group->get_quota) if ($group->commit >= 0);

}

sub irc_msg
{
    my $text = shift;
    $text = deletenl ($text);
    my $wrapper = Text::Wrapper->new(columns => 300);
    my @text = split (/\n/, $wrapper->wrap($text));
    while ($text = shift @text)
    {
        $bot->privmsg($chan, $text);
        ## sleep 0.1; -> useless
        ## sleep 1 if (($cmpt%10) == 9); -> useless
        $cmpt++;
        $cmpt = 0 if ($cmpt == 30);
    }
}

sub on_public {
    my ($self, $event)=@_;
    my ($nick, $mynick)=($event->nick, $self->nick);
    my $texte=$event->{args}[0];

    if ($texte =~ m/^${mynick}: accept [0-9]+$/)
    {
        if (is_modo ($nick) == 1)
	{
	    $texte =~ s/^${mynick}: accept //;
            moderate( $texte , 1 );
	}
    }
    elsif ($texte =~ m/^${mynick}: acceptall$/)
    {
        if (is_modo ($nick) == 1)
        {
	    foreach my $objid ( get_moderation_list() ) {	
	        moderate( $objid , 1 );
	    }
        }
    }
    elsif ($texte =~ m/^${mynick}: refuse [0-9]+ .*$/)
    {
        if (is_modo ($nick) == 1)
	{
	    my $oid = $texte;
	    my $raison = $texte;
	    $raison =~ s/^${mynick}: refuse [0-9]+ //;
	    $oid =~ s/^${mynick}: refuse ([0-9]+) .*$/$1/;
            moderate( $oid , 0 , $raison);
	}
    }
    elsif ($texte =~ m/^${mynick}: help$/)
    {
        irc_msg("Commands :");
        irc_msg("help - show this help");
        irc_msg("accept <oid> - accept object with id <oid>");
        irc_msg("acceptall - accept all objects in moderation list");
        irc_msg("refuse <oid> <reason> - refuse object with id <oid> for reason <reason>");
	irc_msg("list - force listing of all objects waiting for moderation");
	irc_msg("desc <group> - give the description of <group>");
        irc_msg("web2group <website> - give the groupe name of <website>");
        irc_msg("owner <group> - give owner information of <group>");
        irc_msg("lsgroup <group> - give the list of users of <group>");
        irc_msg("quotacheck <limit> - give the list of <limit> users where quota limit nearly reach "); 
        irc_msg("getquota <group> - give quota for <group>");
        irc_msg("setquota <group> <newquota> - change quota for <group> to <newquota>");
	irc_msg("whois <domain> - give NS for <domain>");
	irc_msg("stats - give vhffs stats");
	irc_msg("coffea - give a coffea");
   }
    elsif ($texte =~ m/^${mynick}: list$/)
    {
	list_moderation( 0 );
    }
    elsif ($texte =~ m/^${mynick}: desc [a-z0-9]+$/)
    {
        my $groupid = $texte;
	$groupid =~ s/^${mynick}: desc //;
        get_desc ($groupid);
    }
    elsif ($texte =~ m/^${mynick}: test [a-z0-9]+$/)
    {
	my $groupid = $texte;
	$groupid =~ s/^${mynick}: test //;
	get_gall ($groupid);
    }
    elsif ($texte =~ m/^${mynick}: web2group [a-z0-9\.\-]+$/)
    {
        my $webtogroup = $texte;
        $webtogroup =~ s/^${mynick}: web2group //;
        find_group_from_web($webtogroup);
    }

    elsif ($texte =~ m/^${mynick}: owner [a-z0-9]+$/)
    {
        my $groupid = $texte;
        $groupid =~ s/^${mynick}: owner //;
        owner_info ($groupid);
    }

    elsif ($texte =~ m/^${mynick}: lsgroup [a-z0-9]+$/)
    {
        my $groupid = $texte;
        $groupid =~ s/^${mynick}: lsgroup //;
        fetch_usergroup ($groupid);
    }
    elsif ($texte =~ m/^${mynick}: quotacheck [0-9]+$/)
    {
        my $limit = $texte;
        $limit =~ s/^${mynick}: quotacheck //;
        quotacheck($limit);
    }

    elsif ($texte =~ m/^${mynick}: getquota [a-z0-9]+$/)
    {
        my $groupquota = $texte;
        $groupquota =~ s/^${mynick}: getquota //;
        get_quota($groupquota);
    }

    elsif ($texte =~ m/^${mynick}: setquota [a-z0-9]+ .*$/)
    {
        if (is_modo ($nick) == 1)
        {
            my $groupname = $texte;
            my $quotavalue = $texte;
            $quotavalue =~ s/^${mynick}: setquota [a-z0-9]+ //;
            $groupname =~ s/^${mynick}: setquota ([a-z0-9]+) .*$/$1/;
            set_quota($groupname,$quotavalue);
        }

    }

    elsif ($texte =~ m/^${mynick}: whois [a-z0-9\.\-]+$/)
    {
	my $whois = $texte;
	$whois =~ s/^${mynick}: whois //;
	my $resolv = Net::DNS::Resolver->new;
	
	if (my $query = $resolv->query($whois, "NS")) 
	{
	    irc_msg ("Domain $whois registered");
	    foreach my $rr (grep { $_->type eq 'NS' } $query->answer) 
		{ irc_msg("NS : ".$rr->nsdname); }
 	}
  	else { irc_msg("query failed for $whois : ".$resolv->errorstring); }
    }
    elsif ($texte =~ m/^${mynick}: stats$/)
    {
	my $stats = new Vhffs::Stats( $vhffs );
	$stats->refresh();
        irc_msg ($stats->get_groups_total() . " groups, " . $stats->get_user_total() . " users");
    }
    elsif ($texte =~ m/^${mynick}: coffea$/)
    {
	irc_msg ('                              )         )');
	irc_msg ('                             (     (     )   (');
	irc_msg ('                            (     (     (     )');
	irc_msg ('                             )   (      )     )');
	irc_msg ('                            (     )    (     (');
	irc_msg ('                                    )        )');
	irc_msg ('                        ___________________________');
	irc_msg ('                       |                            ~\ ');
	irc_msg ('                       |                          |\  \ ');
	irc_msg ('                       |                          | )  )');
	irc_msg ('                       |                          |/  / ');
	irc_msg ('                       |                             / ');
	irc_msg ('                        \                         /~~ ');
	irc_msg ('                         \                       / ');
	irc_msg ('                          \____________________ / ');
	irc_msg ('                ________________________________________');
	irc_msg ('                \______________________________________/');
    }
    elsif ($texte =~ m/coin/)
    {
	irc_msg ('PAN !!');
    }

} # on_public

sub on_kick {
    my $self=shift;
    $self->join($chan);
} # on_kick

$conn->add_handler        ('cping',    \&on_ping);
$conn->add_handler        ('crping',   \&on_ping_reply);
$conn->add_global_handler ('376',      \&on_connect);
$conn->add_handler        ('cversion', \&on_cversion);
$conn->add_handler        ('public',   \&on_public);
$conn->add_handler        ('kick',     \&on_kick);

sub CatchAlrm
{
    list_moderation( 1 );
    alarm 60;
}


local $SIG{ALRM} = \&CatchAlrm;
$irc->start;