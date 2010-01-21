CREATE DATABASE policyd;
USE policyd;
  
CREATE TABLE policy (
  _rcpt        char(60) NOT NULL default '',
  _optin       tinyint(1) unsigned NOT NULL default '1',
  _priority    tinyint(1) unsigned NOT NULL default '0',
  UNIQUE KEY _rcpt (_rcpt,_optin) 
) TYPE=MyISAM;

CREATE TABLE policy_training (
  _rcpt        char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _rcpt (_rcpt),
  KEY _expire (_expire)
) TYPE=MyISAM;

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

CREATE TABLE rcpt_acl (
  _sender      char(60) NOT NULL default '',
  _rcpt        char(60) NOT NULL default '',
  _wblist      char(60) NOT NULL default '',
  _priority    int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _rcpt (_rcpt,_sender)
) TYPE=MyISAM;


CREATE TABLE triplet (
  _from        char(60) NOT NULL default '',
  _rcpt        char(60) NOT NULL default '',
  _host        char(46) NOT NULL default '',
  _datenew     int(10) unsigned NOT NULL default '0',
  _datelast    int(10) unsigned NOT NULL default '0',
  _count       smallint(5) unsigned NOT NULL default '0',
  UNIQUE KEY _host (_host,_from,_rcpt),
  KEY _datelast (_datelast),
  KEY _datenew (_datenew)
) TYPE=MyISAM;
  
CREATE TABLE whitelist (
  _whitelist   char(46) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _whitelist (_whitelist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE whitelist_sender (
  _whitelist   char(60) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _whitelist (_whitelist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE whitelist_dnsname (
  _whitelist   char(60) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _whitelist (_whitelist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE blacklist (
  _blacklist   char(46) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _blacklist (_blacklist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE blacklist_helo (
  _helo        char(60) NOT NULL default '',
  UNIQUE KEY _helo (_helo)
) TYPE=MyISAM;

CREATE TABLE blacklist_sender (
  _blacklist   char(60) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _blacklist (_blacklist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE blacklist_dnsname (
  _blacklist   char(60) NOT NULL default '',
  _description char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _blacklist (_blacklist),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE spamtrap (
  _rcpt        char(60) NOT NULL default '',
  _active      tinyint(1) unsigned NOT NULL default '1',
  UNIQUE KEY _rcpt (_rcpt,_active)
) TYPE=MyISAM;

CREATE TABLE helo (
  _host        char(46) NOT NULL default '',
  _helo        char(60) NOT NULL default '',
  _expire      int(10) unsigned NOT NULL default '0',
  UNIQUE KEY _host (_host,_helo),
  KEY _expire (_expire)
) TYPE=MyISAM;

CREATE TABLE statistics (
  _action      char(60) NOT NULL default '',
  _count       bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (_action)
) TYPE=MyISAM;

--
-- lets initialize action entries on statistics table
--

INSERT INTO statistics (_action) VALUES ('blacklist=block');
INSERT INTO statistics (_action) VALUES ('blacklist_dnsname=block');
INSERT INTO statistics (_action) VALUES ('blacklist_helo=new');

INSERT INTO statistics (_action) VALUES ('greylist=abl');
INSERT INTO statistics (_action) VALUES ('greylist=abuse');
INSERT INTO statistics (_action) VALUES ('greylist=awl');
INSERT INTO statistics (_action) VALUES ('greylist=new');
INSERT INTO statistics (_action) VALUES ('greylist=new_train');
INSERT INTO statistics (_action) VALUES ('greylist=optout');
INSERT INTO statistics (_action) VALUES ('greylist=update');
INSERT INTO statistics (_action) VALUES ('greylist=update_train');

INSERT INTO statistics (_action) VALUES ('helo=abuse');

INSERT INTO statistics (_action) VALUES ('spamtrap=new');

INSERT INTO statistics (_action) VALUES ('throttle=abuse(f)');
INSERT INTO statistics (_action) VALUES ('throttle=blacklisted(f)');
INSERT INTO statistics (_action) VALUES ('throttle=clear(a)');
INSERT INTO statistics (_action) VALUES ('throttle=new(a)');
INSERT INTO statistics (_action) VALUES ('throttle_update');

INSERT INTO statistics (_action) VALUES ('throttle_rcpt=abuse(f)');
INSERT INTO statistics (_action) VALUES ('throttle_rcpt=clear(a)');
INSERT INTO statistics (_action) VALUES ('throttle_rcpt=new(a)');
INSERT INTO statistics (_action) VALUES ('throttle_rcpt=update');

INSERT INTO statistics (_action) VALUES ('whitelist_dnsname=update');
INSERT INTO statistics (_action) VALUES ('whitelist_sender=update');
INSERT INTO statistics (_action) VALUES ('whitelist=update');
