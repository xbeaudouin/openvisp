<?php

    $HostName="openvisp";
    $UserName="openvisp";
    $PassWord="PasSwOrd";
    $DataBase="openvisp";
    
    mysql_connect ($HostName, $UserName, $PassWord);
    mysql_select_db ($DataBase);

    $query = "SELECT * FROM domain WHERE active LIKE '1' AND backupmx LIKE '0' ORDER BY 'domain' ASC";
    $domain_handle = mysql_query ($query);

    print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n";
    print "<html>\n";
    print "<head>\n";
    print "<title>Local Domains in amavisd</title>\n";
    print "</head>\n";
    print "<body>\n";
    print "<pre>\n";

    for ($count = 1; $row = mysql_fetch_row ($domain_handle); ++$count)
    {
	print "$row[0]\n";
    }
    print "\n";

    print "</pre>\n";
    print "</body>\n";
    print "</html>\n";
?>
