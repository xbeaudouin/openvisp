# $Id: 0.96_0.97-dev.sql,v 1.92 2009/09/27 08:38:18 ngoralski Exp $
# 
# Update accounts table
#
ALTER TABLE accounts ADD company varchar(255) DEFAULT "";
ALTER TABLE accounts ADD address varchar(255) DEFAULT "";
ALTER TABLE accounts ADD city varchar(255) DEFAULT "";
ALTER TABLE accounts ADD postal_code varchar(40) DEFAULT "";
ALTER TABLE accounts ADD weburl varchar(255) DEFAULT "";
ALTER TABLE accounts ADD email varchar(255) DEFAULT "";
ALTER TABLE accounts ADD phone varchar(50) DEFAULT "";
ALTER TABLE accounts ADD fax varchar(50) DEFAULT "";
ALTER TABLE accounts ADD logo varchar(255) DEFAULT "";
ALTER TABLE accounts ADD emailsupport varchar(150) DEFAULT "";
ALTER TABLE accounts ADD phonesupport varchar(30) DEFAULT "";
ALTER TABLE accounts ADD websupport varchar(255) DEFAULT "";
ALTER TABLE accounts ADD webfaq varchar(255) DEFAULT "";

#
# Update Filter
#
INSERT INTO  `filter_field` ( `num` , `fieldname` , `active` , `modificationdate` , `creationdate` ) VALUES (NULL ,  'List-Post',  '1', NOW( ) , NOW( )), (NULL ,  'List-Id',  '0', NOW( ) , NOW( )), (NULL, 'List-Help', '1', NOW(), NOW()), (NULL, 'Delivered-To', '0', NOW(), NOW()), (NULL, 'To', '1', NOW(), NOW());


#
# Rights Table 
#

CREATE TABLE IF NOT EXISTS `rights` ( `id` int(11) NOT NULL auto_increment, `mail` tinyint(1) NOT NULL default '0', `datacenter` tinyint(1) NOT NULL default '0', `ftp` tinyint(1) NOT NULL default '0', `http` tinyint(1) NOT NULL default '0', `domain` tinyint(1) NOT NULL default '0', `mysql` tinyint(1) NOT NULL default '0', `postgresql` tinyint(1) NOT NULL default '0', `manage` tinyint(1) NOT NULL default '0', PRIMARY KEY  (`id`) ) ENGINE=MYISAM COMMENT='OpenVISP - Rights';


#
#
ALTER TABLE  `rights` ADD  `domain` TINYINT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `rights` ADD  `postgresql` TINYINT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `rights` ADD  `mysql` TINYINT( 1 ) NOT NULL DEFAULT  '0';
ALTER TABLE  `rights` ADD  `manage` TINYINT( 1 ) NOT NULL DEFAULT  '0';
#
#
# Version
CREATE TABLE IF NOT EXISTS `ovavers` ( `ova` char(20), `query` smallint(2) NOT NULL default '0' ) ENGINE=MYISAM;
#
#
# Perforce

CREATE TABLE IF NOT EXISTS spamreport (  id varchar(32) NOT NULL,  key2 varchar(32) NOT NULL,  email varchar(255) NOT NULL,  created int(11) NOT NULL default '0',  PRIMARY KEY  (email,created) ) ENGINE=MYISAM COMMENT='Spam Repport status ';

INSERT INTO accounts (username, password, created, modified, enabled, company, address, city, postal_code, weburl, email, phone, fax, logo, emailsupport, phonesupport, websupport, webfaq) SELECT * from admin;

DROP TABLE admin;

#
# Get the the  admin account that manage few domains from account & domain_admins tables
# to put them in rights table
#
INSERT INTO rights(id,mail)
			 SELECT accounts.id, CONCAT('1')
			 FROM accounts,domain_admins, rights
			 WHERE accounts.username = domain_admins.username
			 AND domain_admins.domain != 'ALL'
			 AND accounts.id != rights.id
			 GROUP BY accounts.id;

#
# Get the super admin from accounts and domain_admins and insert them in rights table
#
INSERT INTO rights(id,mail,datacenter,ftp,http,mysql,postgresql,domain, manage)
			 SELECT accounts.id, CONCAT('1'), CONCAT('1'), CONCAT('1'), CONCAT('1'), CONCAT('1'), CONCAT('1'), CONCAT('1'), CONCAT('1')
			 FROM accounts,domain_admins, rights
			 WHERE accounts.username = domain_admins.username
			 AND domain_admins.domain = 'ALL'
			 AND accounts.id != rights.id
			 GROUP BY accounts.id;


CREATE TABLE quota (
  id int(11) NOT NULL,
  diskspace int(11) NOT NULL,
  ftp int(11) NOT NULL,
  mysqldb int(11) NOT NULL,
  mysqlusers int(11) NOT NULL,
  postgresqldb int(11) NOT NULL,
  postgresqlusers int(11) NOT NULL,
  domains int(11) NOT NULL,
  emails int(11) NOT NULL,
  emails_alias int(11) NOT NULL,
  http int(11) NOT NULL,
  http_alias int(11) NOT NULL,
  createdate timestamp NOT NULL default '0000-00-00 00:00:00', 
  modifiedate timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id),
  KEY id (id)
) ENGINE=MYISAM COMMENT='Openvisp admin quota';


#
# Get the the  admin account that manage few domains from account & domain_admins tables
# to put their quota
#
INSERT INTO quota(id,createdate,modifiedate)
			 SELECT accounts.id, NOW(), NOW()
			 FROM accounts,domain_admins,rights
			 WHERE accounts.username = domain_admins.username
			 AND domain_admins.domain != 'ALL'
			 AND accounts.id != rights.id
			 GROUP BY accounts.id;

#
# Get the super admin from accounts and domain_admins and insert them in quota table
#
INSERT INTO quota(id,diskspace,ftp,mysqldb,mysqlusers,postgresqldb,postgresqlusers,domains,emails,emails_alias,http,http_alias,createdate,modifiedate)
			 SELECT accounts.id, '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1', '-1',NOW(), NOW()
			 FROM accounts,domain_admins,rights
			 WHERE accounts.username = domain_admins.username
			 AND domain_admins.domain = 'ALL'
			 AND accounts.id != rights.id
			 GROUP BY accounts.id;

CREATE TABLE `dbtype` (
  `id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `dblist` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `dbtype` tinyint(2) unsigned NOT NULL,
  `dbname` char(64) default NULL,
  `createdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modifiedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `owner` (`owner`),
  KEY `domain` (`domain`),
  KEY `dbname` (`dbname`)
) ENGINE=MyISAM  COMMENT='Openvisp databases table' ;

