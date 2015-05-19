<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_order_item`
MODIFY `order_id` INT(11) UNSIGNED NOT NULL;

ALTER TABLE `m2epro_ebay_order_item`
MODIFY `transaction_id` VARCHAR(20) NOT NULL;

ALTER TABLE `m2epro_ebay_feedback`
MODIFY `ebay_transaction_id` VARCHAR(20) NOT NULL;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_order_item`
    ADD COLUMN `update_date` DATETIME DEFAULT NULL AFTER `component_mode`,
    ADD COLUMN `create_date` DATETIME DEFAULT NULL AFTER `update_date`,
    ADD INDEX `product_id` (`product_id`);
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_order_item');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'update_date') === false) {
    $installer->getConnection()->addColumn($tempTable, 'update_date', 'DATETIME DEFAULT NULL AFTER `component_mode`');
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'create_date') === false) {
    $installer->getConnection()->addColumn($tempTable, 'create_date', 'DATETIME DEFAULT NULL AFTER `update_date`');
}
if (!isset($tempTableIndexList[strtoupper('product_id')])) {
    $installer->getConnection()->addKey($tempTable, 'product_id', 'product_id');
}

//#############################################

$installer->endSetup();

//#############################################