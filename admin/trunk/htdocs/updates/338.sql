# Remove garbage , from aliases
UPDATE alias
SET goto=TRIM(LEADING ',' FROM goto);

ALTER TABLE `msgs` ADD PRIMARY KEY(`mail_id`);

ALTER TABLE  `msgrcpt` ADD FOREIGN KEY (  `mail_id` ) REFERENCES  `dbopenvisp`.`msgs` (`mail_id`) ON DELETE CASCADE ;

