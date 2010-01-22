ALTER TABLE `alias`
ADD FOREIGN KEY ( `policy_id` ) REFERENCES `policy` (`id`) ON DELETE CASCADE;

# Alias Record that are orphan with policy.id
# select alias.* from alias left join policy on (alias.policy_id=policy.id) where alias.address is not null and policy.id is null;


ALTER TABLE `dbname`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `dbtype_id` ) REFERENCES `dbtype` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_ip_id` ) REFERENCES  `server_ip` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE;

ALTER TABLE `dbusers`
ADD FOREIGN KEY ( `accounts_id` ) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `dbtype_id` ) REFERENCES `dbtype` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_ip_id` ) REFERENCES  `server_ip` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY ( `server_id` ) REFERENCES `server` (`id`) ON DELETE CASCADE;


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


ALTER TABLE  `log`
ADD FOREIGN KEY (  `accounts_id` ) REFERENCES  `accounts` (`id`) ON DELETE CASCADE,
ADD FOREIGN KEY (  `domain_id` ) REFERENCES  `domain` (`id`) ON DELETE CASCADE;