CREATE TABLE IF NOT EXISTS `dbusers` (
  `id` int(11) NOT NULL auto_increment,
  `owner` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `dbtype` tinyint(2) unsigned NOT NULL,
  `username` char(16) default NULL,
  `password` char(20) default NULL,
  `createdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modifiedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `id` (`id`),
  KEY `owner` (`owner`),
  KEY `domain` (`domain`),
  KEY `dbtype` (`dbtype`),
  KEY `username` (`username`)
) ENGINE=MyISAM COMMENT='Openvisp databases users table';


#
# Data Center updates to new auth system
#
ALTER TABLE apc_admins DROP INDEX username;
ALTER TABLE apc_admins CHANGE username id INT(11) NOT NULL;
ALTER TABLE apc_admins ADD PRIMARY KEY (id);
ALTER TABLE datacenter_admins CHANGE username id INT(11) NOT NULL;
# ID=0 of datacenter_admins is dead
DELETE FROM datacenter_admins WHERE id=0;
INSERT INTO datacenter_admins (id,full,created,modified) VALUES (1,1,NOW(),NOW());

#
# Add isalias field into whost table
#
ALTER TABLE whost ADD isalias varchar(255) DEFAULT "0" AFTER CustomLog;


ALTER TABLE  ftpaccount
CHANGE `owner`  `owner` INT( 11 ) NOT NULL,
ADD `server` varchar(30) ;

ALTER TABLE apc_admins DROP PRIMARY KEY;

## 08/07/2007
CREATE TABLE server (
  id int(11) NOT NULL AUTO_INCREMENT,
  servername varchar(50) NOT NULL,
  `desc` VARCHAR( 100 ) NOT NULL,
  fk_server_type_id int(4) NOT NULL,
  isdefault tinyint(1) NOT NULL,
  modified datetime NOT NULL,
  created datetime NOT NULL,
  active tinyint(1) NOT NULL,
	PRIMARY KEY (id),
  FULLTEXT KEY `desc` (`desc`)
) ENGINE=MYISAM COMMENT='Openvisp databases server table';

CREATE TABLE server_type (
  id int(4) NOT NULL AUTO_INCREMENT,
  type varchar(50) NOT NULL,
  modified datetime NOT NULL,
  created datetime NOT NULL,
  active tinyint(1) NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MYISAM COMMENT='Openvisp databases server_type table';

## 10/07/2007 / NG 

CREATE TABLE ftpaccount_stat (
  ftpaccount varchar(200) NOT NULL,
  size int(15) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (ftpaccount,date)
) ENGINE=MYISAM COMMENT='Openvisp database ftpaccount_stat table';


CREATE TABLE ftptransfert_stat (
  ftpaccount varchar(200) NOT NULL,
  upload bigint(40) default '0',
  download bigint(40) default '0',
	server varchar(50) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY  (ftpaccount,`date`)
) ENGINE=MYISAM COMMENT='Openvisp database ftptransfert_stat table';

## 22/10/2007 / XB

## Change APC schemas.
## This will loose all APC datas.

DROP TABLE apc;
DROP TABLE apc_admins;
DROP TABLE apc_ports;

CREATE TABLE `apc` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(12) NOT NULL default '''apc''',
  `ip` varchar(16) NOT NULL default '',
  `nbports` int(2) NOT NULL default '8',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MYISAM COMMENT='APC Masterswitch List';

CREATE TABLE `apc_ports` (
  `port` tinyint(1) unsigned NOT NULL default '0',
  `descr` varchar(24) NOT NULL default '',
  `apc` int(11) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '1',
  UNIQUE KEY `port` (`port`,`apc`)
) TYPE=MYISAM COMMENT='APC MasterSwitch Ports';

CREATE TABLE `apc_admins` (
  `id` int(11) NOT NULL,
  `apc` int(11) NOT NULL,
  `port` tinyint(1) NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL default '0'
) TYPE=MYISAM COMMENT='APC MasterSwitch Admins table';


ALTER TABLE `apc_ports` CHANGE `descr` `descr` VARCHAR(24);

## 24/01/2008: Table for Amavisd storage

CREATE TABLE `msgs` (
   `mail_id` varchar(12) NOT NULL default '',
   `secret_id` varchar(12) default '',
   `am_id` varchar(20) NOT NULL default '',
   `time_num` int(10) unsigned NOT NULL default '0',
   `time_iso` varchar(16) NOT NULL default '',
   `sid` int(10) unsigned NOT NULL default '0',
   `policy` varchar(255) default '',
   `client_addr` varchar(255) default '',
   `size` int(10) unsigned NOT NULL default '0',
   `content` char(1) default NULL,
   `quar_type` char(1) default NULL,
   `dsn_sent` char(1) default NULL,
   `spam_level` float default NULL,
   `message_id` varchar(255) default '',
   `from_addr` varchar(255) default '',
   `subject` varchar(255) default '',
   `host` varchar(255) NOT NULL default '',
   `quar_loc` varchar(255) default '',
   KEY `sid` (`sid`),
   KEY `time_num` (`time_num`),
   KEY `mail_id` (`mail_id`),
   KEY `time_iso` (`time_iso`),
   KEY `quar_type` (`quar_type`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;

CREATE TABLE `maddr` (
   `id` bigint(20) unsigned NOT NULL auto_increment,
   `email` varchar(255) NOT NULL default '',
   `domain` varchar(255) NOT NULL default '',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `maddr_idx_email` (`email`),
   KEY `maddr_idx_domain` (`domain`),
   KEY `email` (`email`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;

CREATE TABLE `msgrcpt` (
   `mail_id` varchar(12) NOT NULL default '',
   `rid` int(10) unsigned NOT NULL default '0',
   `ds` char(1) NOT NULL default '',
   `rs` char(1) NOT NULL default '',
   `bl` char(1) default '',
   `wl` char(1) default '',
   `bspam_level` float default NULL,
   `smtp_resp` varchar(255) default '',
   KEY `msgrcpt_idx_mail_id` (`mail_id`),
   KEY `msgrcpt_idx_rid` (`rid`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;

# 25/01/2008: Schema for storing files and stats stuff

CREATE TABLE `datacenter_sw_stats` (
  `id` int(11) NOT NULL auto_increment,
  `router` varchar(255) NOT NULL,
  `port` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `95per_lm_in` float default NULL,
  `95per_lm_out` float default NULL,
  `95per_lm_all` float default NULL,
  `95per_l2m_in` float default NULL,
  `95per_l2m_out` float default NULL,
  `95per_l2m_all` float default NULL,
  `95per_cur_in` float default NULL,
  `95per_cur_out` float default NULL,
  `95per_cur_all` float default NULL,
  `cfgfile` varchar(255) NOT NULL,
  `lm_cfgfile` int(11) NOT NULL,
  `rrdfile` varchar(255) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `router` (`router`),
  KEY `port` (`port`),
  KEY `rrdfile` (`rrdfile`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1 COMMENT='Switch files and stats' AUTO_INCREMENT=1 ;


# XB: 29/01/2008 longer APC names
ALTER TABLE `apc` CHANGE `name` `name` VARCHAR( 20 ) NOT NULL DEFAULT '''apc''';

# NG: 01/03/2008 paid mailbox status
ALTER TABLE `mailbox` ADD `paid` TINYINT( 1 ) NOT NULL DEFAULT  '1';
ALTER TABLE `accounts` ADD `paid` TINYINT( 1 ) NOT NULL DEFAULT  '1';
ALTER TABLE `ftpaccount` ADD `paid` TINYINT( 1 ) NOT NULL DEFAULT  '1';
ALTER TABLE `domain` ADD `paid` TINYINT( 1 ) NOT NULL DEFAULT  '1';

ALTER TABLE `mailbox` ADD INDEX ( `maildir` );


# 2008/03/10 NG

ALTER TABLE `whost` ADD `synced` TINYINT(1) NOT NULL DEFAULT '0';


-- SQL Order for future use with apache.
-- Do no use at this time
-- CREATE TABLE `whost_options` (
--   `id` int(11) NOT NULL auto_increment,
--   `name` varchar(255) NOT NULL,
-- 	`type` varchar(255) NOT NULL,
--   `description` varchar(255) NOT NULL,
--   `active` int(1) NOT NULL,
--   `created` datetime NOT NULL default '0000-00-00 00:00:00',
--   `modified` datetime NOT NULL default '0000-00-00 00:00:00',
--   PRIMARY KEY  (`id`),
--   KEY `name` (`name`)
-- ) ENGINE=MYISAM DEFAULT CHARSET=latin1 COMMENT='Table with option list for webhosting' AUTO_INCREMENT=1 ;
	

-- CREATE TABLE `whost_config` (
--   `whost_id` int(11) NOT NULL,
--   `option_id` int(11) NOT NULL,
--   `value` varchar(255) NOT NULL,
--   `active` int(1) NOT NULL,
--   `created` datetime NOT NULL default '0000-00-00 00:00:00',
--   `modified` datetime NOT NULL default '0000-00-00 00:00:00',
--   PRIMARY KEY  (`whost_id`,`option_id`)

-- ) ENGINE=MYISAM DEFAULT CHARSET=latin1 COMMENT='Assocation of Whost option and whost site' ;


-- INSERT INTO  `whost_options` ( `name` , `description` , `active` , `created` , `modified` )  VALUES 
-- ('ServerAdmin', '', '1', NOW( ) , NOW( )), 
-- ('php_admin_value',  '', '0', NOW( ) , NOW( )), 
-- ('', '', '1', NOW(), NOW()), 
-- ('', '', '0', NOW(), NOW()), 
-- ('', '', '1', NOW(), NOW())
-- ;

# NG 13/03/2008
ALTER TABLE `whost` ADD `paid` TINYINT( 1 ) NOT NULL DEFAULT  '1';




# NG 23/03/2008 db quota by domain
ALTER TABLE `domain`
ADD `db_count` INT( 11 ) NOT NULL DEFAULT '0' AFTER `ftp_account` ,
ADD `db_users` INT( 11 ) NOT NULL DEFAULT '0' AFTER `db_count` ,
ADD `db_quota` INT( 11 ) NOT NULL DEFAULT '0' AFTER `db_users` ;
ALTER TABLE `mailbox` ADD INDEX ( `maildir` );



# NG 11/06/2008
DROP TABLE IF EXISTS `servers`;
DROP TABLE IF EXISTS `servers_type`;

#ALTER TABLE `server`
#  DROP `public_ip4`,
#  DROP `private_ip4`,
#  DROP `public_ip6`;

CREATE TABLE IF NOT EXISTS `server_ip` (
  `pk_server_ip` int(11) NOT NULL AUTO_INCREMENT,
  `public` varchar(15) NOT NULL,
  `private` varchar(15) NOT NULL,
  `comment` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`pk_server_ip`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;


CREATE TABLE `server_job` (
  `fk_server_id` int(11) NOT NULL,
  `fk_server_jobmodel_id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `port` tinyint(2) NOT NULL,
  `desc` tinytext NOT NULL,
  `active` tinyint(4) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`fk_server_id`,`fk_server_jobmodel_id`),
  FULLTEXT KEY `desc` (`desc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE  `server_job`
ADD  `fk_server_app` INT NOT NULL AFTER  `fk_server_jobmodel_id` ,
ADD  `fk_server_ip` INT NOT NULL AFTER  `fk_server_app` ;

ALTER TABLE  `server_job`
CHANGE  `desc`  `description` TINYTEXT NOT NULL;

ALTER TABLE  `server`
CHANGE  `desc`  `description` TINYTEXT NOT NULL;


CREATE TABLE IF NOT EXISTS `wblist` (
  `rid` int(10) unsigned NOT NULL,
  `sid` int(10) unsigned NOT NULL,
  `wb` varchar(10) NOT NULL,
  PRIMARY KEY  (`rid`,`sid`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1;




CREATE TABLE IF NOT EXISTS `whost_config` (
  `whost_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`whost_id`,`option_id`)
) ENGINE=MYISAM DEFAULT CHARSET=latin1 COMMENT='Assocation of Whost option and whost site';


