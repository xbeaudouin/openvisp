#!/usr/bin/perl -w
# TODO: test if speedycgi works ?

# ovs -- mail traffic statistics
# copyright (c) 2007 Xavier Beaudouin <kiwi@oav.net>
# released under the GNU General Public License
#
# Based upon mailgraph, which 
# copyright (c) 2000-2007 ETH Zurich
# copyright (c) 2000-2007 David Schweikert <david@schweikert.ch>
# released under the GNU General Public License
#
# And Based also upon couriergraph, which is
# copyright (c) 2002-2006 Ralf Hildebrandt <ralf.hildebrandt@charite.de>
# relased under the GNU General Public License

use RRDs;
use POSIX qw(uname);

my $VERSION = "1.03";

my $host = (POSIX::uname())[1];
my $scriptname = 'ovs.cgi';
my $xpoints = 540;
my $points_per_sample = 3;
my $ypoints = 160;
my $ypoints_err = 260;
my $rrd = 'ovs.rrd';		 # path to where the RRD database is
my $rrd_virus = 'ovs_virus.rrd'; # path to where the Virus RRD database is
my $rrd_pop = 'ovs_pop.rrd';	 # path to where the Virus RRD database is
my $tmp_dir = '/tmp/ovs';	 # temporary directory where to store the images

my @graphs = (
	{ title => 'Last Day',   seconds => 3600*24,        },
	{ title => 'Last Week',  seconds => 3600*24*7,      },
	{ title => 'Last Month', seconds => 3600*24*31,     },
	{ title => 'Last Year',  seconds => 3600*24*365, },
);

my %color = (
	sent      => '000099', # rrggbb in hex
	received  => '009900',
	rejected  => 'AA0000', 
	bounced   => '000000',
	virus	  => 'CCFF99',
	spam      => 'DDBB00',
	greylist  => 'CCCCCC',
	helo      => '009999',
	spf       => '990099',
	dnf       => '00FFFF',
	policydbl => 'FF0000',
	vrfytmp   => 'FFF000',
	vrfyrjt   => 'FF00F0',
);

sub rrd_graph(@)
{
	my ($range, $file, $ypoints, @rrdargs) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	# choose carefully the end otherwise rrd will maybe pick the wrong RRA:
	my $end  = time; $end -= $end % $step;
	my $date = localtime(time);
	$date =~ s|:|\\:|g unless $RRDs::VERSION < 1.199908;

	my ($graphret,$xs,$ys) = RRDs::graph($file,
		'--imgformat', 'PNG',
		'--width', $xpoints,
		'--height', $ypoints,
		'--start', "-$range",
		'--end', $end,
		'--vertical-label', 'msgs/min',
		'--lower-limit', 0,
		'--units-exponent', 0, # don't show milli-messages/s
		'--lazy',
		'--color', 'SHADEA#ffffff',
		'--color', 'SHADEB#ffffff',
		'--color', 'BACK#ffffff',

		$RRDs::VERSION < 1.2002 ? () : ( '--slope-mode'),

		@rrdargs,

		'COMMENT:['.$date.']\r',
	);

	my $ERR=RRDs::error;
	die "ERROR: $ERR\n" if $ERR;
}

