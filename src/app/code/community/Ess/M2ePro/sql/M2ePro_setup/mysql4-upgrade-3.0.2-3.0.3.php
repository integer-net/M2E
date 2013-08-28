<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

DELETE FROM `ess_config`
WHERE `group` = '/M2ePro/docs/' OR `group` LIKE '/M2ePro/support/%';

INSERT INTO `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
  ('/documentation/', 'baseurl', 'http://docs.m2epro.com/', 'Documentation baseurl',
   '2011-04-20 15:20:53', '2011-04-20 15:20:53'),
  ('/support/', 'defect_mail', 'support@m2epro.com', 'Defect email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/support/', 'feature_mail', 'support@m2epro.com', 'Feature email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/support/', 'inquiry_mail', 'support@m2epro.com', 'Inquiry email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/support/uservoice/', 'mode', '1', '0 - disable, \r\n1 - enable', '2011-02-24 09:52:27', '2011-02-24 09:52:27'),
  ('/support/uservoice/', 'baseurl', 'http://magento2ebay.uservoice.com/api/v1/', 'UserVoice api baseurl',
   '2011-02-24 09:52:27', '2011-02-24 09:52:27'),
  ('/support/uservoice/', 'client_key', 'WEsfO8nFh3FXffUU1Oa7A', 'UserVoice api client key',
   '2011-02-24 09:52:27', '2011-02-24 09:52:27');

SQL
);

//#############################################

$installer->endSetup();

//#############################################