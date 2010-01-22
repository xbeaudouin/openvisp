#!/usr/bin/perl 
# vim:ts=4

use POSIX;
use DBI;
use File::Basename;
use strict;
################# CONFIGURABLE LINES START ###############
# default location of routers.conf file
my( $conffile ) = "/etc/routers2.conf";
my( $db_name ) = "openvispadmin";
my( $db_user ) = "openvispadmin";
my( $db_pass ) = "openvispadmin";
my( $db_host ) = "localhost";
################# CONFIGURABLE LINES END #################

my(@cfgfiles) = ();
my($pattern, $thisfile);
my($workdir, $rrd, $opt );
my(%targets,$t);
my( %config );
my( $pathsep ) = "/";
my( $confpath, $cfgfiles );
my( $debug ) = 0;
#my( $debug ) = 1;
# Db stuff;
my ($dbh, $sth, $row, $db_query);

###########################################################################
# readconf: pass it a list of section names
sub readconf(@)
{
	my ($inlist, $i, @secs, $sec);
	
	@secs = @_;
	%config = ();

	# set defaults
	%config = ( 'routers.cgi-confpath' => ".",);

	( open CFH, "<".$conffile ) || do {
		print "Error: unable to open file $conffile\n";
		exit(0);
	};

	$inlist=0;
	$sec = "";
	while( <CFH> ) {
		/^\s*#/ && next;
		/\[(\S*)\]/ && do { 
			$sec = $1;
			$inlist=0;	
			foreach $i ( @secs ) {
				if ( $i eq $1 ) { $inlist=1; last; }
			}
			next;
		};
		# note final \s* to strip all trailing spaces (which doesnt work 
		# because the * operator is greedy!)
		if ( $inlist ) { /(\S+)\s*=\s*(\S.*?)\s*$/ and $config{"$sec-$1"}=$2; }
	}
	close CFH;
	
	# some path corrections: remove trailing path separators on f/s paths
	foreach ( qw/dbpath confpath graphpath graphurl/ ) {
		$config{"routers.cgi-$_"} =~ s/[\/\\]$//;
	}

}

###########################################################################


############### MAIN CODE STARTS HERE #######

# get parameters
print "Reading configuration\n" if($debug);
# Check if conffile is readable or test several standard placas
if(! -r $conffile ) {
  $conffile = "/usr/local/etc/routers2.conf";
  if(! -r $conffile ) {
    $conffile = "/opt/etc/routers2.conf";
    if(! -r $conffile ) {
      $conffile = "/etc/routers2.conf";
    }
  }
}
readconf('routers.cgi','web');

$confpath = $config{'routers.cgi-confpath'};
$cfgfiles = $config{'routers.cgi-cfgfiles'};
if(! -d $confpath ) {
	print "Error: MRTG directory $confpath does not exist.\n";
	exit 1;
}

# Now we have the defaults, and we know which files to process.
# We can optimise our processing of the .cfg files.
foreach $pattern ( split " ",$cfgfiles ) {
	print "$confpath$pathsep$pattern\n" if($debug);
	push @cfgfiles, glob( $confpath.$pathsep.$pattern );
}

if( @ARGV ) { @cfgfiles = @ARGV; }