sub graph($$)
{
	my ($range, $file) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	rrd_graph($range, $file, $ypoints,
		"DEF:sent=$rrd:sent:AVERAGE",
		"DEF:msent=$rrd:sent:MAX",
		"CDEF:rsent=sent,60,*",
		"CDEF:rmsent=msent,60,*",
		"CDEF:dsent=sent,UN,0,sent,IF,$step,*",
		"CDEF:ssent=PREV,UN,dsent,PREV,IF,dsent,+",
		"AREA:rsent#$color{sent}:Sent    ",
		'GPRINT:ssent:MAX:total\: %8.0lf msgs',
		'GPRINT:rsent:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmsent:MAX:max\: %4.0lf msgs/min\l',

		"DEF:recv=$rrd:recv:AVERAGE",
		"DEF:mrecv=$rrd:recv:MAX",
		"CDEF:rrecv=recv,60,*",
		"CDEF:rmrecv=mrecv,60,*",
		"CDEF:drecv=recv,UN,0,recv,IF,$step,*",
		"CDEF:srecv=PREV,UN,drecv,PREV,IF,drecv,+",
		"LINE2:rrecv#$color{received}:Received",
		'GPRINT:srecv:MAX:total\: %8.0lf msgs',
		'GPRINT:rrecv:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmrecv:MAX:max\: %4.0lf msgs/min\l',

                "DEF:rejected=$rrd:rejected:AVERAGE",
                "DEF:mrejected=$rrd:rejected:MAX",
                "CDEF:rrejected=rejected,60,*",
                "CDEF:drejected=rejected,UN,0,rejected,IF,$step,*",
                "CDEF:srejected=PREV,UN,drejected,PREV,IF,drejected,+",
                "CDEF:rmrejected=mrejected,60,*",
                "LINE2:rrejected#$color{rejected}:Rejected",
                'GPRINT:srejected:MAX:total\: %8.0lf msgs',
                'GPRINT:rrejected:AVERAGE:avg\: %5.2lf msgs/min',
                'GPRINT:rmrejected:MAX:max\: %4.0lf msgs/min\l',

	);
}

