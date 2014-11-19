<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
('/other/paypal/', 'url', 'paypal.com/cgi-bin/webscr/', 'PayPal url', '2011-01-12 02:55:16', '2011-01-12 02:55:16');

SQL
);

//#############################################

$installer->endSetup();

//#############################################