<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_account`
    ADD COLUMN `other_listings_move_settings` VARCHAR(255) DEFAULT NULL AFTER `other_listings_move_mode`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_account');

if ($installer->getConnection()->tableColumnExists($tempTable, 'other_listings_move_settings') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'other_listings_move_settings',
                                           'VARCHAR(255) DEFAULT NULL AFTER `other_listings_move_mode`');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = 'http://docs.m2epro.com/display/eBayAmazonMagentoV4/M2E+Pro'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

SQL
);

//#############################################

$installer->endSetup();

//#############################################