CREATE TABLE IF NOT EXISTS `whost_options` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `values` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `parent_id` tinyint(11) NOT NULL default '0',
  `categorie_id` tinyint(11) NOT NULL,
  `active` int(1) NOT NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MYISAM  DEFAULT CHARSET=latin1 COMMENT='Table with option list for webhosting' AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `whost_option_cat` (
  `int` int(11) NOT NULL,
  `categorie` varchar(255) NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET=latin1;



# 16 06 2008
ALTER TABLE  `domain` ADD  `status` TINYINT( 1 ) NOT NULL COMMENT  'Domain Status';

# 26 07 2008

CREATE TABLE  IF NOT EXISTS `server_jobmodel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(255) NOT NULL,
  `description` tinytext NOT NULL,
  `active` tinyint(1) NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role` (`role`),
  FULLTEXT KEY `desc` (`description`)
) ENGINE=MYISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `server_apps` (
  `id` int(11)  NOT NULL AUTO_INCREMENT,
  `apps` varchar(255) NOT NULL,
  `version` varchar(20) default '0',
  `description` tinytext NOT NULL,
  `active` tinyint(1) NOT NULL,
  createdate timestamp NOT NULL default '0000-00-00 00:00:00', 
  modifiedate timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (id)

)  ENGINE=MYISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;


SELECT CONCAT('test');

CREATE  TABLE  whost_alias (  `pk_id` int( 11  )  NOT  NULL  auto_increment ,
 `vhost_alias` varchar( 255  )  NOT  NULL ,
 `fk_vhost_id` int( 11 ) NOT NULL ,
 `active` tinyint( 1  )  NOT  NULL default  '1',
 `created` datetime NOT  NULL default  '0000-00-00 00:00:00',
 `modified` datetime NOT  NULL default  '0000-00-00 00:00:00',
 PRIMARY  KEY (  `pk_id`  )  ) ENGINE  =  MYISAM DEFAULT CHARSET  = latin1 COMMENT  =  'Alias Web Hosting Table';


ALTER TABLE `whost`
  DROP `owner`,
  DROP `isalias`,
  DROP `enabled`;



