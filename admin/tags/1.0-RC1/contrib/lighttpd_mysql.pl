#!/usr/bin/perl


use DBI;
use Sys::Hostname;

$| = 1;
# edited the variables below.
$has_edited_source = 1;

  
$serverDb="";
$serverUser="";
$serverPass="";
$serverName="";
$serverPort="3306";
$hostname = hostname;

$dbh = DBI->connect("DBI:mysql:database=$serverDb;host=$serverName;port=$serverPort",$serverUser,$serverPass);
if (not $dbh) {
        die "Unable to connect to the database.  Please check your connection variables. (Bad password? Incorrect perms?) $serverPass";
}



$records = $dbh->prepare("
SELECT CONCAT(whost.vhost,'.',domain.domain) as webhost, whost.id, whost.DocumentRoot 
FROM whost, whost_server, server, domain
WHERE whost.paid = '1' 
AND whost.active = '1' 
AND whost.id = whost_server.whost_id
AND whost.domain_id = domain.id
AND whost_server.server_id = server.id
AND server.private_name = '$hostname'
");
$records->execute;
if (not $records) {
        exit (0);
}

while ($data = $records->fetchrow_hashref) {

		$aliases = $dbh->prepare("
		SELECT DISTINCT whost_alias.vhost_alias
FROM whost_alias
WHERE whost_id = '".$data->{'id'}."'
");


		$aliases->execute;
		$equal_sign = "== \"";
		my $end_sign = "";
		while ($data_alias = $aliases->fetchrow_hashref) {
				$data->{'webhost'} .= "|".$data_alias->{'vhost_alias'};
				$equal_sign = "=~ \"^(";
				$end_sign = ")\$";
		}

		print '$HTTP["host"] '.$equal_sign.$data->{'webhost'}.$end_sign.'" {
server.document-root = "'.$data->{'DocumentRoot'}."/htdocs\"\n";

		$options = $dbh->prepare("
SELECT whost_options.name, whost_config.value
FROM whost_config, whost_options
WHERE whost_config.whost_id='".$data->{'id'}."' 
AND whost_config.option_id = whost_options.id
AND whost_config.active = '1'
");
		$options->execute;
		if ($options) {
				while ( $data_options = $options->fetchrow_hashref ){
						print $data_options->{'name'}." = ".$data_options->{'value'}."\n";
				}
		}

		print "}\n";

}
