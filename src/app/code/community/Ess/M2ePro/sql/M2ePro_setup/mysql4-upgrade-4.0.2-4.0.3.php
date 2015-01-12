<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE m2epro_synchronization_run ENGINE = MYISAM;
ALTER TABLE m2epro_synchronization_log ENGINE = MYISAM;
ALTER TABLE m2epro_product_change ENGINE = MYISAM;
ALTER TABLE m2epro_processing_request ENGINE = MYISAM;
ALTER TABLE m2epro_lock_item ENGINE = MYISAM;
ALTER TABLE m2epro_locked_object ENGINE = MYISAM;
ALTER TABLE m2epro_listing_log ENGINE = MYISAM;
ALTER TABLE m2epro_listing_other_log ENGINE = MYISAM;

ALTER TABLE `m2epro_ebay_template_general`
MODIFY `vat_percent` FLOAT NOT NULL DEFAULT 0;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_amazon_category`
    ADD COLUMN `xsd_hash` VARCHAR(255) NOT NULL AFTER `marketplace_id`;

    ALTER TABLE `m2epro_amazon_category_specific`
    ADD COLUMN `type` VARCHAR(25) NULL DEFAULT NULL AFTER `custom_attribute`;

    ALTER TABLE `m2epro_amazon_template_general`
    ADD COLUMN `handling_time` INT(11) UNSIGNED NOT NULL DEFAULT 1 AFTER `condition_note_custom_attribute`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category');

if ($installer->getConnection()->tableColumnExists($tempTable, 'xsd_hash') === false) {
    $installer->getConnection()->addColumn($tempTable, 'xsd_hash', 'VARCHAR(255) NOT NULL AFTER `marketplace_id`');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_category_specific');

if ($installer->getConnection()->tableColumnExists($tempTable, 'type') === false) {
    $installer->getConnection()->addColumn($tempTable, 'type', 'VARCHAR(25) DEFAULT NULL AFTER `custom_attribute`');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_template_general');

if ($installer->getConnection()->tableColumnExists($tempTable, 'handling_time') === false) {
    $installer->getConnection()->addColumn(
        $tempTable,
        'handling_time',
        'INT(11) UNSIGNED NOT NULL DEFAULT 1 AFTER `condition_note_custom_attribute`');
}

//#############################################

$tempTable = $installer->getTable('ess_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/M2ePro/license/ebay/'
                                               AND   `key` = 'is_free'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO `ess_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/M2ePro/license/amazon/', 'is_free', '0', '0 - No\r\n1 - Yes', '2012-08-17 10:41:17', '2012-05-21 10:47:49'),
('/M2ePro/license/ebay/', 'is_free', '0', '0 - No\r\n1 - Yes', '2012-08-17 10:41:17', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_template_synchronization`
SET `revise_change_description_template` = '0'
WHERE `component_mode` = 'amazon';

SQL
);

//#############################################

$currentDateTime = date('c');

$accountTable = $installer->getTable('m2epro_account');
$marketplaceTable = $installer->getTable('m2epro_marketplace');
$amazonAccountTable = $installer->getTable('m2epro_amazon_account');
$amazonMarketplaceTable = $installer->getTable('m2epro_amazon_marketplace');

$amazonAccountsOld = $installer->getConnection()
                               ->query("SELECT * FROM `{$amazonAccountTable}`")
                               ->fetchAll();

foreach ($amazonAccountsOld as $amazonAccountOld) {

    if (empty($amazonAccountOld['marketplaces_data'])) {
        continue;
    }

    $marketplaces = json_decode($amazonAccountOld['marketplaces_data'],true);

    if (!is_array($marketplaces) || count($marketplaces) <= 0) {
        continue;
    }

    $oldAccountId = (int)$amazonAccountOld['account_id'];

    $tempAccountRow = $installer->getConnection()
                                    ->query("SELECT * FROM `{$accountTable}` WHERE `id` = ".$oldAccountId)
                                    ->fetch();

    foreach ($marketplaces as $marketplaceId => $marketplaceData) {

        $marketplaceId = (int)$marketplaceId;
        unset($marketplaceData['marketplace_id']);

        $tempMarketplaceRow = $installer->getConnection()
                                        ->query('SELECT * FROM `'
                                                .$marketplaceTable
                                                .'` WHERE `id` = '
                                                .(int)$marketplaceId)
                                        ->fetch();

        $newAccountTitle = $tempAccountRow['title'].' ('.$tempMarketplaceRow['title'].')';
        $newAccountTitle = $installer->getConnection()->quote($newAccountTitle);

        $installer->run(<<<SQL

        INSERT INTO `m2epro_account` (`title`,`component_mode`,`update_date`,`create_date`)
        VALUES ({$newAccountTitle},'amazon','{$currentDateTime}','{$currentDateTime}');
SQL
);
        $newAccountId = (int)$installer->getConnection()
                                       ->query("SELECT MAX(`id`) FROM `{$accountTable}`")
                                       ->fetchColumn();

        $newMarketplacesData = json_encode(array($marketplaceId=>$marketplaceData));

        $other_listings_mapping_settings = $amazonAccountOld['other_listings_mapping_settings'];
        if (is_null($other_listings_mapping_settings)) {
            $other_listings_mapping_settings = 'NULL';
        } else {
            $other_listings_mapping_settings = '\''.$other_listings_mapping_settings.'\'';
        }

        $orders_last_synchronization = $amazonAccountOld['orders_last_synchronization'];
        if (is_null($orders_last_synchronization)) {
            $orders_last_synchronization = 'NULL';
        } else {
            $orders_last_synchronization = '\''.$orders_last_synchronization.'\'';
        }

        $installer->run(<<<SQL

        INSERT INTO `m2epro_amazon_account` (`account_id`,
                                             `marketplaces_data`,
                                             `other_listings_synchronization`,
                                             `other_listings_mapping_mode`,
                                             `other_listings_mapping_settings`,
                                             `other_listings_move_mode`,
                                             `orders_mode`,
                                             `orders_last_synchronization`,
                                             `magento_orders_settings`)
        VALUES ({$newAccountId},
                '{$newMarketplacesData}',
                {$amazonAccountOld['other_listings_synchronization']},
                {$amazonAccountOld['other_listings_mapping_mode']},
                {$other_listings_mapping_settings},
                {$amazonAccountOld['other_listings_move_mode']},
                {$amazonAccountOld['orders_mode']},
                {$orders_last_synchronization},
                '{$amazonAccountOld['magento_orders_settings']}');

        UPDATE `m2epro_listing_other`
        SET `account_id` = {$newAccountId}
        WHERE `account_id` = {$oldAccountId}
        AND   `marketplace_id` = {$marketplaceId};

        UPDATE `m2epro_order`
        SET `account_id` = {$newAccountId}
        WHERE `account_id` = {$oldAccountId}
        AND   `marketplace_id` = {$marketplaceId};

        UPDATE `m2epro_template_general`
        SET `account_id` = {$newAccountId}
        WHERE `account_id` = {$oldAccountId}
        AND   `marketplace_id` = {$marketplaceId};

        UPDATE `m2epro_amazon_item`
        SET `account_id` = {$newAccountId}
        WHERE `account_id` = {$oldAccountId}
        AND   `marketplace_id` = {$marketplaceId};

SQL
);
    }

    $installer->run(<<<SQL

        DELETE FROM `m2epro_account`
        WHERE `id` = {$oldAccountId};

        DELETE FROM `m2epro_amazon_account`
        WHERE `account_id` = {$oldAccountId};

SQL
);

}

//#############################################

$installer->endSetup();

//#############################################