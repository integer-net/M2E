<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_amazon_category_specific (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT(11) UNSIGNED NOT NULL,
  xpath VARCHAR(255) NOT NULL,
  mode VARCHAR(25) NOT NULL,
  custom_value VARCHAR(255) DEFAULT NULL,
  custom_attribute VARCHAR(255) DEFAULT NULL,
  attributes TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_amazon_listing_product`
    ADD COLUMN `general_id_search_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id`,
    ADD COLUMN `general_id_search_suggest_data` TEXT DEFAULT NULL AFTER `general_id_search_status`,
    ADD INDEX `general_id_search_status` (`general_id_search_status`);
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_listing_product');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'general_id_search_status') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'general_id_search_status',
                                           'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `general_id`');
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'general_id_search_suggest_data') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'general_id_search_suggest_data',
                                           'TEXT DEFAULT NULL AFTER `general_id_search_status`');
}
if (!isset($tempTableIndexList[strtoupper('general_id_search_status')])) {
    $installer->getConnection()->addKey($tempTable, 'general_id_search_status', 'general_id_search_status');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/synchronization/settings/defaults/deleted_products/'
                                               AND   `key` = 'mode'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/synchronization/settings/defaults/deleted_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/synchronization/settings/defaults/deleted_products/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/synchronization/settings/defaults/deleted_products/', 'last_time', NULL, 'Last check time',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->endSetup();

//#############################################