CREATE TABLE IF NOT EXISTS `mail_login_logs` (
  `firstlogin` datetime NOT NULL,
  `lastlogin` datetime NOT NULL,
  `login` varchar(150) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `method` varchar(10) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `hostname` varchar(50) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `comment` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `whost_server` (
  `fk_whost_id` int(11) NOT NULL,
  `fk_server_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `createdate` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modifiedate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`fk_server_id`,`fk_whost_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Migrate to innodb

ALTER TABLE  `server_jobmodel` DROP INDEX  `desc`;
ALTER TABLE  `server` DROP INDEX `desc`;
ALTER TABLE  `server_job` DROP INDEX  `desc`;



ALTER TABLE  `accounts` ENGINE = INNODB;
ALTER TABLE  `alias` ENGINE = INNODB;
ALTER TABLE  `apc` ENGINE = INNODB;
ALTER TABLE  `apc_admins` ENGINE = INNODB;
ALTER TABLE  `apc_ports` ENGINE = INNODB;
ALTER TABLE  `datacenter_admins` ENGINE = INNODB;
ALTER TABLE  `datacenter_sw_stats` ENGINE = INNODB;
ALTER TABLE  `dblist` ENGINE = INNODB;
ALTER TABLE  `dbtype` ENGINE = INNODB;
ALTER TABLE  `dbusers` ENGINE = INNODB;
ALTER TABLE  `domain` ENGINE = INNODB;
ALTER TABLE  `domain_admins` ENGINE = INNODB;
ALTER TABLE  `domain_alias` ENGINE = INNODB;
ALTER TABLE  `filter` ENGINE = INNODB;
ALTER TABLE  `filteraction_field` ENGINE = INNODB;
ALTER TABLE  `filter_field` ENGINE = INNODB;
ALTER TABLE  `ftpaccount` ENGINE = INNODB;
ALTER TABLE  `ftpaccount_stat` ENGINE = INNODB;
ALTER TABLE  `ftptransfert_stat` ENGINE = INNODB;
ALTER TABLE  `lmtp` ENGINE = INNODB;
ALTER TABLE  `log` ENGINE = INNODB;
ALTER TABLE  `maddr` ENGINE = INNODB;
ALTER TABLE  `mailbox` ENGINE = INNODB;
ALTER TABLE  `mailbox_stat` ENGINE = INNODB;
ALTER TABLE  `mail_login_logs` ENGINE = INNODB;
ALTER TABLE  `msgrcpt` ENGINE = INNODB;
ALTER TABLE  `msgs` ENGINE = INNODB;
ALTER TABLE  `ovavers` ENGINE = INNODB;
ALTER TABLE  `overquota` ENGINE = INNODB;
ALTER TABLE  `policy` ENGINE = INNODB;
ALTER TABLE  `quota` ENGINE = INNODB;
ALTER TABLE  `rights` ENGINE = INNODB;
ALTER TABLE  `server` ENGINE = INNODB;
ALTER TABLE  `server_apps` ENGINE = INNODB;
ALTER TABLE  `server_ip` ENGINE = INNODB;
ALTER TABLE  `server_job` ENGINE = INNODB;
ALTER TABLE  `server_jobmodel` ENGINE = INNODB;
ALTER TABLE  `spamreport` ENGINE = INNODB;
ALTER TABLE  `stats_admin` ENGINE = INNODB;
ALTER TABLE  `vacation` ENGINE = INNODB;
ALTER TABLE  `wblist` ENGINE = INNODB;
ALTER TABLE  `whost` ENGINE = INNODB;
ALTER TABLE  `whost_alias` ENGINE = INNODB;
ALTER TABLE  `whost_config` ENGINE = INNODB;
ALTER TABLE  `whost_options` ENGINE = INNODB;
ALTER TABLE  `whost_option_cat` ENGINE = INNODB;
ALTER TABLE  `whost_server` ENGINE = INNODB;


CREATE TABLE IF NOT EXISTS `stats_mail_user` (
   fk_mailbox_id int(11) NOT NULL,
	 date int(3) NOT NULL,
	 last_date int(10) NOT NULL,
	 bytes_in int(10) NULL,
	 bytes_out int(10) NULL,
	 spam int(10) NULL,
	 virus int(10) NULL,
	 clean int(10) NULL,
	 blocked int(10) NULL,
	 malformed int(10) NULL
)  ENGINE=INNODB  DEFAULT CHARSET=latin1;



# 12 08 2008
# Modif pour les foreigns keys

ALTER TABLE  `accounts`
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE  `modified`  `modified` TIMESTAMP NULL;

ALTER TABLE  `alias`
CHANGE  `policy_id`  `policy_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT  '1',
CHANGE  `modified`  `modified` TIMESTAMP NULL;

-- ALTER TABLE `apc`
-- DROP PRIMARY KEY,
-- ADD `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
-- ADD PRIMARY KEY  (id);

-- ALTER TABLE `apc_ports`
-- DROP INDEX `port`,
-- ADD `apc_id` INT( 11 ) UNSIGNED NOT NULL;

-- ALTER TABLE  `apc_admins`
-- DROP PRIMARY KEY,
-- DROP INDEX `apc`,
-- ADD `apc_id` INT( 11 ) UNSIGNED NOT NULL AFTER `port`,
-- CHANGE  `id`  `accounts_id` INT( 11 ) UNSIGNED NOT NULL,
-- CHANGE  `port`  `apc_ports_id` INT( 1 ) UNSIGNED NOT NULL;

-- ALTER TABLE `apc_ports`
-- ADD `apc_id`  INT( 11 ) UNSIGNED NOT NULL FIRST;

ALTER TABLE  `datacenter_admins`
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL;

RENAME TABLE `datacenter_sw_stats` TO `stats_datacenter_sw`;

ALTER TABLE  `stats_datacenter_sw`
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

RENAME TABLE `dblist`  TO `dbname` ;

ALTER TABLE  `dbname`
DROP INDEX `id`,
DROP INDEX `owner`,
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `owner` `accounts_id`  INT( 11 ) UNSIGNED NOT NULL,
CHANGE  `dbname`  `dbname` CHAR( 64 )  NOT NULL,
CHANGE  `dbtype`  `dbtype_name`  VARCHAR( 30 ) NOT NULL,
CHANGE `createdate` `created` TIMESTAMP NULL,
CHANGE `modifiedate` `modified` TIMESTAMP NULL,
ADD `domain_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `dbtype_id` TINYINT( 2 ) UNSIGNED NOT NULL;


ALTER TABLE  `dbtype`
CHANGE `type` `name` VARCHAR( 20 ),
CHANGE `created` `created` TIMESTAMP NULL,
CHANGE `modified` `modified` TIMESTAMP NULL;


ALTER TABLE  `dbusers`
DROP INDEX `id`,
CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `createdate` `created` TIMESTAMP NULL,
CHANGE `modifiedate` `modified` TIMESTAMP NULL,
CHANGE  `dbtype`  `dbtype_name`  VARCHAR( 30 ) NOT NULL,
CHANGE `owner` `accounts_id`  INT( 11 ) UNSIGNED NOT NULL,
ADD `domain_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `dbtype_id` TINYINT( 2 ) UNSIGNED NOT NULL;

ALTER TABLE `domain`
DROP PRIMARY KEY;

ALTER TABLE `domain`
ADD `pk_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY  (pk_id);

ALTER TABLE  `domain`
DROP PRIMARY KEY,
CHANGE `pk_id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
CHANGE `created` `created` TIMESTAMP NULL,
CHANGE `modified` `modified` TIMESTAMP NULL,
ADD PRIMARY KEY  (id),
ADD UNIQUE ( `domain` );


ALTER TABLE  `domain_admins`
ADD  `accounts_id` INT( 11 ) UNSIGNED NOT NULL FIRST ,
ADD  `domain_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `accounts_id`,
CHANGE `created` `created` TIMESTAMP NULL;


ALTER TABLE  `domain_alias` ADD
`domain_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `dalias` ;

 
ALTER TABLE  `filter`
DROP PRIMARY KEY,
DROP `num`,
ADD  `mailbox_id` INT( 11 ) UNSIGNED NOT NULL FIRST,
CHANGE  `fk_fieldnum`  `filter_field_id` TINYINT( 6 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `fk_actionnum`  `filteraction_id` TINYINT( 4 ) UNSIGNED NOT NULL DEFAULT  '0',
CHANGE  `modificationdate`  `modified` TIMESTAMP NULL,
CHANGE  `creationdate`  `created` TIMESTAMP NULL;



ALTER TABLE  `filteraction_field`
CHANGE `num`  `id` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE  `modificationdate`  `modified` TIMESTAMP NULL,
CHANGE  `creationdate`  `created` TIMESTAMP NULL;


ALTER TABLE  `filter_field`
CHANGE `num` `id` TINYINT( 3 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE  `modificationdate`  `modified` TIMESTAMP NULL,
CHANGE  `creationdate`  `created` TIMESTAMP NULL;



ALTER TABLE `ftpaccount`
DROP PRIMARY KEY,
ADD `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
ADD PRIMARY KEY  (id),
CHANGE `fk_whost_id` `server_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `domain_id` INT( 11 ) UNSIGNED NOT NULL AFTER `domain`;


RENAME TABLE `ftpaccount_stat` TO `stats_ftpaccount`;

ALTER TABLE `stats_ftpaccount`
DROP PRIMARY KEY,
ADD `ftpaccount_id` INT( 11 ) UNSIGNED NOT NULL FIRST;

RENAME TABLE `ftptransfert_stat` TO `stats_ftptransfert`;

ALTER TABLE `stats_ftptransfert`
DROP PRIMARY KEY,
ADD `ftpaccount_id` INT( 11 ) UNSIGNED NOT NULL FIRST,
ADD `server_id` INT( 11 ) UNSIGNED NOT NULL AFTER `server`;



ALTER TABLE `log`
DROP INDEX `timestamp`,
CHANGE `timestamp` `timestamp` TIMESTAMP NULL,
ADD `accounts_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `timestamp`,
ADD `domain_id` INT(11) unsigned NOT NULL AFTER `accounts_id`,
ADD `ip` VARCHAR( 20 ) NOT NULL AFTER `domain_id`,
ADD `login` VARCHAR( 255 ) NULL;



CREATE TABLE IF NOT EXISTS `log_dup` (
  `timestamp` timestamp NULL default NULL,
  `accounts_id` int(11) unsigned NOT NULL,
  `domain_id` int(11) unsigned NOT NULL,
  `ip` varchar(20) NOT NULL,
  `username` varchar(255) NOT NULL default '',
  `domain` varchar(255) NOT NULL default '',
  `action` varchar(255) NOT NULL default '',
  `data` varchar(255) NOT NULL default '',
  `login` varchar(255) default NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='OpenVISP Admin - Log';

INSERT INTO log_dup 
SELECT DISTINCT * FROM log
GROUP BY `timestamp`, `accounts_id`,`domain_id`,`data`;

TRUNCATE TABLE `log`;
INSERT INTO log_dup SELECT * FROM log_dup;
DROP TABLE log_dup;


ALTER TABLE  `mailbox`
DROP INDEX `maildir_2`,
DROP PRIMARY KEY,
ADD `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL,
ADD `domain_id` INT( 11 ) UNSIGNED NOT NULL AFTER `username`,
ADD `pop` TINYINT( 1 )  NOT NULL DEFAULT '1' AFTER `options` ,
ADD `imap` TINYINT( 1 )  NOT NULL DEFAULT '1' AFTER `pop` ,
ADD `webmail` TINYINT( 1 )  NOT NULL DEFAULT '1' AFTER `imap`,
ADD PRIMARY KEY  (id),
ADD UNIQUE ( `username` );


ALTER TABLE `policy`
CHANGE `modified` `modified` TIMESTAMP NULL,
ADD `domain_id`  INT( 11 ) UNSIGNED NOT NULL FIRST;


ALTER TABLE  `quota`
CHANGE `id`  `accounts_id` INT( 11 ) UNSIGNED NOT NULL,
CHANGE `createdate` `created` TIMESTAMP NULL ,
CHANGE `modifiedate` `modified` TIMESTAMP NULL;


ALTER TABLE  `rights`
CHANGE `id`  `accounts_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `datacenter_manage` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `datacenter`,
DROP `username`;


ALTER TABLE  `server`
CHANGE `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL;


ALTER TABLE `server_apps`
CHANGE `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `createdate` `created` TIMESTAMP NULL ,
CHANGE `modifiedate` `modified` TIMESTAMP NULL;


ALTER TABLE `server_ip`
CHANGE `pk_server_ip`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL;


ALTER TABLE `server_job`
CHANGE `fk_server_id` `server_id` INT( 11 ) UNSIGNED NOT NULL,
CHANGE `fk_server_jobmodel_id` `server_jobmodel_id` INT( 11 ) UNSIGNED NOT NULL,
CHANGE `fk_server_app` `server_apps_id` INT( 11 ) UNSIGNED NOT NULL,
CHANGE `fk_server_ip` `server_ip_id` INT( 11 ) UNSIGNED NOT NULL,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL;



#ALTER TABLE `server_jobmodel`
#DROP INDEX `role_2`;

ALTER TABLE `server_jobmodel`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;


ALTER TABLE `spamreport`
DROP PRIMARY KEY,
ADD  `mailbox_id` INT( 11 ) UNSIGNED NOT NULL AFTER `email`,
CHANGE `created` `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ;


ALTER TABLE `stats_admin`
ADD  `accounts_id` INT( 11 ) UNSIGNED NOT NULL FIRST,
DROP INDEX `username`;

RENAME TABLE `mailbox_stat` TO `stats_mailbox`;

ALTER TABLE `stats_mailbox`
DROP PRIMARY KEY,	
ADD `mailbox_id` INT( 11 ) UNSIGNED NOT NULL FIRST,
CHANGE `date` `date` TIMESTAMP NULL;

RENAME TABLE `mail_login_logs` TO `stats_mail_login` ;

ALTER TABLE `stats_mail_login`
DROP `DOMAIN`,
ADD `mailbox_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `login`,
ADD `count` INT( 6 ) NOT NULL DEFAULT '0' AFTER `hostname` ;


ALTER TABLE `stats_mail_user`
CHANGE `fk_mailbox_id` `mailbox_id` INT( 11 ) UNSIGNED NOT NULL;


ALTER TABLE `vacation`
ADD  `mailbox_id` INT( 11 ) UNSIGNED NOT NULL FIRST,
CHANGE `created` `created` TIMESTAMP NULL ,
ADD `modified` TIMESTAMP NULL;

ALTER TABLE `wblist`  COMMENT = '# Table used by Amavis to Whitelist email' ;

ALTER TABLE `whost`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL,
ADD `domain_id`  INT( 11 ) UNSIGNED NOT NULL AFTER domain;


ALTER TABLE `whost_alias`
CHANGE `pk_id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `fk_vhost_id` `whost_id` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL;


ALTER TABLE `whost_config`
CHANGE `whost_id` `whost_id` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `option_id` `option_id` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `created` `created` TIMESTAMP NULL ,
CHANGE `modified` `modified` TIMESTAMP NULL;


ALTER TABLE `whost_options`
CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
CHANGE `modified` `modified` TIMESTAMP NULL,
CHANGE  `created`  `created` TIMESTAMP NULL;


ALTER TABLE `whost_server`
CHANGE `createdate` `created` TIMESTAMP NULL,
CHANGE `modifiedate` `modified` TIMESTAMP NULL,
CHANGE `fk_whost_id` `whost_id` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `fk_server_id` `server_id` INT( 11 ) UNSIGNED NOT NULL ;


# Update des champs lies

-- UPDATE apc_admins, apc SET apc_admins.apc_id=apc.id WHERE apc_admins.apc=apc.name;
-- UPDATE apc_ports, apc SET apc_ports.apc_id=apc.id WHERE apc_ports.apc=apc.name;

UPDATE dbname, domain SET dbname.domain_id=domain.id WHERE dbname.domain=domain.domain;
UPDATE dbname, dbtype SET dbname.dbtype_id=dbtype.id WHERE dbname.dbtype_name=dbtype.name;

UPDATE dbusers, domain SET dbusers.domain_id=domain.id WHERE dbusers.domain=domain.domain;
UPDATE dbusers, dbtype SET dbusers.dbtype_id=dbtype.id WHERE dbusers.dbtype_name=dbtype.name;


INSERT INTO domain_admins(`username`,`domain`,`created`,`active`)
SELECT DISTINCT domain_admins.username, domain.domain, NOW(), '1'
FROM domain_admins, domain
WHERE domain_admins.domain='ALL';

DELETE FROM domain_admins WHERE domain='ALL';

UPDATE domain_admins, accounts SET accounts_id=accounts.id WHERE accounts.username=domain_admins.username;
UPDATE domain_admins, domain SET domain_admins.domain_id=domain.id WHERE domain.domain=domain_admins.domain;

UPDATE domain_alias, domain SET domain_alias.domain_id=domain.id WHERE domain_alias.domain=domain.domain;

UPDATE filter, mailbox SET filter.mailbox_id=mailbox.id WHERE filter.email=mailbox.username;

UPDATE ftpaccount, domain SET ftpaccount.domain_id=domain.id WHERE ftpaccount.domain=domain.domain;
UPDATE ftpaccount, server SET ftpaccount.server_id=server.id WHERE ftpaccount.server=server.servername;

UPDATE log SET login=LEFT( username, LOCATE(' ', username) ),
ip=REPLACE(REPLACE(SUBSTR(username, LOCATE(' ', username) ), '(',''), ')', '') ;
UPDATE log, accounts SET log.accounts_id=accounts.id WHERE accounts.username=log.login;
UPDATE log, domain SET log.domain_id=domain.id WHERE domain.domain=log.domain;

DELETE FROM log WHERE accounts_id = 0 OR domain_id = 0;

UPDATE mailbox, domain SET mailbox.domain_id=domain.id WHERE mailbox.domain=domain.domain;

UPDATE policy, domain SET policy.domain_id=domain.id WHERE policy.domain=domain.domain;

UPDATE stats_ftpaccount, ftpaccount SET stats_ftpaccount.ftpaccount_id=ftpaccount.id WHERE stats_ftpaccount.ftpaccount=ftpaccount.login;

UPDATE stats_ftptransfert, ftpaccount SET stats_ftptransfert.ftpaccount_id=ftpaccount.id WHERE stats_ftptransfert.ftpaccount=ftpaccount.login;
UPDATE stats_ftptransfert, server SET stats_ftptransfert.server_id=server.id WHERE stats_ftptransfert.server=server.servername;

UPDATE stats_mailbox, mailbox SET stats_mailbox.mailbox_id=mailbox.id WHERE stats_mailbox.email=mailbox.username;

UPDATE stats_mail_login, mailbox SET stats_mail_login.mailbox_id=mailbox.id WHERE stats_mail_login.login=mailbox.username;

UPDATE spamreport, mailbox SET spamreport.mailbox_id=mailbox.id WHERE spamreport.email=mailbox.username;

UPDATE stats_admin, accounts SET stats_admin.accounts_id=accounts.id WHERE stats_admin.username=accounts.username;

UPDATE vacation, mailbox SET vacation.mailbox_id=mailbox.id WHERE vacation.email=mailbox.username;

UPDATE whost, domain SET whost.domain_id=domain.id WHERE whost.domain=domain.domain;


UPDATE alias, policy, domain
SET alias.policy_id = policy.id
WHERE alias.goto like CONCAT('@%',domain.domain)
AND domain.id = policy.domain_id;

DROP TABLE IF EXISTS tmp_ng1 ;

CREATE TABLE tmp_ng1
SELECT alias.address, alias.goto, policy.id, pol2.id as actual_id
FROM domain, policy, alias left join policy as pol2 on (alias.policy_id=pol2.id)
WHERE alias.address like CONCAT('%@',domain.domain)
AND domain.id = policy.domain_id
AND policy.id != pol2.id;

UPDATE alias, tmp_ng1
SET alias.policy_id  = tmp_ng1.id
WHERE alias.address = tmp_ng1.address
AND alias.goto = tmp_ng1.goto
AND alias.policy_id = tmp_ng1.actual_id;

DROP TABLE tmp_ng1 ;


# Suppression des vieux CHAMPS

ALTER TABLE `alias`
DROP domain;

-- ALTER TABLE `apc_admins`
-- DROP `apc`;

-- ALTER TABLE `apc_ports`
-- DROP `apc`;


ALTER TABLE `dbname`
DROP `domain`,
DROP `dbtype_name`;

ALTER TABLE `dbusers`
DROP `domain`,
DROP `dbtype_name`;

ALTER TABLE `domain_alias`
DROP `domain`;

ALTER TABLE `domain_admins`
DROP `username`,
DROP `domain`;


ALTER TABLE `filter`
DROP `email`;

ALTER TABLE `ftpaccount`
DROP `domain`,
DROP `server`;


ALTER TABLE `log`
DROP `login`,
DROP `username`;

ALTER TABLE `mailbox`
DROP `domain`;

ALTER TABLE `policy`
DROP `domain`;


ALTER TABLE `stats_ftpaccount`
DROP `ftpaccount`;

ALTER TABLE `stats_ftptransfert`
DROP `ftpaccount`,
DROP `server`;

ALTER TABLE `stats_mail_login` DROP `login`;

ALTER TABLE `stats_mailbox` DROP `email`;

ALTER TABLE `stats_admin` DROP username;

ALTER TABLE `vacation`
DROP `email`,
DROP `domain`;

ALTER TABLE `spamreport`
DROP `email`,
ADD PRIMARY KEY (`id`,`key2`,`mailbox_id`);

ALTER TABLE `whost`
DROP `domain`;


# Passage de champs modified en current_timestamp


ALTER TABLE  `accounts`
CHANGE  `modified`  `modified`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `alias`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `dbname`
CHANGE  `modified`  `modified`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `dbtype`
CHANGE  `modified`  `modified`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `dbusers`
CHANGE  `modified`  `modified`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `domain`
CHANGE  `modified`  `modified`  TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `filteraction_field`
CHANGE  `modified`  `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL		DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `filter_field`
CHANGE  `modified`  `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `log`
CHANGE `timestamp` `timestamp` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `mailbox`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `stats_mailbox`
CHANGE `date` `date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `policy`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `quota`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `server`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `server_apps`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `server_ip`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `server_job`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `vacation`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `whost`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `whost_alias`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `whost_config`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `whost_options`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `whost_server`
CHANGE `modified` `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;




#

DELETE FROM `spamreport`
WHERE mailbox_id=0;

DELETE FROM `stats_ftpaccount`
WHERE ftpaccount_id=0;

TRUNCATE TABLE `spamreport`;
TRUNCATE TABLE `stats_ftptransfert`;

DELETE FROM `vacation`
WHERE mailbox_id=0;

TRUNCATE TABLE `server_job`;


# Ajout des Cles Primaires

ALTER TABLE `filter`
ADD PRIMARY KEY `mailbox_id` (`mailbox_id`,`exec_order`,`filter_field_id`,`filteraction_id`,`created`);

ALTER TABLE `stats_mailbox`
ADD PRIMARY KEY `stats_mailbox` (`mailbox_id`,`date`);

# ALTER TABLE `stats_admin`
# ADD PRIMARY KEY `accounts_id_graphid` (`accounts_id`, `graphid`);

ALTER TABLE `stats_ftpaccount`
ADD PRIMARY KEY `ftpaccount_id_date` (`ftpaccount_id`, `date`);

ALTER TABLE `stats_ftptransfert`
ADD PRIMARY KEY `ftpaccount_id_server_id_date` (`ftpaccount_id`, `server_id`, `date`);

ALTER TABLE `stats_mail_login`
ADD PRIMARY KEY  `maillogin`  (`firstlogin`, `mailbox_id`, `method`,`ip`,`hostname`);

ALTER TABLE `stats_mail_user`
ADD PRIMARY KEY  `mailinfo`  (`mailbox_id`, `date`);

ALTER TABLE `vacation` ADD PRIMARY KEY(`mailbox_id`);

ALTER TABLE `whost_alias`
DROP PRIMARY KEY,
ADD PRIMARY KEY(`id`,`whost_id`);


# Ajout des Index
ALTER TABLE `accounts` ADD INDEX ( `username` );

ALTER TABLE `alias` ADD INDEX ( `policy_id` );

ALTER TABLE `dbname`
ADD INDEX (`accounts_id`),
ADD INDEX (`domain_id`),
ADD INDEX (`dbtype_id`);

ALTER TABLE `dbusers`
ADD INDEX (`accounts_id`),
ADD INDEX (`domain_id`),
ADD INDEX (`dbtype_id`);

ALTER TABLE `domain_admins`
ADD PRIMARY KEY  (`accounts_id`,`domain_id`);

ALTER TABLE `domain_alias`
ADD INDEX ( `domain_id` ) ;

ALTER TABLE `mailbox`
ADD INDEX ( `domain_id` ) ;

ALTER TABLE `policy`
ADD INDEX ( `domain_id` );

ALTER TABLE `ftpaccount`
ADD INDEX ( `server_id` ),
ADD INDEX ( `domain_id` );

ALTER TABLE `server` ADD INDEX ( `servername` );

ALTER TABLE `whost_alias` ADD UNIQUE (`vhost_alias`);

-- ALTER TABLE `apc` ADD INDEX ( `ip` );

-- ALTER TABLE `apc_admins`
-- ADD INDEX ( `name` ), ADD INDEX ( `apc` );


# Ajout des Regles pour le drop cascade

ALTER TABLE `alias`
ADD FOREIGN KEY ( `policy_id` ) REFERENCES `policy` (`id`) ON DELETE CASCADE;

# Alias Record that are orphan with policy.id
# select alias.* from alias left join policy on (alias.policy_id=policy.id) where alias.address is not null and policy.id is null;


ALTER TABLE `dbname`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `dbtype_id` ) REFERENCES `dbtype` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;


ALTER TABLE `dbusers`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `dbtype_id` ) REFERENCES `dbtype` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;


ALTER TABLE `domain_admins`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;

ALTER TABLE `domain_alias`
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;

ALTER TABLE `filter`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `filter_field_id` ) REFERENCES `filter_field` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `filteraction_id` ) REFERENCES `filteraction_field` (`id`) ON DELETE CASCADE;

