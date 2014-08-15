<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_products_changes`
WHERE `deferred` = 1;

ALTER TABLE `m2epro_products_changes`
DROP COLUMN `deferred`,
DROP COLUMN `analizing_date`;

ALTER TABLE `m2epro_synchronizations_templates`
MODIFY `relist_schedule_week_start_time` TIME DEFAULT NULL,
ADD COLUMN `relist_mode` TINYINT(2) UNSIGNED NOT NULL after `revise_change_listing_template`,
ADD COLUMN `relist_schedule_week_end_time` TIME DEFAULT NULL after `relist_schedule_week_start_time`,
ADD INDEX `relist_mode` (`relist_mode`),
ADD INDEX `relist_schedule_week_end_time` (`relist_schedule_week_end_time`);

UPDATE `m2epro_synchronizations_templates`
SET `relist_mode` = 1
WHERE `relist_status_enabled` = 1
OR    `relist_is_in_stock` = 1
OR    `relist_qty` != 0;

UPDATE `m2epro_synchronizations_templates`
SET `relist_schedule_week_start_time` = NULL,
    `relist_schedule_week_end_time` = NULL
WHERE `relist_schedule_week_start_time` = ''
OR    `relist_schedule_week_start_time` = '00:00:00';

UPDATE `m2epro_synchronizations_templates`
SET `relist_schedule_week_end_time` = ADDTIME(`relist_schedule_week_start_time`,'01:00:00')
WHERE `relist_schedule_week_start_time` IS NOT NULL;

SQL
);

//#############################################

$installer->endSetup();

//#############################################