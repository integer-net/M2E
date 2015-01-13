<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

UPDATE `m2epro_config`
SET `value` = '0'
WHERE `group` = '/amazon/synchronization/settings/marketplaces/'
AND   `key` = 'mode';

SQL
);

//#############################################

$installer->endSetup();

//#############################################