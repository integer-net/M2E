<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_accounts`
ADD COLUMN `orders_combined_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 after `orders_status_payment_complete_mode`,
ADD INDEX `orders_combined_mode` (`orders_combined_mode`);

INSERT INTO `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
('/synchronization/settings/templates/inspector/', 'interval', '3600', 'Interval to run',
 '2011-07-21 09:51:07', '2011-03-01 10:21:28');

UPDATE `m2epro_config`
SET `value` = 1
WHERE `group` = '/feedbacks/notification/'
AND `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/M2EPro/M2E+Pro/'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

SQL
);

//#############################################

$installer->endSetup();

//#############################################