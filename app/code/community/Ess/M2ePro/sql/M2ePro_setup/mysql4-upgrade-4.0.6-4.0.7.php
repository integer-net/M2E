<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_processing_request`
    ADD COLUMN `perform_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `perform_type` (`perform_type`);

    ALTER TABLE `m2epro_ebay_template_description`
    ADD COLUMN `watermark_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `variation_configurable_images`,
    ADD COLUMN `watermark_image` LONGBLOB DEFAULT NULL AFTER `watermark_mode`,
    ADD COLUMN `watermark_settings` TEXT DEFAULT NULL AFTER `watermark_image`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'perform_type') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'perform_type',
                                           'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`');
}
if (!isset($tempTableIndexList[strtoupper('perform_type')])) {
    $installer->getConnection()->addKey($tempTable, 'perform_type', 'perform_type');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_description');

if ($installer->getConnection()->tableColumnExists($tempTable, 'watermark_mode') === false) {
    $installer->getConnection()->addColumn(
        $tempTable,
        'watermark_mode',
        'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `variation_configurable_images`'
    );
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'watermark_image') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'watermark_image',
                                           'LONGBLOB DEFAULT NULL AFTER `watermark_mode`');
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'watermark_settings') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'watermark_settings',
                                           'TEXT DEFAULT NULL AFTER `watermark_image`');
}

//#############################################

$installer->endSetup();

//#############################################