sub graph_err($$)
{
	my ($range, $file) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	rrd_graph($range, $file, $ypoints_err,
		"DEF:bounced=$rrd:bounced:AVERAGE",
		"DEF:mbounced=$rrd:bounced:MAX",
		"CDEF:rbounced=bounced,60,*",
		"CDEF:dbounced=bounced,UN,0,bounced,IF,$step,*",
		"CDEF:sbounced=PREV,UN,dbounced,PREV,IF,dbounced,+",
		"CDEF:rmbounced=mbounced,60,*",
		"AREA:rbounced#$color{bounced}:Bounced ",
		'GPRINT:sbounced:MAX:total\: %8.0lf msgs',
		'GPRINT:rbounced:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmbounced:MAX:max\: %4.0lf msgs/min\l',

		"DEF:virus=$rrd_virus:virus:AVERAGE",
		"DEF:mvirus=$rrd_virus:virus:MAX",
		"CDEF:rvirus=virus,60,*",
		"CDEF:dvirus=virus,UN,0,virus,IF,$step,*",
		"CDEF:svirus=PREV,UN,dvirus,PREV,IF,dvirus,+",
		"CDEF:rmvirus=mvirus,60,*",
		"STACK:rvirus#$color{virus}:Viruses ",
		'GPRINT:svirus:MAX:total\: %8.0lf msgs',
		'GPRINT:rvirus:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmvirus:MAX:max\: %4.0lf msgs/min\l',

		"DEF:spam=$rrd_virus:spam:AVERAGE",
		"DEF:mspam=$rrd_virus:spam:MAX",
		"CDEF:rspam=spam,60,*",
		"CDEF:dspam=spam,UN,0,spam,IF,$step,*",
		"CDEF:sspam=PREV,UN,dspam,PREV,IF,dspam,+",
		"CDEF:rmspam=mspam,60,*",
		"LINE2:rspam#$color{spam}:Spam    ",
		'GPRINT:sspam:MAX:total\: %8.0lf msgs',
		'GPRINT:rspam:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmspam:MAX:max\: %4.0lf msgs/min\l',

		"DEF:greylist=$rrd_virus:greylist:AVERAGE",
		"DEF:mgreylist=$rrd_virus:greylist:MAX",
		"CDEF:rgreylist=greylist,60,*",
		"CDEF:dgreylist=greylist,UN,0,greylist,IF,$step,*",
		"CDEF:sgreylist=PREV,UN,dgreylist,PREV,IF,dgreylist,+",
		"CDEF:rmgreylist=mgreylist,60,*",
		"STACK:rgreylist#$color{greylist}:Greylist",
		'GPRINT:sgreylist:MAX:total\: %8.0lf msgs',
		'GPRINT:rgreylist:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmgreylist:MAX:max\: %4.0lf msgs/min\l',

		"DEF:helo=$rrd_virus:helo:AVERAGE",
		"DEF:mhelo=$rrd_virus:helo:MAX",
		"CDEF:rhelo=helo,60,*",
		"CDEF:dhelo=helo,UN,0,helo,IF,$step,*",
		"CDEF:shelo=PREV,UN,dhelo,PREV,IF,dhelo,+",
		"CDEF:rmhelo=mhelo,60,*",
		"STACK:rhelo#$color{helo}:Helo rej",
		'GPRINT:shelo:MAX:total\: %8.0lf msgs',
		'GPRINT:rhelo:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmhelo:MAX:max\: %4.0lf msgs/min\l',

		"DEF:spf=$rrd_virus:spf:AVERAGE",
		"DEF:mspf=$rrd_virus:spf:MAX",
		"CDEF:rspf=spf,60,*",
		"CDEF:dspf=spf,UN,0,spf,IF,$step,*",
		"CDEF:sspf=PREV,UN,dspf,PREV,IF,dspf,+",
		"CDEF:rmspf=mspf,60,*",
		"STACK:rspf#$color{spf}:Spf     ",
		'GPRINT:sspf:MAX:total\: %8.0lf msgs',
		'GPRINT:rspf:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmspf:MAX:max\: %4.0lf msgs/min\l',

		"DEF:dnf=$rrd_virus:dnf:AVERAGE",
		"DEF:mdnf=$rrd_virus:dnf:MAX",
		"CDEF:rdnf=dnf,60,*",
		"CDEF:ddnf=dnf,UN,0,dnf,IF,$step,*",
		"CDEF:sdnf=PREV,UN,ddnf,PREV,IF,ddnf,+",
		"CDEF:rmdnf=mdnf,60,*",
		"STACK:rdnf#$color{dnf}:DomainNF",
		'GPRINT:sdnf:MAX:total\: %8.0lf msgs',
		'GPRINT:rdnf:AVERAGE:avg\: %5.2lf msgs/min',
		'GPRINT:rmdnf:MAX:max\: %4.0lf msgs/min\l',

                "DEF:policydbl=$rrd_virus:policydbl:AVERAGE",
                "DEF:mpolicydbl=$rrd_virus:policydbl:MAX",
                "CDEF:rpolicydbl=policydbl,60,*",
                "CDEF:dpolicydbl=policydbl,UN,0,policydbl,IF,$step,*",
                "CDEF:spolicydbl=PREV,UN,dpolicydbl,PREV,IF,dpolicydbl,+",
                "CDEF:rmpolicydbl=mpolicydbl,60,*",
                "STACK:rpolicydbl#$color{policydbl}:PolicyBL",
                'GPRINT:spolicydbl:MAX:total\: %8.0lf msgs',
                'GPRINT:rpolicydbl:AVERAGE:avg\: %5.2lf msgs/min',
                'GPRINT:rmpolicydbl:MAX:max\: %4.0lf msgs/min\l',

                "DEF:vrfytmp=$rrd_virus:vrfytmp:AVERAGE",
                "DEF:mvrfytmp=$rrd_virus:vrfytmp:MAX",
                "CDEF:rvrfytmp=vrfytmp,60,*",
                "CDEF:dvrfytmp=vrfytmp,UN,0,vrfytmp,IF,$step,*",
                "CDEF:svrfytmp=PREV,UN,dvrfytmp,PREV,IF,dvrfytmp,+",
                "CDEF:rmvrfytmp=mvrfytmp,60,*",
                "STACK:rvrfytmp#$color{vrfytmp}:Vrfy Tmp",
                'GPRINT:svrfytmp:MAX:total\: %8.0lf msgs',
                'GPRINT:rvrfytmp:AVERAGE:avg\: %5.2lf msgs/min',
                'GPRINT:rmvrfytmp:MAX:max\: %4.0lf msgs/min\l',

                "DEF:vrfyrjt=$rrd_virus:vrfyrjt:AVERAGE",
                "DEF:mvrfyrjt=$rrd_virus:vrfyrjt:MAX",
                "CDEF:rvrfyrjt=vrfyrjt,60,*",
                "CDEF:dvrfyrjt=vrfyrjt,UN,0,vrfyrjt,IF,$step,*",
                "CDEF:svrfyrjt=PREV,UN,dvrfyrjt,PREV,IF,dvrfyrjt,+",
                "CDEF:rmvrfyrjt=mvrfyrjt,60,*",
                "STACK:rvrfyrjt#$color{vrfyrjt}:Vrfy rej",
                'GPRINT:svrfyrjt:MAX:total\: %8.0lf msgs',
                'GPRINT:rvrfyrjt:AVERAGE:avg\: %5.2lf msgs/min',
                'GPRINT:rmvrfyrjt:MAX:max\: %4.0lf msgs/min\l',

	);
}