ALTER TABLE  `ftpaccount`
ADD FOREIGN KEY (  `domain_id` ) REFERENCES  `domain` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (  `server_id` ) REFERENCES  `server` (`id`) ON DELETE CASCADE;


ALTER TABLE `mailbox`
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`)  ON DELETE CASCADE;

ALTER TABLE `quota`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

ALTER TABLE `rights`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

ALTER TABLE `spamreport`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE;

ALTER TABLE `stats_admin`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE;


ALTER TABLE  `stats_ftpaccount`
ADD FOREIGN KEY (  `ftpaccount_id` ) REFERENCES  `ftpaccount` (`id`) ON DELETE CASCADE ;

ALTER TABLE `stats_ftptransfert`
ADD FOREIGN KEY ( `ftpaccount_id` ) REFERENCES `ftpaccount` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE;

ALTER TABLE `stats_mailbox`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE;


ALTER TABLE `stats_mail_login`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE;

ALTER TABLE `stats_mail_user`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE;

ALTER TABLE `server_job`
ADD FOREIGN KEY ( `server_id` ) REFERENCES  `server` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_jobmodel_id` ) REFERENCES  `server_jobmodel` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_apps_id` ) REFERENCES  `server_apps` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_ip_id` ) REFERENCES  `server_ip` (`id`) ON DELETE CASCADE
;

