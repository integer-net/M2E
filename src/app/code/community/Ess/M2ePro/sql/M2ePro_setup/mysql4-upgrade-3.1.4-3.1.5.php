<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

ALTER TABLE `m2epro_ebay_orders`
ADD COLUMN `final_value_fee` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0 after `best_offer_sale`;

ALTER TABLE `m2epro_listings_templates`
ADD COLUMN `local_shipping_cash_on_delivery_cost_mode` TINYINT(2) UNSIGNED NOT NULL
    after `international_shipping_discount_mode`,
ADD COLUMN `local_shipping_cash_on_delivery_cost_value` VARCHAR(255) NOT NULL
    after `local_shipping_cash_on_delivery_cost_mode`,
ADD COLUMN `local_shipping_cash_on_delivery_cost_attribute` VARCHAR(255) NOT NULL
    after `local_shipping_cash_on_delivery_cost_value`;

INSERT INTO `m2epro_config` (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
('/autocomplete/', 'max_records_quantity', '100', NULL, '2012-04-05 18:19:49', '2012-04-05 18:19:49'),
('/cron/distribution/', 'max_execution_time', '300', 'in seconds', '2012-04-05 18:19:49', '2012-04-05 18:19:49');

ALTER TABLE `m2epro_synchronizations_templates`
ADD COLUMN `relist_list_mode` TINYINT(2) UNSIGNED NOT NULL after `relist_filter_user_lock`;

SQL
);

//#############################################

$installer->endSetup();

//#############################################