sub graph_pop($$)
{
	my ($range, $file) = @_;
	my $step = $range*$points_per_sample/$xpoints;
	rrd_graph($range, $file, $ypoints_err,
                "DEF:pop3d_login=$rrd_pop:pop3d_login:AVERAGE",
                "DEF:mpop3d_login=$rrd_pop:pop3d_login:MAX",
                "DEF:pop3d_ssl_login=$rrd_pop:pop3d_ssl_login:AVERAGE",
                "DEF:mpop3d_ssl_login=$rrd_pop:pop3d_ssl_login:MAX",

                "CDEF:rpop3d_login=pop3d_login,60,*",
                "CDEF:vpop3d_login=pop3d_login,UN,0,pop3d_login,IF,$step,*",
                "CDEF:rmpop3d_login=mpop3d_login,60,*",

                "CDEF:rpop3d_ssl_login=pop3d_ssl_login,60,*",
                "CDEF:vpop3d_ssl_login=pop3d_ssl_login,UN,0,pop3d_ssl_login,IF,$step,*",
                "CDEF:rmpop3d_ssl_login=mpop3d_ssl_login,60,*",

                "DEF:imapd_login=$rrd_pop:imapd_login:AVERAGE",
                "DEF:mimapd_login=$rrd_pop:imapd_login:MAX",
                "DEF:imapd_ssl_login=$rrd_pop:imapd_ssl_login:AVERAGE",
                "DEF:mimapd_ssl_login=$rrd_pop:imapd_ssl_login:MAX",

                "CDEF:rimapd_login=imapd_login,60,*",
                "CDEF:vimapd_login=imapd_login,UN,0,imapd_login,IF,$step,*",
                "CDEF:rmimapd_login=mimapd_login,60,*",

                "CDEF:rimapd_ssl_login=imapd_ssl_login,60,*",
                "CDEF:rmimapd_ssl_login=mimapd_ssl_login,60,*",
                "CDEF:vimapd_ssl_login=imapd_ssl_login,UN,0,imapd_ssl_login,IF,$step,*",

                'LINE:rpop3d_login#DD0000:pop3',
                'GPRINT:vpop3d_login:AVERAGE:total\: %.0lf logins',
                'GPRINT:rmpop3d_login:MAX:max\: %.0lf logins/min\l',
                'HRULE:0#000000',

                'AREA:rpop3d_ssl_login#770000:pop3/ssl:STACK',
                'GPRINT:vpop3d_ssl_login:AVERAGE:total\: %.0lf logins',
                'GPRINT:rmpop3d_ssl_login:MAX:max\: %.0lf logins/min\l',
                'HRULE:0#000000',

                'LINE:rimapd_login#00DD00:imap',
                'GPRINT:vimapd_login:AVERAGE:total\: %.0lf logins',
                'GPRINT:rmimapd_login:MAX:max\: %.0lf logins/min\l',
                'HRULE:0#000000',

                'AREA:rimapd_ssl_login#007700:imap/ssl:STACK',
                'GPRINT:vimapd_ssl_login:AVERAGE:total\: %.0lf logins',
                'GPRINT:rmimapd_ssl_login:MAX:max\: %.0lf logins/min\l',
	);
}

