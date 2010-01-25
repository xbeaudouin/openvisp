#!/usr/bin/php
#
# this code is made gpl since i like to share it with others that use postfix and policyd
# where postfix admin is the webadmin part, spam@junc.org is my spamtrap email adresse that being used to fetch from
# postfix alias to make policyd happy knowing that the alias from is spamtrap in policyd
#
# if you find any bugs in the php send it to my spam@junc.org :-)
#
# remember to set chmod so only root can read the file, and add the script to your cron
#
<?php
    $HostName="localhost";
    $UserNamePF="postfix-username";
    $PassWordPF="postfix-password";
    $DataBasePF="postfix-database";
    $UserNamePD="policyd-username";
    $PassWordPD="policyd-password";
    $DataBasePD="policyd-database";
    $postfix_db = mysql_connect ($HostName, $UserNamePF, $PassWordPF) or die(mysql_error());
    $policyd_db = mysql_connect ($HostName, $UserNamePD, $PassWordPD) or die(mysql_error());
    $policyd_link = mysql_select_db ($DataBasePD,$policyd_db) or die(mysql_error());
    $postfix_link = mysql_select_db ($DataBasePF,$postfix_db) or die(mysql_error());
    $sql="TRUNCATE spamtrap";
    mysql_query ($sql,$policyd_db) or die(mysql_error());
    $sql = "SELECT * FROM alias WHERE goto LIKE 'spam@junc.org' AND address NOT LIKE 'spam@junc.org' AND active LIKE '1'";
    $postfix_handle = mysql_query ($sql,$postfix_db) or die(mysql_error());
    $num_of_spamtrap = mysql_num_rows ($postfix_handle) or die(mysql_error());
    $count = 0;
    do
    {
	++$count;
	$row = mysql_fetch_row ($postfix_handle) or die(mysql_error());
	$sql="INSERT INTO spamtrap VALUES ('$row[0]','1');";
	mysql_query ($sql,$policyd_db) or die(mysql_error());
    }
    while ($count < $num_of_spamtrap);
    mysql_close($policyd_db);
    mysql_close($postfix_db);
?>
#
# nice eh ?
#
