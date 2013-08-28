<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_order`
MODIFY `account_id` INT(11) UNSIGNED NOT NULL;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_ebay_account`
    ADD PRIMARY KEY (`account_id`),
    DROP KEY `server_hash`,
    DROP KEY `orders_last_synchronization`,
    DROP KEY `ebay_store_subscription_level`,
    DROP KEY `ebay_store_title`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_account');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if (!isset($tempTableIndexList[strtoupper('primary')])) {
    $installer->getConnection()->addKey($tempTable, 'PRIMARY', 'account_id', 'primary');
}

$tempTableListToDrop = array(
    'server_hash',
    'orders_last_synchronization',
    'ebay_store_subscription_level',
    'ebay_store_title'
);
foreach ($tempTableListToDrop as $indexName) {
    if (isset($tempTableIndexList[strtoupper($indexName)])) {
        $installer->getConnection()->dropKey($tempTable, $indexName);
    }
}

//#############################################

$installer->endSetup();

//#############################################