sub print_html()
{
	print "Content-Type: text/html\n\n";

	print <<HEADER;
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mail statistics for $host</title>
<meta http-equiv="Refresh" content="300" />
<meta http-equiv="Pragma" content="no-cache" />
<link rel="stylesheet" href="ovs.css" type="text/css" />
</head>
<body>
HEADER

	print "<h1>Mail statistics for $host</h1>\n";

	print "<ul id=\"jump\">\n";
	for my $n (0..$#graphs) {
		print "  <li><a href=\"#G$n\">$graphs[$n]{title}</a>&nbsp;</li>\n";
	}
	print "</ul>\n";

	for my $n (0..$#graphs) {
		print "<h2 id=\"G$n\">$graphs[$n]{title}</h2>\n";
		print "<p><img src=\"$scriptname?${n}-n\" alt=\"ovs\"/><br/>\n";
		print "<img src=\"$scriptname?${n}-e\" alt=\"ovs\"/></p><br/>\n";
		print "<img src=\"$scriptname?${n}-p\" alt=\"ovs\"/></p>\n";
	}

	print <<FOOTER;
<hr/>
<table><tr><td>
<a href="http://openvisp.fr/">OpenVISP Stats</a> $VERSION
by <a href="http://oav.net/">Xavier Beaudouin</a>
<br/>
Based on <a href="http://mailgraph.schweikert.ch/">Mailgraph</a> &amp; <a href="http://www.arschkrebs.de/postfix/couriergraph/">CourierGraph</a>
</td>
<td align="right">
<a href="http://oss.oetiker.ch/rrdtool/"><img src="http://oss.oetiker.ch/rrdtool/.pics/rrdtool.gif" alt="" width="120" height="34"/></a>
</td></tr></table>
</body></html>
FOOTER
}

sub send_image($)
{
	my ($file)= @_;

	-r $file or do {
		print "Content-type: text/plain\n\nERROR: can't find $file\n";
		exit 1;
	};

	print "Content-type: image/png\n";
	print "Content-length: ".((stat($file))[7])."\n";
	print "\n";
	open(IMG, $file) or die;
	my $data;
	print $data while read(IMG, $data, 16384)>0;
}

sub main()
{
	my $uri = $ENV{REQUEST_URI} || '';
	$uri =~ s/\/[^\/]+$//;
	$uri =~ s/\//,/g;
	$uri =~ s/(\~|\%7E)/tilde,/g;
	mkdir $tmp_dir, 0777 unless -d $tmp_dir;
	mkdir "$tmp_dir/$uri", 0777 unless -d "$tmp_dir/$uri";

	my $img = $ENV{QUERY_STRING};
	if(defined $img and $img =~ /\S/) {
		if($img =~ /^(\d+)-n$/) {
			my $file = "$tmp_dir/$uri/ovs_$1.png";
			graph($graphs[$1]{seconds}, $file);
			send_image($file);
		}
		elsif($img =~ /^(\d+)-e$/) {
			my $file = "$tmp_dir/$uri/ovs_$1_err.png";
			graph_err($graphs[$1]{seconds}, $file);
			send_image($file);
		}
		elsif($img =~ /^(\d+)-p$/) {
			my $file = "$tmp_dir/$uri/ovs_$1_pop.png";
			graph_pop($graphs[$1]{seconds}, $file);
			send_image($file);
		}
		else {
			die "ERROR: invalid argument\n";
		}
	}
	else {
		print_html;
	}
}

main;