ALTER TABLE `vacation`
ADD FOREIGN KEY ( `mailbox_id` ) REFERENCES `mailbox` (`id`) ON DELETE CASCADE;


ALTER TABLE `whost`
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;

ALTER TABLE  `whost_alias`
ADD FOREIGN KEY (  `whost_id` ) REFERENCES  `whost` (`id`) ON DELETE CASCADE ;


ALTER TABLE `whost_config`
ADD FOREIGN KEY ( `whost_id` ) REFERENCES `whost` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (  `option_id` ) REFERENCES  `whost_options` (`id`) ON DELETE CASCADE;


ALTER TABLE `whost_server`
ADD FOREIGN KEY ( `whost_id` ) REFERENCES `whost` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE;


##  New addons
# ALTER TABLE  `domain`
# ADD `status` TINYINT( 1 ) NOT NULL DEFAULT  '0';

ALTER TABLE `log` ADD `domain_name` VARCHAR(255) NOT NULL AFTER `domain_id`;

## Forgotten index
ALTER TABLE  `log`
ADD INDEX (  `accounts_id` ),
ADD INDEX (  `domain_id` ),
ADD INDEX (  `domain_name` );


ALTER TABLE  `log`
ADD FOREIGN KEY (  `accounts_id` ) REFERENCES  `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (  `domain_id` ) REFERENCES  `domain` (`id`) ON DELETE CASCADE;

INSERT INTO  `domain` (`domain` ,`description`, `created`)
VALUES ('ova.local',  'OVA Domain do not touch !!', NOW());

ALTER TABLE accounts
ADD  `tech` TINYINT( 1 ) UNSIGNED NOT NULL COMMENT  'Is the custormer a technical contact' AFTER enabled;

ALTER TABLE  `server_jobmodel`
CHANGE  `created`  `created` TIMESTAMP NULL,
CHANGE  `modified`  `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE  `server_apps`
ADD `server_jobmodel_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `version`,
ADD INDEX (  `server_jobmodel_id` );


CREATE TABLE IF NOT EXISTS `stats_wwwusage` (
  `whost_id` int(11) unsigned NOT NULL,
  `ldate` int(8) unsigned NOT NULL,
  `bytes` int(32) unsigned NOT NULL default '0',
  PRIMARY KEY  (`whost_id`,`ldate`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='# Table used by Lighttd to store Bandwith usage';


ALTER TABLE  `server_job` CHANGE  `port`  `port` SMALLINT( 2 ) UNSIGNED NOT NULL;



INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('database', 'Database Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('httpd', 'HTTPD Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('policy', 'Policyd Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('smtpd', 'SMTPD Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('pop', 'POP3 Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('imap', 'IMAP Server',  '1',  'NOW()');
INSERT INTO server_jobmodel (role, description, active, created ) VALUES ('ftpd', 'FTP Server',  '1',  'NOW()');


INSERT INTO `server_apps` (`id`, `apps`, `version`, `description`, `server_jobmodel_id`, `active`, `created`, `modified`) VALUES
(1, 'lighttpd', '1.4', 'Serveur lighttpd', 2, 1, '2008-06-01 14:04:58', '2008-06-01 14:04:58'),
(2, 'postfix', '2.2', '', 4, 1, '2008-06-01 14:09:07', '2008-06-01 14:09:07'),
(3, 'mysql', '5.0', '', 1, 1, '2008-06-01 14:09:25', '2008-06-01 14:09:25'),
(4, 'dovecot', '0.9', 'serveur pop imap', 6, 1, '2008-06-01 14:14:41', '2008-06-01 14:14:41');

INSERT INTO  `dbtype` (`name` ,`active` ,`created` ,`modified`)
VALUES ('mysql',  '1', NOW( ) , CURRENT_TIMESTAMP);

ALTER TABLE  `dbname`
ADD `server_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `accounts_id`,
ADD INDEX (  `server_id` ) ;

