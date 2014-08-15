<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_listings_templates`
MODIFY `enhancement` VARCHAR(255) NOT NULL;

UPDATE `m2epro_descriptions_templates`
SET `gallery_images_mode` = 11
WHERE `gallery_images_mode` = 12;

SQL
);

//#############################################

$installer->endSetup();

//#############################################