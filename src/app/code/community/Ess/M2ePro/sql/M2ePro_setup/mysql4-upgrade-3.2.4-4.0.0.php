<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

CREATE TABLE IF NOT EXISTS m2epro_amazon_account (
  account_id INT(11) UNSIGNED NOT NULL,
  marketplaces_data TEXT DEFAULT NULL,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  PRIMARY KEY (account_id),
  INDEX other_listings_mapping_mode (other_listings_mapping_mode),
  INDEX other_listings_move_mode (other_listings_move_mode),
  INDEX other_listings_synchronization (other_listings_synchronization)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_category (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  node_title VARCHAR(255) NOT NULL,
  category_path VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_dictionary_category (
  id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  parent_id INT(11) UNSIGNED DEFAULT NULL,
  node_hash VARCHAR(255) NOT NULL,
  xsd_hash VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  item_types TEXT DEFAULT NULL,
  browsenode_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  is_listable TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sorder INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX browsenode_id (browsenode_id),
  INDEX is_listable (is_listable),
  INDEX marketplace_id (marketplace_id),
  INDEX node_hash (node_hash),
  INDEX path (path),
  INDEX sorder (sorder),
  INDEX title (title),
  INDEX xsd_hash (xsd_hash)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_dictionary_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  nodes LONGTEXT NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_dictionary_specific (
  id INT(11) UNSIGNED NOT NULL,
  parent_id INT(11) UNSIGNED DEFAULT NULL,
  xsd_hash VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  xml_tag VARCHAR(255) NOT NULL,
  xpath VARCHAR(255) NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `values` TEXT DEFAULT NULL,
  params TEXT DEFAULT NULL,
  min_occurs TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  max_occurs TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  INDEX max_occurs (max_occurs),
  INDEX min_occurs (min_occurs),
  INDEX parent_id (parent_id),
  INDEX title (title),
  INDEX type (type),
  INDEX xml_tag (xml_tag),
  INDEX xpath (xpath),
  INDEX xsd_hash (xsd_hash)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX marketplace_id (marketplace_id),
  INDEX product_id (product_id),
  INDEX sku (sku),
  INDEX store_id (store_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  item_id VARCHAR(255) NOT NULL,
  general_id VARCHAR(255) NOT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  is_afn_channel TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_isbn_general_id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX is_afn_channel (is_afn_channel),
  INDEX is_isbn_general_id (is_isbn_general_id),
  INDEX item_id (item_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED DEFAULT NULL,
  item_id VARCHAR(255) DEFAULT NULL,
  general_id VARCHAR(255) DEFAULT NULL,
  worldwide_id VARCHAR(255) DEFAULT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  is_afn_channel TINYINT(2) UNSIGNED DEFAULT NULL,
  is_isbn_general_id TINYINT(2) UNSIGNED DEFAULT NULL,
  is_upc_worldwide_id TINYINT(2) UNSIGNED DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX is_afn_channel (is_afn_channel),
  INDEX is_isbn_general_id (is_isbn_general_id),
  INDEX is_upc_worldwide_id (is_upc_worldwide_id),
  INDEX item_id (item_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX worldwide_id (worldwide_id),
  INDEX category_id (category_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (listing_product_variation_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  developer_key VARCHAR(255) DEFAULT NULL,
  default_currency VARCHAR(255) NOT NULL,
  PRIMARY KEY (marketplace_id),
  INDEX default_currency (default_currency),
  INDEX developer_key (developer_key)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_order (
  order_id INT(11) UNSIGNED NOT NULL,
  amazon_order_id VARCHAR(255) NOT NULL,
  is_afn_channel TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) DEFAULT NULL,
  shipping_service VARCHAR(255) DEFAULT NULL,
  shipping_address TEXT NOT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL,
  paid_amount DECIMAL(12, 4) UNSIGNED NOT NULL,
  tax_amount DECIMAL(12, 4) UNSIGNED NOT NULL,
  discount_amount DECIMAL(12, 4) UNSIGNED NOT NULL,
  qty_shipped INT(11) UNSIGNED NOT NULL DEFAULT 0,
  qty_unshipped INT(11) UNSIGNED NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL,
  purchase_update_date DATETIME DEFAULT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX amazon_order_id (amazon_order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX paid_amount (paid_amount)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  amazon_order_item_id VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  general_id VARCHAR(255) DEFAULT NULL,
  is_isbn_general_id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  price DECIMAL(12, 4) UNSIGNED NOT NULL,
  tax_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  discount_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  qty_purchased INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (order_item_id),
  INDEX general_id (general_id),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_template_description (
  template_description_id INT(11) UNSIGNED NOT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  brand_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  brand_template VARCHAR(255) NOT NULL,
  manufacturer_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_template VARCHAR(255) NOT NULL,
  bullet_points_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  bullet_points TEXT NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL,
  editor_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (template_description_id),
  INDEX brand_mode (brand_mode),
  INDEX bullet_points_mode (bullet_points_mode),
  INDEX description_mode (description_mode),
  INDEX editor_type (editor_type),
  INDEX gallery_images_mode (gallery_images_mode),
  INDEX image_main_attribute (image_main_attribute),
  INDEX image_main_mode (image_main_mode),
  INDEX manufacturer_mode (manufacturer_mode),
  INDEX title_mode (title_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_template_general (
  template_general_id INT(11) UNSIGNED NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  general_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  worldwide_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  worldwide_id_custom_attribute VARCHAR(255) NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value VARCHAR(2000) NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_general_id),
  INDEX condition_mode (condition_mode),
  INDEX condition_note_mode (condition_note_mode),
  INDEX general_id_mode (general_id_mode),
  INDEX sku_mode (sku_mode),
  INDEX worldwide_id_mode (worldwide_id_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_coefficient VARCHAR(255) NOT NULL,
  currency VARCHAR(50) NOT NULL,
  price_mode TINYINT(2) UNSIGNED NOT NULL,
  price_custom_attribute VARCHAR(255) NOT NULL,
  price_coefficient VARCHAR(255) NOT NULL,
  sale_price_mode TINYINT(2) UNSIGNED NOT NULL,
  sale_price_custom_attribute VARCHAR(255) NOT NULL,
  sale_price_coefficient VARCHAR(255) NOT NULL,
  sale_price_start_date DATETIME NOT NULL,
  sale_price_end_date DATETIME NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX currency (currency),
  INDEX price_coefficient (price_coefficient),
  INDEX price_custom_attribute (price_custom_attribute),
  INDEX price_mode (price_mode),
  INDEX qty_coefficient (qty_coefficient),
  INDEX qty_custom_attribute (qty_custom_attribute),
  INDEX qty_custom_value (qty_custom_value),
  INDEX qty_mode (price_mode),
  INDEX sale_price_coefficient (sale_price_coefficient),
  INDEX sale_price_custom_attribute (sale_price_custom_attribute),
  INDEX sale_price_end_date (sale_price_end_date),
  INDEX sale_price_mode (sale_price_mode),
  INDEX sale_price_start_date (sale_price_start_date)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_amazon_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  start_auto_list TINYINT(2) UNSIGNED NOT NULL,
  end_auto_stop TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_list_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_type TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_metric TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_value INT(11) UNSIGNED NOT NULL,
  relist_schedule_week VARCHAR(255) NOT NULL,
  relist_schedule_week_start_time TIME DEFAULT NULL,
  relist_schedule_week_end_time TIME DEFAULT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id),
  INDEX end_auto_stop (end_auto_stop),
  INDEX relist_filter_user_lock (relist_filter_user_lock),
  INDEX relist_is_in_stock (relist_is_in_stock),
  INDEX relist_list_mode (relist_list_mode),
  INDEX relist_mode (relist_mode),
  INDEX relist_qty (relist_qty),
  INDEX relist_qty_value (relist_qty_value),
  INDEX relist_qty_value_max (relist_qty_value_max),
  INDEX relist_schedule_through_metric (relist_schedule_through_metric),
  INDEX relist_schedule_through_value (relist_schedule_through_value),
  INDEX relist_schedule_week_end_time (relist_schedule_week_end_time),
  INDEX relist_schedule_week_start_time (relist_schedule_week_start_time),
  INDEX relist_shedule_type (relist_schedule_type),
  INDEX relist_shedule_week (relist_schedule_week),
  INDEX relist_status_enabled (relist_status_enabled),
  INDEX revise_update_price (revise_update_price),
  INDEX revise_update_qty (revise_update_qty),
  INDEX start_auto_list (start_auto_list),
  INDEX stop_out_off_stock (stop_out_off_stock),
  INDEX stop_qty (stop_qty),
  INDEX stop_qty_value (stop_qty_value),
  INDEX stop_qty_value_max (stop_qty_value_max),
  INDEX stop_status_disabled (stop_status_disabled)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_processing_request (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `hash` VARCHAR(255) NOT NULL,
  request_body LONGTEXT NOT NULL,
  responser_model VARCHAR(255) NOT NULL,
  responser_params LONGTEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `hash` (`hash`),
  INDEX responser_model (responser_model)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

//#############################################

/*
    ALTER TABLE `m2epro_ebay_feedback`
    ADD COLUMN `last_response_attempt_date` DATETIME DEFAULT NULL AFTER `seller_feedback_type`;

    ALTER TABLE `m2epro_ebay_dictionary_marketplace`
    ADD COLUMN `tax_categories` LONGTEXT NOT NULL AFTER `categories_features_defaults`;

    ALTER TABLE `m2epro_ebay_template_general`
    ADD COLUMN `tax_category` VARCHAR(255) NOT NULL AFTER `store_categories_secondary_attribute`,
    ADD COLUMN `tax_category_attribute` VARCHAR(255) NOT NULL AFTER `tax_category`;
*/

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_feedback');

if ($installer->getConnection()->tableColumnExists($tempTable, 'last_response_attempt_date') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'last_response_attempt_date',
                                           'DATETIME DEFAULT NULL AFTER `seller_feedback_type`');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_dictionary_marketplace');

if ($installer->getConnection()->tableColumnExists($tempTable, 'tax_categories') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'tax_categories',
                                           'LONGTEXT NOT NULL AFTER `categories_features_defaults`');
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_ebay_template_general');

if ($installer->getConnection()->tableColumnExists($tempTable, 'tax_category') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'tax_category',
                                           'VARCHAR(255) NOT NULL AFTER `store_categories_secondary_attribute`');
}
if ($installer->getConnection()->tableColumnExists($tempTable, 'tax_category_attribute') === false) {
    $installer->getConnection()->addColumn($tempTable,
                                           'tax_category_attribute',
                                           'VARCHAR(255) NOT NULL AFTER `tax_category`');
}

//#############################################

$tempTable = $installer->getTable('m2epro_config');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `group` = '/amazon/'
                                               AND   `key` = 'application_name'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/', 'application_name', 'M2ePro - Amazon Magento Integration', NULL,
 '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/amazon/connector/', 'mode', 'server', 'server', '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/amazon/currency/', 'USD', 'US Dollar', NULL, '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/amazon/currency/', 'GBP', 'British Pound', NULL, '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/amazon/currency/', 'EUR', 'Euro', NULL, '2012-04-12 14:21:48', '2012-04-12 14:21:48'),
('/amazon/synchronization/settings/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/defaults/update_listings_products/', 'max_deactivate_time', '21600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/defaults/update_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/marketplaces/details/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/marketplaces/specifics/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/orders/', 'max_deactivate_time', '21600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/other_listings/', 'max_deactivate_time', '21600', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/end/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/start/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/amazon/synchronization/settings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/license/validation/domain/notification/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/license/validation/ip/notification/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/license/validation/directory/notification/', 'mode', '1', '0 - disable, \r\n1 - enable',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/wizard/upgrade/', 'status', '0', '0 - None, 99 - Skip, 100 - Complete',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/error/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/error/', 'max_inactive_hours', '1', 'Allowed number of hours cron could be inactive',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/processing/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/processing/', 'interval', '120', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/cron/task/processing/', 'last_access', NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
('/ebay/synchronization/settings/feedbacks/response/', 'interval', '86400', 'in seconds',
 '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_marketplace');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `url` = 'amazon.com'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_marketplace VALUES
(24, 4, 'Canada', 'CA', 'amazon.ca', 0, 4, 'America', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(25, 3, 'Germany', 'DE', 'amazon.de', 0, 3, 'Europe', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(26, 5, 'France', 'FR', 'amazon.fr', 0, 5, 'Europe', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(27, 6, 'Japan', 'JP', 'amazon.jp', 0, 6, 'Asia / Pacific', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(28, 2, 'United Kingdom', 'UK', 'amazon.co.uk', 0, 2, 'Europe', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(29, 1, 'United States', 'US', 'amazon.com', 0, 1, 'America', 'amazon', '2011-11-07 11:49:26', '2011-05-05 06:55:44'),
(30, 7, 'Spain', 'ES', 'amazon.es', 0, 7, 'Europe', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(31, 8, 'Italy', 'IT', 'amazon.it', 0, 8, 'Europe', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44'),
(32, 9, 'China', 'CN', 'amazon.cn', 0, 9, 'Asia / Pacific', 'amazon', '2012-05-17 09:26:38', '2011-05-05 06:55:44');

SQL
);
}

//--------------------------------------------

$tempTable = $installer->getTable('m2epro_amazon_marketplace');
$tempRow = $installer->getConnection()->query("SELECT * FROM `{$tempTable}`
                                               WHERE `default_currency` = 'USD'")
                                      ->fetch();

if ($tempRow === false) {

    $installer->run(<<<SQL

INSERT INTO m2epro_amazon_marketplace VALUES
(24, '8636-1433-4377', 'USD'),
(25, '7078-7205-1944', 'EUR'),
(26, '7078-7205-1944', 'EUR'),
(27, NULL, ''),
(28, '7078-7205-1944', 'GBP'),
(29, '8636-1433-4377', 'USD'),
(30, '7078-7205-1944', 'EUR'),
(31, '7078-7205-1944', 'EUR'),
(32, NULL, '');

SQL
);
}

//#############################################

$installer->run(<<<SQL

UPDATE `ess_config`
SET `group` = '/M2ePro/server/'
WHERE `group` = '/M2ePro/'
AND   `key` = 'application_key';

UPDATE `m2epro_config`
SET `value` = '1'
WHERE `group` = '/component/amazon/'
AND   `key` = 'mode';

UPDATE `m2epro_config`
SET `value` = '500'
WHERE `group` = '/synchronization/settings/defaults/inspector/'
AND   `key` = 'max_count_items_per_one_time';

UPDATE `m2epro_config`
SET `key` = 'max_inactive_hours'
WHERE `group` = '/cron/notification/'
AND   `key` = 'inactive_hours';

UPDATE `m2epro_ebay_template_general`
SET `sku_mode` = 1
WHERE `variation_enabled` = 1
AND   `variation_ignore` = 0;

SQL
);

//#############################################

$installer->run(<<<SQL

DELETE FROM `m2epro_config`
WHERE `group` = '/cache/license/status/';

SQL
);

//#############################################

$installer->endSetup();

//#############################################