ALTER TABLE  `dbusers`
ADD `server_id` INT( 11 ) UNSIGNED NOT NULL AFTER  `accounts_id`,
ADD INDEX (  `server_id` ) ;

ALTER TABLE  `dbusers`
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE ;

ALTER TABLE  `dbname`
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE ;


CREATE VIEW zz_vw_webvhost AS
SELECT whost.id,CONCAT(whost.vhost,'.',domain.domain) as virtual FROM whost,domain WHERE whost.domain_id=domain.id;

ALTER TABLE `server`
CHANGE servername name varchar(50) NOT NULL;

ALTER TABLE `server`
ADD public_name varchar(255) NOT NULL AFTER name,
ADD private_name varchar(255) NOT NULL AFTER public_name;

ALTER TABLE `server` ADD UNIQUE (`public_name`);
ALTER TABLE `server` ADD INDEX ( `private_name` );

ALTER TABLE  `stats_mail_user`
ADD  `mail_in` INT( 10 ) NOT NULL ,
ADD  `mail_out` INT( 10 ) NOT NULL ;

UPDATE server_apps SET version = '2.2' WHERE id=2;

UPDATE server_apps SET version = '0.9' WHERE id=4;



ALTER TABLE  `domain_alias`
DROP PRIMARY KEY;

