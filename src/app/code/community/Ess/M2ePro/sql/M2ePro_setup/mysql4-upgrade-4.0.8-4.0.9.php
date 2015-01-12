<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `existance_check_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id_search_suggest_data`,
    ADD INDEX `existance_check_status` (`existance_check_status`);
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'existance_check_status') === false) {
    $installer->getConnection()->addColumn($tempTable, 'existance_check_status',
                                    'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id_search_suggest_data`');
}
if (!isset($tempTableIndexList[strtoupper('existance_check_status')])) {
    $installer->getConnection()->addKey($tempTable, 'existance_check_status', 'existance_check_status');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/video_tutorials/'
                                               AND   `key` = 'baseurl'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/video_tutorials/', 'baseurl', 'http://docs.m2epro.com/display/eBayAmazonMagentoV4/Video+Tutorials', NULL,
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_amazon_listing_product`
SET `existance_check_status` = 0;

SQL
);

//#############################################

$installer->endSetup();

//#############################################