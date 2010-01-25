#!/usr/bin/php
<?php
    $UserNamePD="policyd-username";
    $PassWordPD="policyd-password";
    $DataBasePD="policyd-database";
    $HostName="localhost";
    mysql_connect ($HostName, $UserNamePD, $PassWordPD);
    mysql_select_db ($DataBasePD);
    
    //# CREATE TABLE `whitelist` (
    //#  `_whitelist` binary(15) NOT NULL default '',
    //#  `_description` binary(60) NOT NULL default '',
    //#  `_expire` int(10) unsigned NOT NULL default '0',
    //#  UNIQUE KEY `_whitelist` (`_whitelist`),
    //#  KEY `_expire` (`_expire`)
    //# ) ENGINE=MyISAM DEFAULT CHARSET=binary;
    
    // if # is not in description its local to policyd
    // and if its static with 0 as expire we add it to spamassassin
    
    // spamassassin have msa_networks olso so we add whitelisted ip to it to
    // stop spamassassin doing future dns lookups
    
    // mail me back on me at junc dot org if there is bugs in this 

    $query = "SELECT * FROM whitelist WHERE _expire ='0' AND _description LIKE '%#%'";
    $handle = mysql_query ($query);
    
    print "#\n";
    print "# trusted networks from policyd whitelist ip\n";
    print "#\n";
    
    for ($loop = 1; $row = mysql_fetch_row ($handle); ++$loop)
    {    
    	print "trusted_networks $row[0]\t\t#$row[1]\n";
    	print "msa_networks $row[0]\t\t#$row[1]\n";
    }
    print "#\n";
    print "# eof\n";
    print "#\n";
?>
