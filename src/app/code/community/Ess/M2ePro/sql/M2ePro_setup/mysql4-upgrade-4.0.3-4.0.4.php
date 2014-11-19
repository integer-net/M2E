<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

/*
    ALTER TABLE `m2epro_synchronization_run`
    ADD COLUMN `kill_now` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_synchronization_run');

if ($installer->getConnection()->tableColumnExists($tempTable, 'kill_now') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'kill_now',
                                           'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER `end_date`');
}

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_marketplace`
SET `url` = 'amazon.co.jp'
WHERE `url` = 'amazon.jp'
AND   `component_mode` = 'amazon';

SQL
);

//#############################################

$installer->endSetup();

//#############################################