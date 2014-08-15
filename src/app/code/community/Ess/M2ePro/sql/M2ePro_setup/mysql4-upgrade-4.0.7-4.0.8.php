<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_processing_request`
    ADD COLUMN `component` VARCHAR(255) NOT NULL AFTER `id`,
    ADD INDEX `component` (`component`);

    ALTER TABLE `m2epro_ebay_template_general`
    ADD COLUMN `condition_mode` TINYINT(2) UNSIGNED NOT NULL AFTER `variation_ignore`,
    ADD COLUMN `international_trade` TEXT DEFAULT NULL AFTER `refund_shippingcost`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'component') === false) {
    $installer->getConnection()->addColumn($tempTable, 'component', 'VARCHAR(255) NOT NULL AFTER `id`');
}
if (!isset($tempTableIndexList[strtoupper('component')])) {
    $installer->getConnection()->addKey($tempTable, 'component', 'component');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_general');

if ($installer->getConnection()->tableColumnExists($tempTable, 'condition_mode') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'condition_mode',
                                           'TINYINT(2) UNSIGNED NOT NULL AFTER `variation_ignore`');
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'international_trade') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'international_trade',
                                           'TEXT DEFAULT NULL AFTER `refund_shippingcost`');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/component/ebay/'
                                               AND   `key` = 'allowed'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/component/amazon/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/component/ebay/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_ebay_template_general`
SET `condition_mode` = `categories_mode`;

UPDATE `m2epro_processing_request`
SET `component` = 'amazon';

SQL
);

//#############################################

$tempTable = $installer->getTable('m2epro_xfabric_request');
$installer->getConnection()->query("DROP TABLE IF EXISTS `{$tempTable}`");

$tempTable = $installer->getTable('m2epro_xfabric_schema');
$installer->getConnection()->query("DROP TABLE IF EXISTS `{$tempTable}`");

$installer->run(<<<SQL

DELETE FROM `ess_config`
WHERE `group` LIKE '%/xfabric/%';

DELETE FROM `m2epro_config`
WHERE `group` LIKE '%/xfabric/%';

SQL
);

//#############################################

$installer->endSetup();

//#############################################