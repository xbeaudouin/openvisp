
ALTER TABLE alias ADD domain_id int(11) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `alias` ADD INDEX (  `domain_id` )

ALTER TABLE alias ADD FOREIGN KEY ( `domain_id` ) REFERENCES `domain` (`id`) ON DELETE CASCADE;

CREATE VIEW TMP_ALIAS AS
SELECT domain.id as domain_id, policy.id as policy_id, alias.address, alias.domain_id as alias_domain_id
FROM alias, domain, policy
WHERE domain.id = policy.domain_id
AND policy.id = alias.policy_id;

UPDATE TMP_ALIAS
SET alias_domain_id = domain_id;

DROP VIEW TMP_ALIAS;

ALTER TABLE  `alias`
ADD FOREIGN KEY (`domain_id`) REFERENCES  `domain` (`id`) ON DELETE CASCADE ;
