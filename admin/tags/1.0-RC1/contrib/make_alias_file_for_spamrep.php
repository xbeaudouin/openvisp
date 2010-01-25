<?php
    $HostName="localhost";
    $UserNamePF="postfix-username";
    $PassWordPF="postfix-password";
    $DataBasePF="postfix-database";

    $postfix_db = mysql_connect ($HostName, $UserNamePF, $PassWordPF) or die (mysql_error());
    $postfix_link = mysql_select_db ($DataBasePF,$postfix_db) or die (mysql_error());
    $sql = "SELECT * FROM alias WHERE active LIKE '1' AND mailreport LIKE '1' ORDER BY 'alias' ASC";
    $postfix_handle = mysql_query ($sql,$postfix_db) or die (mysql_error());
    $num_of_alias = mysql_num_rows ($postfix_handle) or die (mysql_error());
    $count = 0;
    do
    {
	++$count;
	$row = mysql_fetch_row ($postfix_handle) or die (mysql_error());
	print "$row[0]\n";
    }
    while ($count < $num_of_alias);
    mysql_close ($postfix_db);
?>