ALTER TABLE  `domain_alias`
ADD  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT,
ADD PRIMARY KEY(`id`);

ALTER TABLE  `domain_alias`
ADD INDEX (`dalias`);



ALTER TABLE `dbusers`
ADD `dalias_id` INT(11) UNSIGNED NOT NULL AFTER `server_id`;

ALTER TABLE `dbusers`
ADD INDEX (`dalias_id`);

ALTER TABLE `dbname`
ADD `dalias_id` INT(11) UNSIGNED NOT NULL AFTER `server_id`;

ALTER TABLE `dbname`
ADD INDEX (`dalias_id`);

ALTER TABLE `dbname`
ADD  `description` TINYTEXT NOT NULL ;

ALTER TABLE `dbusers`
ADD  `description` TINYTEXT NOT NULL ;


ALTER TABLE  `spamreport`
CHANGE `created`  `created` INT( 10 ) NOT NULL;

ALTER TABLE `filter`
ADD `id` INT( 11 ) NOT NULL AUTO_INCREMENT FIRST ,
ADD INDEX (`id`); 


ALTER TABLE  `mailbox` 
ADD  `pop3_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1',
ADD  `imap_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '1',
CHANGE  `smtpauth`  `smtp_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '0';

ALTER TABLE `domain`
ADD `pop3_enabled`  TINYINT( 1 ) NOT NULL DEFAULT  '1',
ADD `imap_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '0',
ADD `smtp_enabled` TINYINT( 1 ) NOT NULL DEFAULT  '0';


ALTER TABLE `dbusers`
ADD `server_ip_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `port` SMALLINT( 2 ) UNSIGNED NOT NULL;

ALTER TABLE `dbname`
ADD `server_ip_id` INT( 11 ) UNSIGNED NOT NULL,
ADD `port` SMALLINT( 2 ) UNSIGNED NOT NULL;


ALTER TABLE `server_job` DROP PRIMARY KEY,
ADD PRIMARY KEY ( `server_id` , `server_jobmodel_id` , `server_apps_id` , `server_ip_id` , `port` ) ;

ALTER TABLE `dbname` ADD INDEX ( `server_ip_id` );

ALTER TABLE `dbusers` ADD INDEX ( `server_ip_id` );

ALTER TABLE `server_ip`
ADD hostname varchar(255) NOT NULL AFTER private;

ALTER TABLE `server_ip` ADD INDEX (`hostname`);

ALTER TABLE `policy`
ADD  clean_quarantine_to      varchar(64) default NULL,
ADD  other_quarantine_to      varchar(64) default NULL,
ADD  spam_quarantine_cutoff_level float default NULL;

ALTER TABLE  `stats_mail_user`
ADD  `mailbox_size` INT( 10 ) NOT NULL ,
ADD  `mailbox_quota` VARCHAR( 10 ) NOT NULL ;

ALTER TABLE `stats_mail_user`
CHANGE `date` `date` VARCHAR(8) NOT NULL;

ALTER TABLE `ftpaccount`
ADD `whost_id` INT( 11 ) NOT NULL AFTER domain_id;

ALTER TABLE `domain`
ADD `whost_quota` INT( 11 ) NOT NULL DEFAULT '0' AFTER `db_quota` ;

# Convert an old table to InnoDB
ALTER TABLE  `apc` ENGINE = INNODB;

# Add APC type to avoid long checks that is brain damaded
ALTER TABLE `apc`
ADD `apttype` INT( 16 ) NOT NULL DEFAULT '1' AFTER `nbports` ;

CREATE TABLE IF NOT EXISTS `domain_dns_status` (
  `domain_id` int(11) unsigned NOT NULL,
  `status` int(1) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `comments`  tinytext NOT NULL,
  PRIMARY KEY  (`domain_id`,`status`,`created`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='# Table used by perl script that check dns records';

