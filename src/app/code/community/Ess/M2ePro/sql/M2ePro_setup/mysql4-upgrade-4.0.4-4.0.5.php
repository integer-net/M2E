<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_processing_request`
    ADD COLUMN `processing_hash` VARCHAR(255) NOT NULL AFTER `hash`,
    ADD INDEX processing_hash (processing_hash);
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_processing_request');
$tempTableIndexList = $installer->getConnection()->getIndexList($tempTable);

if ($installer->getConnection()->tableColumnExists($tempTable, 'processing_hash') === false) {
    $installer->getConnection()->addColumn($tempTable, 'processing_hash', 'VARCHAR(255) NOT NULL AFTER `hash`');
}
if (!isset($tempTableIndexList[strtoupper('processing_hash')])) {
    $installer->getConnection()->addKey($tempTable, 'processing_hash', 'processing_hash');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/amazon/synchronization/settings/other_listings/'
                                               AND   `key` = 'interval'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/synchronization/settings/other_listings/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/other_listings/', 'last_time', NULL, 'Last check time',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//#############################################

$lockedObjectTable = $installer->getTable('m2epro_locked_object');
$processingRequestTable = $installer->getTable('m2epro_processing_request');

$processingRequests = $installer->getConnection()
                                ->query("SELECT `hash` FROM `{$processingRequestTable}`")
                                ->fetchAll();

$tempProcessedHashes = array();
foreach ($processingRequests as $processingRequest) {

    if (in_array($processingRequest['hash'],$tempProcessedHashes)) {
        continue;
    }

    $hash = sha1(rand(1,1000000).microtime(true));
    $processingHash = $processingRequest['hash'];

    $installer->run(<<<SQL

        UPDATE `m2epro_locked_object`
        SET `related_hash` = '{$hash}'
        WHERE `related_hash` = '{$processingHash}';

        UPDATE `m2epro_processing_request`
        SET `hash` = '{$hash}',
            `processing_hash` = '{$processingHash}'
        WHERE `hash` = '{$processingHash}';

SQL
);

    $tempProcessedHashes[] = $processingHash;
}

//#############################################

$installer->endSetup();

//#############################################