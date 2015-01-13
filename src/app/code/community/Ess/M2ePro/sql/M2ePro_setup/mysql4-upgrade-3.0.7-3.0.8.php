<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_products_changes`;

ALTER TABLE `m2epro_products_changes`
ADD COLUMN `creator_type` TINYINT(2) UNSIGNED NOT NULL after `value_new`,
ADD INDEX `creator_type` (`creator_type`),
DROP INDEX `count_changes`;

INSERT INTO `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
('/synchronization/settings/templates/inspector/', 'mode', '0', '0 - disable, \r\n1 - enable',
 '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
('/synchronization/settings/templates/inspector/', 'last_time', NULL, 'Last check time',
 '2011-05-11 09:10:33', '2011-04-27 09:37:01');

ALTER TABLE `m2epro_listings_templates`
MODIFY `vat_percent` FLOAT NOT NULL DEFAULT 0;

SQL
);

//#############################################

$installer->endSetup();

//#############################################