foreach $thisfile ( @cfgfiles ) {
	next if(!-f $thisfile);
	open CFG,"<$thisfile" or next;
	print "Processing $thisfile\n" if($debug);
	$workdir = $config{'routers.cgi-dbpath'}; # default
	while ( <CFG> ) {
		if( /^\s*Include\s*:\s*(\S+)/i ) { push @cfgfiles,$1; next; }
		if( /^\s*WorkDir\s*:\s*(\S+)/i ) {
			$workdir = $1; next;
		}
		if( /^\s*Directory\[(\S+)\]\s*:\s*(\S+)/i ) {
			$t = lc $1;
			$targets{$t} = {} if(!defined $targets{$t});
			$targets{$t}->{directory} = $2;
			next;
		}
		if( /^\s*Target\[(\S+)\]/i ) {
			$t = lc $1;
			$targets{$t} = {} if(!defined $targets{$t});
			$targets{$t}->{file} = "$t.rrd";
			$targets{$t}->{intf} = "$t";
			$targets{$t}->{cfg} = $thisfile;;
			next;
		}
		if( /^\s*Title\[(\S+)\]\s*:\s*(\S.+)/i ) {
			$t = lc $1;
			$opt = $2;
			$targets{$t} = {} if(!defined $targets{$t});
			$targets{$t}->{title} = $opt;
			next;
		}
	}
	close CFG;
	# Try to conenct to database or throw out.
	$dbh = DBI->connect(
		"DBI:mysql:database=".$db_name.";host=".$db_host.";port=3306",
		$db_user,
		$db_pass
	);
	# now process the archiving
	foreach $t ( keys %targets ) {
		next if(!defined $targets{$t}->{file}); # skip dummy ones
		foreach ( keys %{$targets{'_'}} ) {
			$targets{$t}->{$_} = $targets{'_'}->{$_}
				if(!defined $targets{$t}->{$_});
		}
		$rrd = $workdir;
		$rrd .= $pathsep.$targets{$t}->{directory} if(defined $targets{$t}->{directory});
		$rrd .= $pathsep.$targets{$t}->{file};
		$targets{$t}->{rrd} = $rrd;
		$db_query = "SELECT id,port,cfgfile,lm_cfgfile,rrdfile FROM datacenter_sw_stats WHERE port='".$targets{$t}->{intf}."' AND cfgfile='".substr($targets{$t}->{cfg}, length($confpath)+1)."' AND rrdfile='".$rrd."'";
		#print $db_query."\n" if ($debug);
		$sth = $dbh->prepare ($db_query);
		if ($sth->execute) {
			if ($row = $sth->fetchrow_hashref) {
			    # Check if stuff is not more recent than the DB, if so update it.
				if ((stat($targets{$t}->{cfg}))[9] != $row->{lm_cfgfile}) {
					# Needs to update DB
					print "Port needs to be updated in DB\n" if ($debug);
					$db_query = "UPDATE datacenter_sw_stats SET title=".$dbh->quote($targets{$t}->{title}).",lm_cfgfile='".(stat($targets{$t}->{cfg}))[9]."',modified=NOW() WHERE id=".$row->{id};
					#print $db_query."\n" if ($debug);
					$sth = $dbh->prepare($db_query);
					if ($sth->execute) {
						print "Ok updated\n" if ($debug);
					} else {
						print "Not updated ?\n" if ($debug);
					}
				}
			} else {
				$db_query = "INSERT INTO datacenter_sw_stats (router,port,title,cfgfile,lm_cfgfile,rrdfile,created,modified) VALUES ('".basename($targets{$t}->{cfg})."','".$targets{$t}->{intf}."',".$dbh->quote($targets{$t}->{title}).",'".substr($targets{$t}->{cfg}, length($confpath)+1)."','".(stat($targets{$t}->{cfg}))[9]."','".$rrd."',NOW(),NOW())";
				#print $db_query."\n" if ($debug);
				$sth = $dbh->prepare($db_query);
				if ($sth->execute) {
					print "Ok added\n" if ($debug);
				} else {
					print "Not added\n" if ($debug);
				}
			}
		} else {
			print "Problem with DB\n" if ($debug);
			die;
		}
		# rrd => fichier rdd
		# title => Titre du graph
		# intf => nom de l'interface vu de mrtg
		# cfg => chemin du fichier cfg (complet)
		#print "$rrd $targets{$t}->{title} $targets{$t}->{intf} $targets{$t}->{cfg} ".substr($targets{$t}->{cfg}, length($confpath)+1)." ".basename($targets{$t}->{cfg})."\n" if($debug);

	}
}

print "All finished.\n" if($debug) ;
exit(0);
