# Remove garbage , from aliases
UPDATE alias
SET goto=TRIM(LEADING ',' FROM goto);

ALTER TABLE `msgs` ADD PRIMARY KEY(`mail_id`);

DROP TABLE stats_mailbox;

ALTER TABLE `ftpaccount` ADD INDEX ( `whost_id` );

ALTER TABLE `ftpaccount` CHANGE `whost_id` `whost_id` INT( 11 ) UNSIGNED NOT NULL;

ALTER TABLE `whost_alias` DROP PRIMARY KEY ,
ADD PRIMARY KEY (  `id` );

ALTER TABLE `whost_alias` ENGINE = INNODB;

ALTER TABLE `server_job` ADD  `instance` VARCHAR( 50 ) NOT NULL AFTER  `password`;

ALTER TABLE `alias` CHANGE `domain_id` `domain_id` INT( 11 ) UNSIGNED NOT NULL;

ALTER TABLE `quota` CHANGE `emails` `mailboxes` INT( 11 ) NOT NULL;
ALTER TABLE `quota` CHANGE `emails_alias` `aliases` INT( 11 ) NOT NULL;

