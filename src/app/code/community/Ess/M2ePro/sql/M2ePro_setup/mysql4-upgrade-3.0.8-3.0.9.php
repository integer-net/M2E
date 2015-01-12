<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_accounts_store_categories`
MODIFY `category_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
MODIFY `parent_id` DECIMAL(20, 0) UNSIGNED NOT NULL;

ALTER TABLE `m2epro_listings_templates`
MODIFY `store_categories_main_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
MODIFY `store_categories_secondary_id` DECIMAL(20, 0) UNSIGNED NOT NULL;

ALTER TABLE `m2epro_ebay_orders`
ADD COLUMN `checkout_message` VARCHAR(500) DEFAULT NULL after `checkout_status`;

SQL
);

//#############################################

$installer->endSetup();

//#############################################