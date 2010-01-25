CREATE TABLE `blacklist` (
  `_blacklist` char(15) NOT NULL default '',
  `_description` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_blacklist` (`_blacklist`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE blacklist_dnsname (
  _blacklist   char(60) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY _blacklist (_blacklist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE `blacklist_helo` (
  `_helo` char(60) NOT NULL default '',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_helo` (`_helo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `blacklist_sender` (
  `_blacklist` char(60) NOT NULL default '',
  `_description` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_blacklist` (`_blacklist`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `helo` (
  `_host` char(15) NOT NULL default '',
  `_helo` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_host` (`_host`,`_helo`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `policy` (
  `_rcpt` char(60) NOT NULL default '',
  `_optin` tinyint(1) unsigned NOT NULL default '1',
  `_priority` tinyint(1) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_rcpt` (`_rcpt`,`_optin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `policy_training` (
  `_rcpt` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  UNIQUE KEY `_rcpt` (`_rcpt`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `spamtrap` (
  `_rcpt` char(60) NOT NULL default '',
  `_active` tinyint(1) unsigned NOT NULL default '1',
  UNIQUE KEY `_rcpt` (`_rcpt`,`_active`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE throttle (
  _from        char(60) NOT NULL default '',
  _count_max   mediumint(8) unsigned NOT NULL default '0',
  _count_cur   mediumint(8) unsigned NOT NULL default '1',
  _date        int(10) unsigned NOT NULL default '0',
  _quota_cur   int(10) unsigned NOT NULL default '0',
  _quota_max   int(10) unsigned NOT NULL default '0',
  _time_limit  int(10) unsigned NOT NULL default '0',
  _mail_size   int(10) unsigned NOT NULL default '0',
  _count_tot   mediumint(8) unsigned NOT NULL default '1',
  _rcpt_max    mediumint(8) unsigned NOT NULL default '0',
  _rcpt_cur    mediumint(8) unsigned NOT NULL default '1',
  _rcpt_tot    mediumint(8) unsigned NOT NULL default '1',
  _abuse_cur   int(10) unsigned NOT NULL default '0',
  _abuse_tot   int(10) unsigned NOT NULL default '0',
  _log_warn    int(10) unsigned NOT NULL default '0',
  _log_panic   int(10) unsigned NOT NULL default '0',
  _priority    tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY _from (_from)
) TYPE=MyISAM;


CREATE TABLE throttle_from_instance (
  _instance    char(60) NOT NULL default '',
  _from        char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _instance (_instance),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE throttle_rcpt (
  _rcpt        char(60) NOT NULL default '',
  _count_max   mediumint(8) unsigned NOT NULL default '0',
  _count_cur   mediumint(8) unsigned NOT NULL default '1',
  _date        int(10) unsigned NOT NULL default '0',
  _time_limit  int(10) unsigned NOT NULL default '0',
  _count_tot   mediumint(8) unsigned NOT NULL default '1',
  _abuse_cur   int(10) unsigned NOT NULL default '0',
  _abuse_tot   int(10) unsigned NOT NULL default '0',
  _log_warn    int(10) unsigned NOT NULL default '0',
  _log_panic   int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _rcpt (_rcpt)
) TYPE=MyISAM;


CREATE TABLE `triplet` (
  `_from` char(60) NOT NULL default '',
  `_rcpt` char(60) NOT NULL default '',
  `_host` char(15) NOT NULL default '',
  `_datenew` int(10) unsigned NOT NULL default '0',
  `_datelast` int(10) unsigned NOT NULL default '0',
  `_count` smallint(5) unsigned NOT NULL default '0',
  UNIQUE KEY `_host` (`_host`,`_from`,`_rcpt`),
  KEY `_datelast` (`_datelast`),
  KEY `_datenew` (`_datenew`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `whitelist` (
  `_whitelist` char(15) NOT NULL default '',
  `_description` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_whitelist` (`_whitelist`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `whitelist_dnsname` (
  `_whitelist` char(60) NOT NULL default '',
  `_description` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_whitelist` (`_whitelist`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `whitelist_sender` (
  `_whitelist` char(60) NOT NULL default '',
  `_description` char(60) NOT NULL default '',
  `_expire` int(10) unsigned NOT NULL default '0',
  `createdate` timestamp NOT NULL default CURRENT_TIMESTAMP,
  UNIQUE KEY `_whitelist` (`_whitelist`),
  KEY `_expire` (`_expire`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
