<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

$installer->run(<<<SQL

DROP TABLE IF EXISTS ess_config;
CREATE TABLE ess_config (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_account;
CREATE TABLE m2epro_account (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_attribute_set;
CREATE TABLE m2epro_attribute_set (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  object_id INT(11) UNSIGNED NOT NULL,
  object_type TINYINT(2) UNSIGNED NOT NULL,
  attribute_set_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_set_id (attribute_set_id),
  INDEX object_id (object_id),
  INDEX object_type (object_type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_config;
CREATE TABLE m2epro_config (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_account;
CREATE TABLE m2epro_ebay_account (
  account_id INT(11) UNSIGNED NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  token_session VARCHAR(255) NOT NULL,
  token_expired_date DATETIME NOT NULL,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_last_synchronization DATETIME DEFAULT NULL,
  feedbacks_receive TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  feedbacks_auto_response TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  feedbacks_auto_response_only_positive TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  feedbacks_last_used_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  ebay_store_title VARCHAR(255) NOT NULL,
  ebay_store_url TEXT NOT NULL,
  ebay_store_subscription_level VARCHAR(255) NOT NULL,
  ebay_store_description TEXT NOT NULL,
  ebay_info TEXT DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  messages_receive TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (account_id),
  INDEX ebay_store_title (ebay_store_title),
  INDEX feedbacks_auto_response (feedbacks_auto_response),
  INDEX feedbacks_auto_response_only_positive (feedbacks_auto_response_only_positive),
  INDEX feedbacks_last_used_id (feedbacks_last_used_id),
  INDEX feedbacks_receive (feedbacks_receive),
  INDEX messages_receive (messages_receive),
  INDEX mode (mode),
  INDEX orders_mode (orders_mode),
  INDEX other_listings_last_synchronization (other_listings_last_synchronization),
  INDEX other_listings_synchronization (other_listings_synchronization),
  INDEX token_expired_date (token_expired_date),
  INDEX token_session (token_session)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_account_store_category;
CREATE TABLE m2epro_ebay_account_store_category (
  account_id INT(11) UNSIGNED NOT NULL,
  category_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  parent_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  sorder INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (account_id, category_id),
  INDEX parent_id (parent_id),
  INDEX sorder (sorder),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_dictionary_category;
CREATE TABLE m2epro_ebay_dictionary_category (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  parent_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  level TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_leaf TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  features LONGTEXT DEFAULT NULL,
  item_specifics LONGTEXT DEFAULT NULL,
  attribute_set_id INT(11) UNSIGNED DEFAULT NULL,
  attribute_set LONGTEXT DEFAULT NULL,
  PRIMARY KEY (category_id, marketplace_id),
  INDEX attribute_set_id (attribute_set_id),
  INDEX is_leaf (is_leaf),
  INDEX level (level),
  INDEX parent_id (parent_id),
  INDEX title (title)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_dictionary_marketplace;
CREATE TABLE m2epro_ebay_dictionary_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  dispatch LONGTEXT NOT NULL,
  packages LONGTEXT NOT NULL,
  return_policy LONGTEXT NOT NULL,
  listing_features LONGTEXT NOT NULL,
  payments LONGTEXT NOT NULL,
  shipping_locations LONGTEXT NOT NULL,
  shipping_locations_exclude LONGTEXT NOT NULL,
  categories_features_defaults LONGTEXT NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_dictionary_shipping;
CREATE TABLE m2epro_ebay_dictionary_shipping (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  ebay_id VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(255) NOT NULL,
  is_flat TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_calculated TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_international TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `data` LONGTEXT NOT NULL,
  PRIMARY KEY (id),
  INDEX category (category),
  INDEX ebay_id (ebay_id),
  INDEX is_calculated (is_calculated),
  INDEX is_flat (is_flat),
  INDEX is_international (is_international),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_dictionary_shipping_category;
CREATE TABLE m2epro_ebay_dictionary_shipping_category (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  ebay_id VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX ebay_id (ebay_id),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_feedback;
CREATE TABLE m2epro_ebay_feedback (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  ebay_item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  ebay_item_title VARCHAR(255) NOT NULL,
  ebay_transaction_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  buyer_name VARCHAR(200) NOT NULL,
  buyer_feedback_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  buyer_feedback_text VARCHAR(255) NOT NULL,
  buyer_feedback_date DATETIME NOT NULL,
  buyer_feedback_type VARCHAR(20) NOT NULL,
  seller_feedback_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  seller_feedback_text VARCHAR(255) NOT NULL,
  seller_feedback_date DATETIME NOT NULL,
  seller_feedback_type VARCHAR(20) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX buyer_feedback_id (buyer_feedback_id),
  INDEX buyer_feedback_type (buyer_feedback_type),
  INDEX buyer_name (buyer_name),
  INDEX ebay_item_id (ebay_item_id),
  INDEX ebay_item_title (ebay_item_title),
  INDEX ebay_transaction_id (ebay_transaction_id),
  INDEX seller_feedback_id (seller_feedback_id),
  INDEX seller_feedback_type (seller_feedback_type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_feedback_template;
CREATE TABLE m2epro_ebay_feedback_template (
  id INT(11) NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_item;
CREATE TABLE m2epro_ebay_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX item_id (item_id),
  INDEX product_id (product_id),
  INDEX store_id (store_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing;
CREATE TABLE m2epro_ebay_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  products_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (listing_id),
  INDEX products_sold_count (products_sold_count)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_other;
CREATE TABLE m2epro_ebay_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  item_id DECIMAL(20, 0) UNSIGNED DEFAULT NULL,
  old_items TEXT DEFAULT NULL,
  currency VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED NOT NULL,
  online_qty INT(11) UNSIGNED NOT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  online_bids INT(11) UNSIGNED DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX currency (currency),
  INDEX end_date (end_date),
  INDEX item_id (item_id),
  INDEX online_bids (online_bids),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_product;
CREATE TABLE m2epro_ebay_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  ebay_item_id INT(11) UNSIGNED DEFAULT NULL,
  online_start_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_reserve_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_buyitnow_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  online_bids INT(11) UNSIGNED DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  additional_data TEXT DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX ebay_item_id (ebay_item_id),
  INDEX end_date (end_date),
  INDEX online_bids (online_bids),
  INDEX online_buyitnow_price (online_buyitnow_price),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX online_reserve_price (online_reserve_price),
  INDEX online_start_price (online_start_price),
  INDEX start_date (start_date)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_product_variation;
CREATE TABLE m2epro_ebay_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (listing_product_variation_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_product_variation_option;
CREATE TABLE m2epro_ebay_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_marketplace;
CREATE TABLE m2epro_ebay_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  categories_version INT(11) UNSIGNED NOT NULL DEFAULT 0,
  is_multivariation TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (marketplace_id),
  INDEX categories_version (categories_version),
  INDEX is_multivariation (is_multivariation)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_message;
CREATE TABLE m2epro_ebay_message (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  ebay_item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  ebay_item_title VARCHAR(255) NOT NULL,
  sender_name VARCHAR(255) NOT NULL,
  message_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  message_subject VARCHAR(255) NOT NULL,
  message_text TEXT NOT NULL,
  message_date DATETIME NOT NULL,
  message_type VARCHAR(20) NOT NULL,
  message_responses TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX ebay_item_id (ebay_item_id),
  INDEX ebay_item_title (ebay_item_title),
  INDEX message_type (message_type),
  INDEX sender_name (sender_name)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_order;
CREATE TABLE m2epro_ebay_order (
  order_id INT(11) UNSIGNED NOT NULL,
  ebay_order_id VARCHAR(255) NOT NULL,
  selling_manager_record_number INT(11) UNSIGNED DEFAULT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) NOT NULL,
  buyer_user_id VARCHAR(255) NOT NULL,
  checkout_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - Incomplete, 1 - Completed',
  checkout_buyer_message VARCHAR(500) DEFAULT NULL,
  payment_method VARCHAR(255) DEFAULT NULL,
  payment_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  payment_status_ebay VARCHAR(255) DEFAULT NULL,
  payment_status_hold VARCHAR(255) DEFAULT NULL,
  payment_date DATETIME DEFAULT NULL,
  shipping_method VARCHAR(255) DEFAULT NULL,
  shipping_method_selected TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  shipping_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_address TEXT NOT NULL,
  shipping_tracking_details VARCHAR(500) DEFAULT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_type VARCHAR(255) DEFAULT NULL,
  shipping_date DATETIME DEFAULT NULL,
  get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  paid_amount DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  saved_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  best_offer TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  final_fee DECIMAL(12, 4) UNSIGNED NOT NULL,
  tax_rate FLOAT NOT NULL DEFAULT 0,
  tax_state VARCHAR(255) DEFAULT NULL,
  tax_includes_shipping TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  tax_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  purchase_update_date DATETIME DEFAULT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX buyer_user_id (buyer_user_id),
  INDEX checkout_status (checkout_status),
  INDEX ebay_order_id (ebay_order_id),
  INDEX paid_amount (paid_amount),
  INDEX payment_status (payment_status),
  INDEX selling_manager_record_number (selling_manager_record_number),
  INDEX shipping_status (shipping_status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_order_external_transaction;
CREATE TABLE m2epro_ebay_order_external_transaction (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED NOT NULL,
  transaction_id VARCHAR(255) NOT NULL,
  fee FLOAT UNSIGNED NOT NULL DEFAULT 0,
  sum FLOAT UNSIGNED NOT NULL DEFAULT 0,
  is_refund TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  transaction_date DATETIME NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX order_id (order_id),
  INDEX transaction_id (transaction_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_order_item;
CREATE TABLE m2epro_ebay_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  transaction_id DECIMAL(20, 0) UNSIGNED NOT NULL DEFAULT 0,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(64) DEFAULT NULL,
  condition_display_name VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `buy_it_now_price` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  qty_purchased INT(11) UNSIGNED NOT NULL,
  variation TEXT DEFAULT NULL,
  auto_pay TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  listing_type VARCHAR(255) NOT NULL,
  PRIMARY KEY (order_item_id),
  INDEX item_id (item_id),
  INDEX sku (sku),
  INDEX title (title),
  INDEX transaction_id (transaction_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_description;
CREATE TABLE m2epro_ebay_template_description (
  template_description_id INT(11) UNSIGNED NOT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL,
  title_template VARCHAR(255) NOT NULL,
  subtitle_mode TINYINT(2) UNSIGNED NOT NULL,
  subtitle_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL,
  description_template LONGTEXT NOT NULL,
  cut_long_titles TINYINT(2) UNSIGNED NOT NULL,
  hit_counter VARCHAR(255) NOT NULL,
  editor_type TINYINT(2) UNSIGNED NOT NULL,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL,
  variation_configurable_images VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_description_id),
  INDEX cut_long_titles (cut_long_titles),
  INDEX description_mode (description_mode),
  INDEX editor_type (editor_type),
  INDEX gallery_images_mode (gallery_images_mode),
  INDEX hit_counter (hit_counter),
  INDEX image_main_attribute (image_main_attribute),
  INDEX image_main_mode (image_main_mode),
  INDEX subtitle_mode (subtitle_mode),
  INDEX subtitle_template (subtitle_template),
  INDEX title_mode (title_mode),
  INDEX title_template (title_template),
  INDEX variation_configurable_images (variation_configurable_images)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_general;
CREATE TABLE m2epro_ebay_template_general (
  template_general_id INT(11) UNSIGNED NOT NULL,
  categories_mode TINYINT(2) UNSIGNED NOT NULL,
  categories_main_id INT(11) UNSIGNED NOT NULL,
  categories_main_attribute VARCHAR(255) NOT NULL,
  categories_secondary_id INT(11) UNSIGNED NOT NULL,
  categories_secondary_attribute VARCHAR(255) NOT NULL,
  store_categories_main_mode TINYINT(2) UNSIGNED NOT NULL,
  store_categories_main_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_categories_main_attribute VARCHAR(255) NOT NULL,
  store_categories_secondary_mode TINYINT(2) UNSIGNED NOT NULL,
  store_categories_secondary_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_categories_secondary_attribute VARCHAR(255) NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL,
  variation_enabled TINYINT(2) UNSIGNED NOT NULL,
  variation_ignore TINYINT(2) UNSIGNED NOT NULL,
  condition_value VARCHAR(255) NOT NULL,
  condition_attribute VARCHAR(255) NOT NULL,
  product_details LONGTEXT NOT NULL,
  enhancement VARCHAR(255) NOT NULL,
  gallery_type TINYINT(2) UNSIGNED NOT NULL,
  country VARCHAR(255) NOT NULL,
  postal_code VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  use_ebay_tax_table TINYINT(2) UNSIGNED NOT NULL,
  use_ebay_local_shipping_rate_table TINYINT(2) UNSIGNED NOT NULL,
  vat_percent TINYINT(2) NOT NULL,
  get_it_fast TINYINT(2) NOT NULL,
  dispatch_time INT(11) NOT NULL,
  local_shipping_mode TINYINT(2) NOT NULL,
  local_shipping_discount_mode TINYINT(2) NOT NULL,
  international_shipping_mode TINYINT(2) NOT NULL,
  international_shipping_discount_mode TINYINT(2) NOT NULL,
  local_shipping_cash_on_delivery_cost_mode TINYINT(2) UNSIGNED NOT NULL,
  local_shipping_cash_on_delivery_cost_value VARCHAR(255) NOT NULL,
  local_shipping_cash_on_delivery_cost_attribute VARCHAR(255) NOT NULL,
  pay_pal_email_address VARCHAR(255) NOT NULL,
  pay_pal_immediate_payment TINYINT(2) UNSIGNED NOT NULL,
  refund_accepted VARCHAR(255) NOT NULL,
  refund_option VARCHAR(255) NOT NULL,
  refund_within VARCHAR(255) NOT NULL,
  refund_description TEXT NOT NULL,
  refund_shippingcost VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_general_id),
  INDEX categories_main_attribute (categories_main_attribute),
  INDEX categories_main_id (categories_main_id),
  INDEX categories_mode (categories_mode),
  INDEX categories_secondary_attribute (categories_secondary_attribute),
  INDEX categories_secondary_id (categories_secondary_id),
  INDEX condition_attribute (condition_attribute),
  INDEX condition_value (condition_value),
  INDEX dispatch_time (dispatch_time),
  INDEX gallery_type (gallery_type),
  INDEX get_it_fast (get_it_fast),
  INDEX international_shipping_discount_mode (international_shipping_discount_mode),
  INDEX international_shipping_mode (international_shipping_mode),
  INDEX local_shipping_discount_mode (local_shipping_discount_mode),
  INDEX local_shipping_mode (local_shipping_mode),
  INDEX sku_mode (sku_mode),
  INDEX store_categories_main_attribute (store_categories_main_attribute),
  INDEX store_categories_main_id (store_categories_main_id),
  INDEX store_categories_main_mode (store_categories_main_mode),
  INDEX store_categories_secondary_attribute (store_categories_secondary_attribute),
  INDEX store_categories_secondary_id (store_categories_secondary_id),
  INDEX store_categories_secondary_mode (store_categories_secondary_mode),
  INDEX use_ebay_local_shipping_rate_table (use_ebay_local_shipping_rate_table),
  INDEX use_ebay_tax_table (use_ebay_tax_table),
  INDEX variation_enabled (variation_enabled),
  INDEX variation_ignore (variation_ignore),
  INDEX vat_percent (vat_percent)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_general_calculated_shipping;
CREATE TABLE m2epro_ebay_template_general_calculated_shipping (
  template_general_id INT(11) UNSIGNED NOT NULL,
  measurement_system TINYINT(2) UNSIGNED NOT NULL,
  originating_postal_code VARCHAR(255) NOT NULL,
  package_size_mode TINYINT(2) UNSIGNED NOT NULL,
  package_size_ebay VARCHAR(500) NOT NULL,
  package_size_attribute VARCHAR(255) NOT NULL,
  dimension_mode TINYINT(2) UNSIGNED NOT NULL,
  dimension_width VARCHAR(500) NOT NULL,
  dimension_width_attribute VARCHAR(255) NOT NULL,
  dimension_height VARCHAR(500) NOT NULL,
  dimension_height_attribute VARCHAR(255) NOT NULL,
  dimension_depth VARCHAR(500) NOT NULL,
  dimension_depth_attribute VARCHAR(255) NOT NULL,
  weight_mode TINYINT(2) UNSIGNED NOT NULL,
  weight_minor VARCHAR(500) NOT NULL,
  weight_major VARCHAR(500) NOT NULL,
  weight_attribute VARCHAR(255) NOT NULL,
  local_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL,
  local_handling_cost_value VARCHAR(255) NOT NULL,
  local_handling_cost_attribute VARCHAR(255) NOT NULL,
  international_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL,
  international_handling_cost_value VARCHAR(255) NOT NULL,
  international_handling_cost_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_general_id),
  INDEX dimension_depth (dimension_depth(255)),
  INDEX dimension_depth_attribute (dimension_depth_attribute),
  INDEX dimension_height (dimension_height(255)),
  INDEX dimension_height_attribute (dimension_height_attribute),
  INDEX dimension_mode (dimension_mode),
  INDEX dimension_width (dimension_width(255)),
  INDEX dimension_width_attribute (dimension_width_attribute),
  INDEX international_handling_cost_attribute (international_handling_cost_attribute),
  INDEX international_handling_cost_mode (international_handling_cost_mode),
  INDEX international_handling_cost_value (international_handling_cost_value),
  INDEX local_handling_cost_attribute (local_handling_cost_attribute),
  INDEX local_handling_cost_mode (local_handling_cost_mode),
  INDEX local_handling_cost_value (local_handling_cost_value),
  INDEX measurement_system (measurement_system),
  INDEX originating_postal_code (originating_postal_code),
  INDEX package_size_attribute (package_size_attribute),
  INDEX package_size_ebay (package_size_ebay(255)),
  INDEX package_size_mode (package_size_mode),
  INDEX weight_attribute (weight_attribute),
  INDEX weight_major (weight_major(255)),
  INDEX weight_minor (weight_minor(255)),
  INDEX weight_mode (weight_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_general_payment;
CREATE TABLE m2epro_ebay_template_general_payment (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_general_id INT(11) UNSIGNED NOT NULL,
  payment_id VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX payment_id (payment_id),
  INDEX template_general_id (template_general_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_general_shipping;
CREATE TABLE m2epro_ebay_template_general_shipping (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_general_id INT(11) UNSIGNED NOT NULL,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  locations TEXT NOT NULL,
  cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  cost_value VARCHAR(255) NOT NULL,
  cost_additional_items VARCHAR(255) NOT NULL,
  shipping_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_value VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX cost_additional_items (cost_additional_items),
  INDEX cost_mode (cost_mode),
  INDEX cost_value (cost_value),
  INDEX priority (priority),
  INDEX shipping_type (shipping_type),
  INDEX shipping_value (shipping_value),
  INDEX template_general_id (template_general_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_general_specific;
CREATE TABLE m2epro_ebay_template_general_specific (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_general_id INT(11) UNSIGNED NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL,
  mode_relation_id INT(11) UNSIGNED NOT NULL COMMENT 'category_id, attribute_set_id',
  attribute_id VARCHAR(255) NOT NULL,
  attribute_title VARCHAR(255) NOT NULL,
  value_mode TINYINT(2) UNSIGNED NOT NULL,
  value_ebay_recommended LONGTEXT DEFAULT NULL,
  value_custom_value VARCHAR(255) DEFAULT NULL,
  value_custom_attribute VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_id (attribute_id),
  INDEX attribute_title (attribute_title),
  INDEX mode (mode),
  INDEX mode_relation_id (mode_relation_id),
  INDEX template_general_id (template_general_id),
  INDEX value_custom_attribute (value_custom_attribute),
  INDEX value_custom_value (value_custom_value),
  INDEX value_mode (value_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_selling_format;
CREATE TABLE m2epro_ebay_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  listing_type TINYINT(2) UNSIGNED NOT NULL,
  listing_type_attribute VARCHAR(255) NOT NULL,
  listing_is_private TINYINT(2) UNSIGNED NOT NULL,
  duration_mode TINYINT(4) UNSIGNED NOT NULL,
  duration_attribute VARCHAR(255) NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  currency VARCHAR(50) NOT NULL,
  price_variation_mode TINYINT(2) UNSIGNED NOT NULL,
  start_price_mode TINYINT(2) UNSIGNED NOT NULL,
  start_price_coefficient VARCHAR(255) NOT NULL,
  start_price_custom_attribute VARCHAR(255) NOT NULL,
  reserve_price_mode TINYINT(2) UNSIGNED NOT NULL,
  reserve_price_coefficient VARCHAR(255) NOT NULL,
  reserve_price_custom_attribute VARCHAR(255) NOT NULL,
  buyitnow_price_mode TINYINT(2) UNSIGNED NOT NULL,
  buyitnow_price_coefficient VARCHAR(255) NOT NULL,
  buyitnow_price_custom_attribute VARCHAR(255) NOT NULL,
  best_offer_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_accept_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_accept_value VARCHAR(255) NOT NULL,
  best_offer_accept_attribute VARCHAR(255) NOT NULL,
  best_offer_reject_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_reject_value VARCHAR(255) NOT NULL,
  best_offer_reject_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX best_offer_accept_attribute (best_offer_accept_attribute),
  INDEX best_offer_accept_mode (best_offer_accept_mode),
  INDEX best_offer_accept_value (best_offer_accept_value),
  INDEX best_offer_mode (best_offer_mode),
  INDEX best_offer_reject_attribute (best_offer_reject_attribute),
  INDEX best_offer_reject_mode (best_offer_reject_mode),
  INDEX best_offer_reject_value (best_offer_reject_value),
  INDEX buyitnow_price_coefficient (buyitnow_price_coefficient),
  INDEX buyitnow_price_custom_attribute (buyitnow_price_custom_attribute),
  INDEX buyitnow_price_mode (buyitnow_price_mode),
  INDEX currency (currency),
  INDEX duration_attribute (duration_attribute),
  INDEX duration_mode (duration_mode),
  INDEX listing_is_private (listing_is_private),
  INDEX listing_type (listing_type),
  INDEX listing_type_attribute (listing_type_attribute),
  INDEX price_variation_mode (price_variation_mode),
  INDEX qty_custom_attribute (qty_custom_attribute),
  INDEX qty_custom_value (qty_custom_value),
  INDEX qty_mode (qty_mode),
  INDEX reserve_price_coefficient (reserve_price_coefficient),
  INDEX reserve_price_custom_attribute (reserve_price_custom_attribute),
  INDEX reserve_price_mode (reserve_price_mode),
  INDEX start_price_coefficient (start_price_coefficient),
  INDEX start_price_custom_attribute (start_price_custom_attribute),
  INDEX start_price_mode (start_price_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_synchronization;
CREATE TABLE m2epro_ebay_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  start_auto_list TINYINT(2) UNSIGNED NOT NULL,
  end_auto_stop TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_list_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_send_data TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  relist_schedule_type TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_metric TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_value INT(11) UNSIGNED NOT NULL,
  relist_schedule_week VARCHAR(255) NOT NULL,
  relist_schedule_week_start_time TIME DEFAULT NULL,
  relist_schedule_week_end_time TIME DEFAULT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
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
  INDEX relist_send_data (relist_send_data),
  INDEX relist_shedule_type (relist_schedule_type),
  INDEX relist_shedule_week (relist_schedule_week),
  INDEX relist_status_enabled (relist_status_enabled),
  INDEX revise_update_description (revise_update_description),
  INDEX revise_update_price (revise_update_price),
  INDEX revise_update_qty (revise_update_qty),
  INDEX revise_update_sub_title (revise_update_sub_title),
  INDEX revise_update_title (revise_update_title),
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

DROP TABLE IF EXISTS m2epro_listing;
CREATE TABLE m2epro_listing (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_general_id INT(11) UNSIGNED NOT NULL,
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  template_description_id INT(11) UNSIGNED NOT NULL,
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  products_total_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_listed_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_inactive_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  synchronization_start_type TINYINT(2) UNSIGNED NOT NULL,
  synchronization_start_through_metric TINYINT(2) UNSIGNED NOT NULL,
  synchronization_start_through_value INT(11) UNSIGNED NOT NULL,
  synchronization_start_date DATETIME NOT NULL,
  synchronization_stop_type TINYINT(2) UNSIGNED NOT NULL,
  synchronization_stop_through_metric TINYINT(2) UNSIGNED NOT NULL,
  synchronization_stop_through_value INT(11) UNSIGNED NOT NULL,
  synchronization_stop_date DATETIME NOT NULL,
  synchronization_already_start TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  synchronization_already_stop TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  source_products TINYINT(2) UNSIGNED NOT NULL,
  categories_add_action TINYINT(2) UNSIGNED NOT NULL,
  categories_delete_action TINYINT(2) UNSIGNED NOT NULL,
  hide_products_others_listings TINYINT(2) UNSIGNED NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX categories_add_action (categories_add_action),
  INDEX categories_delete_action (categories_delete_action),
  INDEX component_mode (component_mode),
  INDEX hide_products_others_listings (hide_products_others_listings),
  INDEX source_products (source_products),
  INDEX store_id (store_id),
  INDEX synchronization_already_start (synchronization_already_start),
  INDEX synchronization_already_stop (synchronization_already_stop),
  INDEX synchronization_start_date (synchronization_start_date),
  INDEX synchronization_start_through_metric (synchronization_start_through_metric),
  INDEX synchronization_start_through_value (synchronization_start_through_value),
  INDEX synchronization_start_type (synchronization_start_type),
  INDEX synchronization_stop_date (synchronization_stop_date),
  INDEX synchronization_stop_through_metric (synchronization_stop_through_metric),
  INDEX synchronization_stop_type (synchronization_stop_type),
  INDEX template_description_id (template_description_id),
  INDEX template_general_id (template_general_id),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_synchronization_id (template_synchronization_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_category;
CREATE TABLE m2epro_listing_category (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  INDEX listing_id (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_log;
CREATE TABLE m2epro_listing_log (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  listing_title VARCHAR(255) NOT NULL,
  product_title VARCHAR(255) DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  creator VARCHAR(255) DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX action_id (action_id),
  INDEX component_mode (component_mode),
  INDEX creator (creator),
  INDEX initiator (initiator),
  INDEX listing_id (listing_id),
  INDEX listing_title (listing_title),
  INDEX priority (priority),
  INDEX product_id (product_id),
  INDEX product_title (product_title),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_other;
CREATE TABLE m2epro_listing_other (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX marketplace_id (marketplace_id),
  INDEX product_id (product_id),
  INDEX status (status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_other_log;
CREATE TABLE m2epro_listing_other_log (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_other_id INT(11) UNSIGNED DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  creator VARCHAR(255) DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX action_id (action_id),
  INDEX component_mode (component_mode),
  INDEX creator (creator),
  INDEX initiator (initiator),
  INDEX listing_other_id (listing_other_id),
  INDEX priority (priority),
  INDEX title (title),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_product;
CREATE TABLE m2epro_listing_product (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status_changer TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX listing_id (listing_id),
  INDEX product_id (product_id),
  INDEX status (status),
  INDEX status_changer (status_changer)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_product_variation;
CREATE TABLE m2epro_listing_product_variation (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_product_id INT(11) UNSIGNED NOT NULL,
  `add` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `delete` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `add` (`add`),
  INDEX component_mode (component_mode),
  INDEX `delete` (`delete`),
  INDEX listing_product_id (listing_product_id),
  INDEX status (status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_product_variation_option;
CREATE TABLE m2epro_listing_product_variation_option (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  product_type VARCHAR(255) NOT NULL,
  attribute VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute (attribute),
  INDEX component_mode (component_mode),
  INDEX listing_product_variation_id (listing_product_variation_id),
  INDEX `option` (`option`),
  INDEX product_id (product_id),
  INDEX product_type (product_type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_lock_item;
CREATE TABLE m2epro_lock_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  nick VARCHAR(255) NOT NULL,
  `data` TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX nick (nick)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_locked_object;
CREATE TABLE m2epro_locked_object (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  model_name VARCHAR(255) NOT NULL,
  object_id INT(11) UNSIGNED NOT NULL,
  related_hash VARCHAR(255) DEFAULT NULL,
  tag VARCHAR(255) DEFAULT NULL,
  description VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX model_name (model_name),
  INDEX object_id (object_id),
  INDEX related_hash (related_hash),
  INDEX tag (tag)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_marketplace;
CREATE TABLE m2epro_marketplace (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  native_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  code VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sorder INT(11) UNSIGNED NOT NULL DEFAULT 0,
  group_title VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX code (code),
  INDEX component_mode (component_mode),
  INDEX group_title (group_title),
  INDEX native_id (native_id),
  INDEX sorder (sorder),
  INDEX status (status),
  INDEX title (title),
  INDEX url (url)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order;
CREATE TABLE m2epro_order (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED DEFAULT NULL,
  magento_order_id INT(11) UNSIGNED DEFAULT NULL,
  store_id INT(11) UNSIGNED DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX magento_order_id (magento_order_id),
  INDEX marketplace_id (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order_item;
CREATE TABLE m2epro_order_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX order_id (order_id),
  INDEX product_id (product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order_log;
CREATE TABLE m2epro_order_log (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED DEFAULT NULL,
  message TEXT NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX order_id (order_id),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_product_change;
CREATE TABLE m2epro_product_change (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  attribute VARCHAR(255) DEFAULT NULL,
  value_old LONGTEXT DEFAULT NULL,
  value_new LONGTEXT DEFAULT NULL,
  creator_type TINYINT(2) UNSIGNED NOT NULL,
  count_changes INT(11) UNSIGNED DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX attribute (attribute),
  INDEX creator_type (creator_type),
  INDEX product_id (product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronization_log;
CREATE TABLE m2epro_synchronization_log (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  synchronization_run_id INT(11) UNSIGNED NOT NULL,
  synch_task TINYINT(2) UNSIGNED NOT NULL,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  creator VARCHAR(255) DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX creator (creator),
  INDEX initiator (initiator),
  INDEX priority (priority),
  INDEX synch_task (synch_task),
  INDEX synchronization_run_id (synchronization_run_id),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronization_run;
CREATE TABLE m2epro_synchronization_run (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  initiator TINYINT(2) UNSIGNED NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX initiator (initiator)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_template_description;
CREATE TABLE m2epro_template_description (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_template_general;
CREATE TABLE m2epro_template_general (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_template_selling_format;
CREATE TABLE m2epro_template_selling_format (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_template_synchronization;
CREATE TABLE m2epro_template_synchronization (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  revise_change_general_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_description_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_selling_format_template TINYINT(2) UNSIGNED NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX revise_change_description_template (revise_change_description_template),
  INDEX revise_change_general_template (revise_change_general_template),
  INDEX revise_change_selling_format_template (revise_change_selling_format_template),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO ess_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/', 'application_key', 'b79a495170da3b081c9ebae6c255c7fbe1b139b5',
    NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/M2ePro/license/', 'key', NULL, 'License Key', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/M2ePro/license/', 'domain', NULL, 'Valid domain', '2012-05-25 07:52:30', '2012-05-21 10:47:49'),
  ('/M2ePro/license/', 'ip', NULL, 'Valid ip', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/', 'directory', NULL, 'Valid directory', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/amazon/', 'mode', '0',
   '0 - None\r\n1 - Trial\r\n2 - Live', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/amazon/', 'expiration_date', NULL, NULL, '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/amazon/', 'status', '0',
   '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/ebay/', 'mode', '0',
   '0 - None\r\n1 - Trial\r\n2 - Live', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/ebay/', 'expiration_date', NULL, NULL, '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/license/ebay/', 'status', '0',
   '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
  ('/M2ePro/server/', 'lock', '0', '0 - No\r\n1 - Yes', '2012-05-25 07:52:32', '2012-05-21 10:47:49'),
  ('/M2ePro/server/', 'messages', '[]', 'Server messages', '2012-05-25 07:52:32', '2012-05-21 10:47:49'),
  ('/M2ePro/server/', 'directory', '/server/',
   'Server scripts directory', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/modules/', 'M2ePro', '0.0.0.r0', NULL, '2012-05-25 07:52:27', '2012-05-21 10:47:49'),
  ('/server/', 'baseurl', 'https://m2epro.com/',
   'Support server base url', '2012-05-21 10:47:49', '2012-05-21 10:47:49');

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/autocomplete/', 'max_records_quantity',
   '100', 'Max records in the sugessions list', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/backups/', 'domain', NULL, NULL, '2012-05-25 07:52:27', '2012-05-21 10:47:49'),
  ('/backups/', 'ip', NULL, NULL, '2012-05-25 07:52:27', '2012-05-21 10:47:49'),
  ('/backups/', 'directory', NULL, NULL, '2012-05-25 07:52:27', '2012-05-21 10:47:49'),
  ('/backups/', 'date_last_check', NULL, NULL, '2012-05-25 07:52:27', '2012-05-21 10:47:49'),
  ('/block_notices/settings/', 'show',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/component/', 'default', 'ebay', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/component/amazon/', 'mode', '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/component/ebay/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/', 'last_access', NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/notification/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/notification/', 'inactive_hours',
   '12', 'Allowed number of hours cron could be inactive', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/synchronization/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/synchronization/', 'last_access',
   NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/synchronization/', 'interval', '300', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/logs_cleaning/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/logs_cleaning/', 'last_access',
   NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/cron/task/logs_cleaning/', 'interval', '86400', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/debug/exceptions/', 'send_to_server',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/debug/fatal_error/', 'send_to_server',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/documentation/', 'baseurl',
   'http://docs.m2epro.com/display/m2eproV32/M2E+Pro', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'USD', 'US Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'CAD', 'Canadian Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'GBP', 'British Pound', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'AUD', 'Australian Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'EUR', 'Euro', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'CHF', 'Swiss Franc', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'CNY', 'Chinese Renminbi', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'HKD', 'Hong Kong Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'PHP', 'Philippines Peso', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'PLN', 'Polish Zloty', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'SEK', 'Sweden Krona', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'SGD', 'Singapore Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'TWD', 'Taiwanese Dollar', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'INR', 'Indian Rupees', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/currency/', 'MYR', 'Malaysian Ringgit', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/defaults/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/defaults/update_listings_products/', 'since_time',
   NULL, NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/defaults/update_listings_products/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/feedbacks/', 'mode',
   '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/feedbacks/receive/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/feedbacks/response/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/marketplaces/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/marketplaces/categories/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/marketplaces/details/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/messages/', 'mode', '0',
   '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/messages/receive/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/orders/', 'mode', '1',
   '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/other_listings/', 'mode', '1',
   '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/end/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/relist/', 'mode', '1',
   '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/revise/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/start/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/synchronization/settings/templates/stop/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/feedbacks/notification/', 'mode',
   '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/feedbacks/notification/', 'last_check',
   NULL, 'Date last check new buyers feedbacks', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/listings/lockItem/', 'max_deactivate_time',
   '900', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/listings/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/listings/', 'days',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/listings/', 'default',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/other_listings/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/other_listings/', 'days',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/other_listings/', 'default',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/synchronizations/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/synchronizations/', 'days',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/logs/cleaning/synchronizations/', 'default',
   '30', 'in days', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/messages/notification/', 'mode',
   '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/messages/notification/', 'last_check',
   NULL, 'Time of last check for new eBay messages', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/products/settings/', 'show_thumbnails',
   '1', 'Visibility thumbnails into grid', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/form/', 'defect_mail',
   'support@m2epro.com', 'Defect email address', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/form/', 'feature_mail',
   'support@m2epro.com', 'Feature email address', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/form/', 'inquiry_mail',
   'support@m2epro.com', 'Inquiry email address', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/uservoice/', 'mode',
  '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/uservoice/', 'baseurl',
   'http://magento2ebay.uservoice.com/api/v1/', 'UserVoice api baseurl', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/support/uservoice/', 'client_key',
   'WEsfO8nFh3FXffUU1Oa7A', 'UserVoice api client key', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/cron/distribution/', 'max_execution_time',
   '300', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/lockItem/', 'max_deactivate_time',
   '900', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/memory/', 'max_size',
   '512', 'in Mb', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/memory/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/profiler/', 'mode',
   '1', '1 - production, \r\n2 - debugging, \r\n3 - developing', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/profiler/', 'delete_resources',
   '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/profiler/', 'print_type',
   '2', '1 - var_dump(), \r\n2 - print + <br/>, \r\n3 - print + EOL', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/defaults/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/defaults/inspector/', 'mode',
   '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/defaults/inspector/', 'last_listing_product_id',
   NULL, NULL, '2012-06-01 11:48:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/defaults/inspector/', 'min_interval_between_circles',
   '3600', 'in seconds', '2012-06-01 11:48:49', '2012-06-01 11:48:49'),
  ('/synchronization/settings/defaults/inspector/', 'max_count_times_for_full_circle',
   '50', NULL, '2012-06-01 11:48:49', '2012-06-01 11:48:49'),
  ('/synchronization/settings/defaults/inspector/', 'min_count_items_per_one_time',
   '100', NULL, '2012-06-01 11:48:49', '2012-06-01 11:48:49'),
  ('/synchronization/settings/defaults/inspector/', 'max_count_items_per_one_time',
   '1000', NULL, '2012-06-01 11:48:49', '2012-06-01 11:48:49'),
  ('/synchronization/settings/defaults/inspector/', 'last_time_start_circle',
   NULL, NULL, '2012-06-01 11:48:49', '2012-06-01 11:48:49'),
  ('/synchronization/settings/feedbacks/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/marketplaces/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/messages/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/orders/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/other_listings/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/synchronization/settings/templates/', 'mode',
   '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/wizard/', 'status',
   '0', '0 - None, 99 - Skip, 100 - Complete', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/other/paypal/', 'url',
   'paypal.com/cgi-bin/webscr/', NULL, '2011-01-12 02:55:16', '2011-01-12 02:55:16');

INSERT INTO m2epro_marketplace VALUES
  (1, 0, 'United States', 'US', 'ebay.com', 0, 3,
   'America', 'ebay', '2012-05-14 10:52:02', '2011-05-05 06:55:43'),
  (2, 2, 'Canada', 'Canada', 'ebay.ca', 0, 1,
   'America', 'ebay', '2012-05-17 09:26:35', '2011-05-05 06:55:43'),
  (3, 3, 'United Kingdom', 'UK', 'ebay.co.uk', 0, 16,
   'Europe', 'ebay', '2012-05-17 09:26:35', '2011-05-05 06:55:43'),
  (4, 15, 'Australia', 'Australia', 'ebay.com.au', 0, 17,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (5, 16, 'Austria', 'Austria', 'ebay.at', 0, 4,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (6, 23, 'Belgium (French)', 'Belgium_French', 'befr.ebay.be', 0, 6,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (7, 71, 'France', 'France', 'ebay.fr', 0, 7,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (8, 77, 'Germany', 'Germany', 'ebay.de', 0, 8,
   'Europe', 'ebay', '2012-05-14 10:52:02', '2011-05-05 06:55:43'),
  (9, 100, 'eBay Motors', 'eBayMotors', 'motors.ebay.com', 0, 23,
   'Other', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (10, 101, 'Italy', 'Italy', 'ebay.it', 0, 10,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (11, 123, 'Belgium (Dutch)', 'Belgium_Dutch', 'benl.ebay.be', 0, 5,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (12, 146, 'Netherlands', 'Netherlands', 'ebay.nl', 0, 11,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (13, 186, 'Spain', 'Spain', 'ebay.es', 0, 15,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (14, 193, 'Switzerland', 'Switzerland', 'ebay.ch', 0, 13,
   'Europe', 'ebay', '2012-05-17 09:26:36', '2011-05-05 06:55:43'),
  (15, 201, 'Hong Kong', 'HongKong', 'ebay.com.hk', 0, 18,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:43'),
  (16, 203, 'India', 'India', 'ebay.in', 0, 19,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:43'),
  (17, 205, 'Ireland', 'Ireland', 'ebay.ie', 0, 9,
   'Europe', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (18, 207, 'Malaysia', 'Malaysia', 'ebay.com.my', 0, 20,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (19, 210, 'Canada (French)', 'CanadaFrench', 'cafr.ebay.ca', 0, 2,
   'America', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (20, 211, 'Philippines', 'Philippines', 'ebay.ph', 0, 21,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (21, 212, 'Poland', 'Poland', 'ebay.pl', 0, 12,
   'Europe', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (22, 216, 'Singapore', 'Singapore', 'ebay.com.sg', 0, 22,
   'Asia / Pacific', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44'),
  (23, 218, 'Sweden', 'Sweden', 'ebay.se', 0, 14,
   'Europe', 'ebay', '2012-05-17 09:26:37', '2011-05-05 06:55:44');

INSERT INTO m2epro_ebay_marketplace VALUES
  (1, 0, 1),
  (2, 0, 1),
  (3, 0, 1),
  (4, 0, 1),
  (5, 0, 1),
  (6, 0, 0),
  (7, 0, 1),
  (8, 0, 1),
  (9, 0, 1),
  (10, 0, 1),
  (11, 0, 0),
  (12, 0, 0),
  (13, 0, 0),
  (14, 0, 1),
  (15, 0, 0),
  (16, 0, 1),
  (17, 0, 1),
  (18, 0, 1),
  (19, 0, 0),
  (20, 0, 1),
  (21, 0, 0),
  (22, 0, 0),
  (23, 0, 0);

SQL
);

//#############################################

Mage::register('M2ePro_IS_INSTALLATION',true);
$installer->endSetup();

//#############################################