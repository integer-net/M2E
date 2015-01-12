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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_accounts;
CREATE TABLE m2epro_accounts (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  token_session VARCHAR(255) NOT NULL,
  token_expired_date DATETIME NOT NULL,
  ebay_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  ebay_listings_last_synchronization DATETIME DEFAULT NULL,
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
  orders_listings_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_listings_store_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_listings_store_id INT(11) UNSIGNED NOT NULL,
  orders_ebay_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_ebay_create_product TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_ebay_store_id INT(11) UNSIGNED NOT NULL,
  orders_customer_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_customer_exist_id INT(11) UNSIGNED DEFAULT NULL,
  orders_customer_new_website INT(11) UNSIGNED DEFAULT NULL,
  orders_customer_new_group INT(11) UNSIGNED DEFAULT NULL,
  orders_customer_new_subscribe_news TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_customer_new_send_notifications VARCHAR(255) DEFAULT NULL,
  orders_status_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_status_checkout_incomplete TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  orders_status_checkout_completed VARCHAR(255) DEFAULT NULL,
  orders_status_payment_completed VARCHAR(255) DEFAULT NULL,
  orders_status_shipping_completed VARCHAR(255) DEFAULT NULL,
  orders_status_invoice TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_status_shipping TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  messages_receive TINYINT(2) NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX ebay_listings_last_synchronization (ebay_listings_last_synchronization),
  INDEX ebay_listings_synchronization (ebay_listings_synchronization),
  INDEX ebay_store_subscription_level (ebay_store_subscription_level),
  INDEX ebay_store_title (ebay_store_title),
  INDEX feedbacks_auto_response (feedbacks_auto_response),
  INDEX feedbacks_auto_response_only_positive (feedbacks_auto_response_only_positive),
  INDEX feedbacks_last_used_id (feedbacks_last_used_id),
  INDEX feedbacks_receive (feedbacks_receive),
  INDEX messages_receive (messages_receive),
  INDEX mode (mode),
  INDEX orders_customer_exist_id (orders_customer_exist_id),
  INDEX orders_customer_mode (orders_customer_mode),
  INDEX orders_customer_new_group (orders_customer_new_group),
  INDEX orders_customer_new_send_notifications (orders_customer_new_send_notifications),
  INDEX orders_customer_new_subscribe_news (orders_customer_new_subscribe_news),
  INDEX orders_customer_new_website (orders_customer_new_website),
  INDEX orders_ebay_create_product (orders_ebay_create_product),
  INDEX orders_ebay_mode (orders_ebay_mode),
  INDEX orders_ebay_store_id (orders_ebay_store_id),
  INDEX orders_listings_mode (orders_listings_mode),
  INDEX orders_listings_store_id (orders_listings_store_id),
  INDEX orders_listings_store_mode (orders_listings_store_mode),
  INDEX orders_mode (orders_mode),
  INDEX orders_status_checkout_completed (orders_status_checkout_completed),
  INDEX orders_status_checkout_incomplete (orders_status_checkout_incomplete),
  INDEX orders_status_invoice (orders_status_invoice),
  INDEX orders_status_mode (orders_status_mode),
  INDEX orders_status_payment_completed (orders_status_payment_completed),
  INDEX orders_status_shipping (orders_status_shipping),
  INDEX orders_status_shipping_completed (orders_status_shipping_completed),
  INDEX server_hash (server_hash),
  INDEX title (title),
  INDEX token_expired_date (token_expired_date),
  INDEX token_session (token_session)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_accounts_store_categories;
CREATE TABLE m2epro_accounts_store_categories (
  account_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  parent_id INT(11) UNSIGNED NOT NULL,
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_descriptions_templates;
CREATE TABLE m2epro_descriptions_templates (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
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
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX cut_long_titles (cut_long_titles),
  INDEX description_mode (description_mode),
  INDEX editor_type (editor_type),
  INDEX gallery_images_mode (gallery_images_mode),
  INDEX hit_counter (hit_counter),
  INDEX image_main_attribute (image_main_attribute),
  INDEX image_main_mode (image_main_mode),
  INDEX subtitle_mode (subtitle_mode),
  INDEX subtitle_template (subtitle_template),
  INDEX title (title),
  INDEX title_mode (title_mode),
  INDEX title_template (title_template),
  INDEX variation_configurable_images (variation_configurable_images)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_dictionary_categories;
CREATE TABLE m2epro_dictionary_categories (
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

DROP TABLE IF EXISTS m2epro_dictionary_marketplaces;
CREATE TABLE m2epro_dictionary_marketplaces (
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

DROP TABLE IF EXISTS m2epro_dictionary_shippings;
CREATE TABLE m2epro_dictionary_shippings (
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_dictionary_shippings_categories;
CREATE TABLE m2epro_dictionary_shippings_categories (
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_items;
CREATE TABLE m2epro_ebay_items (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  item_id DECIMAL(20,0) UNSIGNED NOT NULL,
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listings;
CREATE TABLE m2epro_ebay_listings (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED DEFAULT NULL,
  ebay_item DECIMAL(20,0) UNSIGNED DEFAULT NULL,
  ebay_old_items TEXT DEFAULT NULL,
  ebay_price DECIMAL(12, 4) UNSIGNED NOT NULL,
  ebay_currency VARCHAR(255) DEFAULT NULL,
  ebay_title VARCHAR(255) DEFAULT NULL,
  ebay_qty INT(11) UNSIGNED NOT NULL,
  ebay_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  ebay_bids INT(11) UNSIGNED DEFAULT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  ebay_start_date DATETIME DEFAULT NULL,
  ebay_end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX ebay_bids (ebay_bids),
  INDEX ebay_currency (ebay_currency),
  INDEX ebay_item (ebay_item),
  INDEX ebay_price (ebay_price),
  INDEX ebay_qty (ebay_qty),
  INDEX ebay_qty_sold (ebay_qty_sold),
  INDEX ebay_title (ebay_title),
  INDEX marketplace_id (marketplace_id),
  INDEX status (status)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listings_logs;
CREATE TABLE m2epro_ebay_listings_logs (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  ebay_listing_id INT(11) UNSIGNED DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  creator VARCHAR(255) DEFAULT NULL,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX action_id (action_id),
  INDEX creator (creator),
  INDEX ebay_listing_id (ebay_listing_id),
  INDEX initiator (initiator),
  INDEX priority (priority),
  INDEX title (title),
  INDEX type (type)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_orders;
CREATE TABLE m2epro_ebay_orders (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  account_mode TINYINT(2) UNSIGNED NOT NULL COMMENT '0 - sandbox, 1 - production',
  marketplace_id INT(11) UNSIGNED DEFAULT NULL,
  magento_order_id INT(11) UNSIGNED DEFAULT NULL,
  amount_paid FLOAT UNSIGNED DEFAULT 0,
  amount_saved FLOAT UNSIGNED DEFAULT 0,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) NOT NULL,
  buyer_userid VARCHAR(255) NOT NULL,
  sales_tax_percent FLOAT NOT NULL DEFAULT 0,
  sales_tax_state VARCHAR(255) DEFAULT NULL,
  sales_tax_shipping_included TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sales_tax_amount FLOAT NOT NULL DEFAULT 0,
  shipping_type VARCHAR(255) DEFAULT NULL,
  get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 - Yes, 0 - No',
  shipping_address TEXT NOT NULL,
  created_date DATETIME NOT NULL,
  is_part_of_order TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  ebay_order_id VARCHAR(255) NOT NULL DEFAULT '0',
  checkout_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - INCOMPETED, 1 - COMPLETED',
  update_time DATETIME DEFAULT NULL,
  payment_time DATETIME DEFAULT NULL,
  payment_used VARCHAR(255) DEFAULT NULL,
  payment_status VARCHAR(255) DEFAULT NULL,
  payment_hold_status VARCHAR(255) DEFAULT NULL,
  payment_status_m2e_code TINYINT(2) UNSIGNED NOT NULL DEFAULT 0
      COMMENT '0 - Not Selected, look into helper code Sales.php ',
  shipping_time DATETIME DEFAULT NULL,
  shipping_buyer_selected TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  shipping_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No select, look into helper Sales.php',
  shipping_selected_service VARCHAR(255) DEFAULT NULL,
  shipping_selected_cost FLOAT NOT NULL DEFAULT 0,
  price FLOAT NOT NULL DEFAULT 0,
  currency VARCHAR(255) NOT NULL DEFAULT 'USD',
  best_offer_sale TINYINT(2) NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX account_mode (account_mode),
  INDEX amount_paid (amount_paid),
  INDEX amount_saved (amount_saved),
  INDEX magento_order_id (magento_order_id),
  INDEX marketplace_id (marketplace_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_orders_external_transactions;
CREATE TABLE m2epro_ebay_orders_external_transactions (
  id INT(11) NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED NOT NULL,
  ebay_id VARCHAR(255) NOT NULL,
  `time` DATETIME NOT NULL,
  fee FLOAT UNSIGNED NOT NULL DEFAULT 0,
  sum FLOAT UNSIGNED NOT NULL DEFAULT 0,
  is_refund TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 - No, 1 - Yes',
  PRIMARY KEY (id),
  INDEX ebay_id (ebay_id),
  INDEX fee (fee),
  INDEX is_refund (is_refund),
  INDEX order_id (order_id),
  INDEX sum (sum),
  INDEX `time` (`time`)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_orders_items;
CREATE TABLE m2epro_ebay_orders_items (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  ebay_order_id INT(11) UNSIGNED NOT NULL,
  item_id DECIMAL(20,0) UNSIGNED NOT NULL DEFAULT 0,
  transaction_id DECIMAL(20,0) UNSIGNED NOT NULL DEFAULT 0,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  store_id INT(11) UNSIGNED DEFAULT NULL,
  listing_type VARCHAR(255) NOT NULL,
  buy_it_now_price FLOAT UNSIGNED NOT NULL DEFAULT 0,
  auto_pay TINYINT(2) UNSIGNED NOT NULL,
  currency VARCHAR(255) NOT NULL DEFAULT 'USD',
  item_sku VARCHAR(255) DEFAULT NULL,
  item_title VARCHAR(255) NOT NULL,
  item_condition_display_name VARCHAR(255) DEFAULT NULL,
  qty_purchased INT(11) UNSIGNED NOT NULL DEFAULT 0,
  price FLOAT UNSIGNED NOT NULL DEFAULT 0,
  variations TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX auto_pay (auto_pay),
  INDEX buy_it_now_price (buy_it_now_price),
  INDEX ebay_order_id (ebay_order_id),
  INDEX item_id (item_id),
  INDEX listing_type (listing_type),
  INDEX price (price),
  INDEX product_id (product_id),
  INDEX qty_purchased (qty_purchased),
  INDEX store_id (store_id),
  INDEX transaction_id (transaction_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_orders_logs;
CREATE TABLE m2epro_ebay_orders_logs (
  id INT(11) NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED NOT NULL,
  message TEXT NOT NULL,
  code INT(11) UNSIGNED NOT NULL DEFAULT 0,
  message_trace TEXT DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX code (code),
  INDEX order_id (order_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_feedbacks;
CREATE TABLE m2epro_feedbacks (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  ebay_item_id DECIMAL(20,0) UNSIGNED NOT NULL,
  ebay_item_title VARCHAR(255) NOT NULL,
  ebay_transaction_id DECIMAL(20,0) UNSIGNED NOT NULL,
  buyer_name VARCHAR(200) NOT NULL,
  buyer_feedback_id DECIMAL(20,0) UNSIGNED NOT NULL,
  buyer_feedback_text VARCHAR(255) NOT NULL,
  buyer_feedback_date DATETIME NOT NULL,
  buyer_feedback_type VARCHAR(20) NOT NULL,
  seller_feedback_id DECIMAL(20,0) UNSIGNED NOT NULL,
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_feedbacks_templates;
CREATE TABLE m2epro_feedbacks_templates (
  id INT(11) NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  body TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings;
CREATE TABLE m2epro_listings (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  attribute_set_id INT(11) UNSIGNED NOT NULL,
  selling_format_template_id INT(11) UNSIGNED NOT NULL,
  listing_template_id INT(11) UNSIGNED NOT NULL,
  description_template_id INT(11) UNSIGNED NOT NULL,
  synchronization_template_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  products_total_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_listed_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_inactive_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
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
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_set_id (attribute_set_id),
  INDEX categories_add_action (categories_add_action),
  INDEX categories_delete_action (categories_delete_action),
  INDEX description_template_id (description_template_id),
  INDEX hide_products_others_listings (hide_products_others_listings),
  INDEX listing_template_id (listing_template_id),
  INDEX selling_format_template_id (selling_format_template_id),
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
  INDEX synchronization_template_id (synchronization_template_id),
  INDEX title (title)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_categories;
CREATE TABLE m2epro_listings_categories (
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_logs;
CREATE TABLE m2epro_listings_logs (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  listing_title VARCHAR(255) NOT NULL,
  product_title VARCHAR(255) DEFAULT NULL,
  creator VARCHAR(255) DEFAULT NULL,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX action_id (action_id),
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_products;
CREATE TABLE m2epro_listings_products (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  ebay_items_id INT(11) UNSIGNED DEFAULT NULL,
  ebay_start_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  ebay_reserve_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  ebay_buyitnow_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  ebay_qty INT(11) UNSIGNED DEFAULT NULL,
  ebay_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  ebay_bids INT(11) UNSIGNED DEFAULT NULL,
  ebay_start_date DATETIME DEFAULT NULL,
  ebay_end_date DATETIME DEFAULT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status_changer TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX ebay_bids (ebay_bids),
  INDEX ebay_buyitnow_price (ebay_buyitnow_price),
  INDEX ebay_end_date (ebay_end_date),
  INDEX ebay_items_id (ebay_items_id),
  INDEX ebay_qty (ebay_qty),
  INDEX ebay_qty_sold (ebay_qty_sold),
  INDEX ebay_reserve_price (ebay_reserve_price),
  INDEX ebay_start_date (ebay_start_date),
  INDEX ebay_start_price (ebay_start_price),
  INDEX listing_id (listing_id),
  INDEX product_id (product_id),
  INDEX status (status),
  INDEX status_changer (status_changer)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_products_variations;
CREATE TABLE m2epro_listings_products_variations (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_product_id INT(11) UNSIGNED NOT NULL,
  ebay_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  ebay_qty INT(11) UNSIGNED DEFAULT NULL,
  ebay_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  `add` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `delete` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `add` (`add`),
  INDEX `delete` (`delete`),
  INDEX ebay_price (ebay_price),
  INDEX ebay_qty (ebay_qty),
  INDEX ebay_qty_sold (ebay_qty_sold),
  INDEX listing_product_id (listing_product_id),
  INDEX status (status)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_products_variations_options;
CREATE TABLE m2epro_listings_products_variations_options (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  product_type VARCHAR(255) NOT NULL,
  attribute VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute (attribute),
  INDEX listing_product_variation_id (listing_product_variation_id),
  INDEX `option` (`option`),
  INDEX product_id (product_id),
  INDEX product_type (product_type)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_templates;
CREATE TABLE m2epro_listings_templates (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  categories_mode TINYINT(2) UNSIGNED NOT NULL,
  categories_main_id INT(11) UNSIGNED NOT NULL,
  categories_main_attribute VARCHAR(255) NOT NULL,
  categories_secondary_id INT(11) UNSIGNED NOT NULL,
  categories_secondary_attribute VARCHAR(255) NOT NULL,
  store_categories_main_mode TINYINT(2) UNSIGNED NOT NULL,
  store_categories_main_id INT(11) UNSIGNED NOT NULL,
  store_categories_main_attribute VARCHAR(255) NOT NULL,
  store_categories_secondary_mode TINYINT(2) UNSIGNED NOT NULL,
  store_categories_secondary_id INT(11) UNSIGNED NOT NULL,
  store_categories_secondary_attribute VARCHAR(255) NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL,
  variation_enabled TINYINT(2) UNSIGNED NOT NULL,
  variation_ignore TINYINT(2) UNSIGNED NOT NULL,
  condition_value VARCHAR(255) NOT NULL,
  condition_attribute VARCHAR(255) NOT NULL,
  product_details LONGTEXT NOT NULL,
  enhancement VARCHAR(25) NOT NULL,
  gallery_type TINYINT(2) UNSIGNED NOT NULL,
  country VARCHAR(255) NOT NULL,
  postal_code VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  use_ebay_tax_table TINYINT(2) UNSIGNED NOT NULL,
  vat_percent TINYINT(2) NOT NULL,
  get_it_fast TINYINT(2) NOT NULL,
  dispatch_time INT(11) NOT NULL,
  local_shipping_mode TINYINT(2) NOT NULL,
  local_shipping_discount_mode TINYINT(2) NOT NULL,
  international_shipping_mode TINYINT(2) NOT NULL,
  international_shipping_discount_mode TINYINT(2) NOT NULL,
  pay_pal_email_address VARCHAR(255) NOT NULL,
  pay_pal_immediate_payment TINYINT(2) UNSIGNED NOT NULL,
  refund_accepted VARCHAR(255) NOT NULL,
  refund_option VARCHAR(255) NOT NULL,
  refund_within VARCHAR(255) NOT NULL,
  refund_description TEXT NOT NULL,
  refund_shippingcost VARCHAR(255) NOT NULL,
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
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
  INDEX marketplace_id (marketplace_id),
  INDEX sku_mode (sku_mode),
  INDEX store_categories_main_attribute (store_categories_main_attribute),
  INDEX store_categories_main_id (store_categories_main_id),
  INDEX store_categories_main_mode (store_categories_main_mode),
  INDEX store_categories_secondary_attribute (store_categories_secondary_attribute),
  INDEX store_categories_secondary_id (store_categories_secondary_id),
  INDEX store_categories_secondary_mode (store_categories_secondary_mode),
  INDEX title (title),
  INDEX use_ebay_tax_table (use_ebay_tax_table),
  INDEX variation_enabled (variation_enabled),
  INDEX variation_ignore (variation_ignore),
  INDEX vat_percent (vat_percent)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_templates_calculated_shipping;
CREATE TABLE m2epro_listings_templates_calculated_shipping (
  listing_template_id INT(11) UNSIGNED NOT NULL,
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
  PRIMARY KEY (listing_template_id),
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

DROP TABLE IF EXISTS m2epro_listings_templates_payments;
CREATE TABLE m2epro_listings_templates_payments (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_template_id INT(11) UNSIGNED NOT NULL,
  payment_id VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX listing_template_id (listing_template_id),
  INDEX payment_id (payment_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_templates_shippings;
CREATE TABLE m2epro_listings_templates_shippings (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_template_id INT(11) UNSIGNED NOT NULL,
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
  INDEX listing_template_id (listing_template_id),
  INDEX priority (priority),
  INDEX shipping_type (shipping_type),
  INDEX shipping_value (shipping_value)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listings_templates_specifics;
CREATE TABLE m2epro_listings_templates_specifics (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_template_id INT(11) UNSIGNED NOT NULL,
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
  INDEX listing_template_id (listing_template_id),
  INDEX mode (mode),
  INDEX mode_relation_id (mode_relation_id),
  INDEX value_custom_attribute (value_custom_attribute),
  INDEX value_custom_value (value_custom_value),
  INDEX value_mode (value_mode)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_lock_items;
CREATE TABLE m2epro_lock_items (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  nick VARCHAR(255) NOT NULL,
  `data` TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX nick (nick)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_marketplaces;
CREATE TABLE m2epro_marketplaces (
  id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  code VARCHAR(255) NOT NULL,
  url VARCHAR(255) NOT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sorder INT(11) UNSIGNED NOT NULL DEFAULT 0,
  categories_version INT(11) UNSIGNED NOT NULL DEFAULT 0,
  group_title VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX categories_version (categories_version),
  INDEX code (code),
  INDEX group_title (group_title),
  INDEX sorder (sorder),
  INDEX status (status),
  INDEX title (title),
  INDEX url (url)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_messages;
CREATE TABLE m2epro_messages (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  ebay_item_id DECIMAL(20,0) UNSIGNED NOT NULL,
  ebay_item_title VARCHAR(255) NOT NULL,
  sender_name VARCHAR(255) NOT NULL,
  message_id DECIMAL(20,0) UNSIGNED NOT NULL,
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_migration_temp;
CREATE TABLE IF NOT EXISTS m2epro_migration_temp (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  type VARCHAR(255) DEFAULT NULL,
  oldValue TEXT NOT NULL,
  newValue TEXT NOT NULL,
  PRIMARY KEY (id),
  INDEX type (type)
)
ENGINE = MYISAM
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_products_changes;
CREATE TABLE m2epro_products_changes (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT(11) UNSIGNED NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  attribute VARCHAR(255) DEFAULT NULL,
  value_old LONGTEXT DEFAULT NULL,
  value_new LONGTEXT DEFAULT NULL,
  count_changes INT(11) UNSIGNED DEFAULT NULL,
  deferred TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  analizing_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `action` (`action`),
  INDEX analizing_date (analizing_date),
  INDEX attribute (attribute),
  INDEX count_changes (count_changes),
  INDEX deferred (deferred),
  INDEX product_id (product_id)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_selling_formats_templates;
CREATE TABLE m2epro_selling_formats_templates (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  listing_type TINYINT(2) UNSIGNED NOT NULL,
  listing_type_attribute VARCHAR(255) NOT NULL,
  listing_is_private TINYINT(2) UNSIGNED NOT NULL,
  duration_ebay TINYINT(4) UNSIGNED NOT NULL,
  duration_attribute VARCHAR(255) NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  currency VARCHAR(50) NOT NULL,
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
  synch_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
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
  INDEX duration_ebay (duration_ebay),
  INDEX listing_is_private (listing_is_private),
  INDEX listing_type (listing_type),
  INDEX listing_type_attribute (listing_type_attribute),
  INDEX qty_custom_attribute (qty_custom_attribute),
  INDEX qty_custom_value (qty_custom_value),
  INDEX qty_mode (qty_mode),
  INDEX reserve_price_coefficient (reserve_price_coefficient),
  INDEX reserve_price_custom_attribute (reserve_price_custom_attribute),
  INDEX reserve_price_mode (reserve_price_mode),
  INDEX start_price_coefficient (start_price_coefficient),
  INDEX start_price_custom_attribute (start_price_custom_attribute),
  INDEX start_price_mode (start_price_mode),
  INDEX title (title)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronizations_logs;
CREATE TABLE m2epro_synchronizations_logs (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  synchronizations_runs_id INT(11) UNSIGNED NOT NULL,
  synch_task TINYINT(2) UNSIGNED NOT NULL,
  creator VARCHAR(255) NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  priority TINYINT(2) UNSIGNED NOT NULL,
  description TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX creator (creator),
  INDEX priority (priority),
  INDEX synch_task (synch_task),
  INDEX synchronizations_runs_id (synchronizations_runs_id),
  INDEX type (type)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronizations_runs;
CREATE TABLE m2epro_synchronizations_runs (
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronizations_templates;
CREATE TABLE m2epro_synchronizations_templates (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  start_auto_list TINYINT(2) UNSIGNED NOT NULL,
  end_auto_stop TINYINT(2) UNSIGNED NOT NULL,
  revise_update_ebay_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_ebay_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  revise_change_selling_format_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_description_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_listing_template TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  relist_schedule_type TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_metric TINYINT(2) UNSIGNED NOT NULL,
  relist_schedule_through_value INT(11) UNSIGNED NOT NULL,
  relist_schedule_week VARCHAR(255) NOT NULL,
  relist_schedule_week_start_time TIME NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX end_auto_stop (end_auto_stop),
  INDEX relist_filter_user_lock (relist_filter_user_lock),
  INDEX relist_is_in_stock (relist_is_in_stock),
  INDEX relist_qty (relist_qty),
  INDEX relist_qty_value (relist_qty_value),
  INDEX relist_qty_value_max (relist_qty_value_max),
  INDEX relist_schedule_through_metric (relist_schedule_through_metric),
  INDEX relist_schedule_through_value (relist_schedule_through_value),
  INDEX relist_schedule_week_start_time (relist_schedule_week_start_time),
  INDEX relist_shedule_type (relist_schedule_type),
  INDEX relist_shedule_week (relist_schedule_week),
  INDEX relist_status_enabled (relist_status_enabled),
  INDEX revise_change_description_template (revise_change_description_template),
  INDEX revise_change_listing_template (revise_change_listing_template),
  INDEX revise_change_selling_format_template (revise_change_selling_format_template),
  INDEX revise_update_description (revise_update_description),
  INDEX revise_update_ebay_price (revise_update_ebay_price),
  INDEX revise_update_ebay_qty (revise_update_ebay_qty),
  INDEX revise_update_sub_title (revise_update_sub_title),
  INDEX revise_update_title (revise_update_title),
  INDEX start_auto_list (start_auto_list),
  INDEX stop_out_off_stock (stop_out_off_stock),
  INDEX stop_qty (stop_qty),
  INDEX stop_qty_value (stop_qty_value),
  INDEX stop_qty_value_max (stop_qty_value_max),
  INDEX stop_status_disabled (stop_status_disabled),
  INDEX title (title)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_templates_attribute_sets;
CREATE TABLE m2epro_templates_attribute_sets (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_type TINYINT(2) UNSIGNED NOT NULL,
  template_id INT(11) UNSIGNED NOT NULL,
  attribute_set_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_set_id (attribute_set_id),
  INDEX template_id (template_id),
  INDEX template_type (template_type)
)
ENGINE = INNODB
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO ess_config (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
  ('/M2ePro/license/', 'key', '', 'License Key', '2011-07-21 09:51:06', '2010-12-16 10:50:34'),
  ('/modules/', 'M2ePro', '0.0.0.r0', '"Magento To Ebay" module', '2011-08-17 10:39:24', '2011-01-19 15:43:36'),
  ('/M2ePro/license/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live', '2011-07-28 09:01:01', '2011-01-19 15:43:36'),
  ('/M2ePro/license/', 'status', '1', '1 - Active\r\n2 - Suspended\r\n3 - Closed',
   '2011-07-28 09:01:01', '2011-01-19 15:43:36'),
  ('/M2ePro/server/', 'lock', '0', '0 - No\r\n1 - Yes', '2011-04-20 13:19:12', '2011-01-19 15:43:36'),
  ('/M2ePro/license/', 'expired_date', '', 'Expiration date', '2011-07-21 09:51:07', '2011-01-19 15:43:36'),
  ('/M2ePro/server/', 'messages', '[]', 'Server messages', '2011-04-21 11:28:05', '2011-02-01 14:32:14'),
  ('/server/', 'baseurl', 'https://m2epro.com/', 'Support server base url',
   '2011-03-01 10:15:03', '2011-03-01 10:15:03'),
  ('/M2ePro/server/', 'directory', '/server/', 'Server scripts directory',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/M2ePro/', 'application_key', 'b79a495170da3b081c9ebae6c255c7fbe1b139b5', 'Application key',
   '2011-02-24 09:52:27', '2011-02-24 09:52:27'),
  ('/M2ePro/license/', 'domain', '', 'Valid domain', '2011-07-21 09:51:07', '2011-03-01 10:21:28'),
  ('/M2ePro/license/', 'ip', '', 'Valid ip', '2011-07-21 09:51:07', '2011-03-01 10:21:28'),
  ('/M2ePro/license/', 'directory', '', 'Valid directory', '2011-07-21 09:51:07', '2011-03-01 10:21:28'),
  ('/M2ePro/docs/', 'baseurl', 'http://docs.m2epro.com/', 'Documentation baseurl',
   '2011-04-20 15:20:53', '2011-04-20 15:20:53'),
  ('/M2ePro/support/', 'defect_mail', 'support@m2epro.com', 'Defect email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/M2ePro/support/', 'feature_mail', 'support@m2epro.com', 'Feature email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49'),
  ('/M2ePro/support/', 'inquiry_mail', 'support@m2epro.com', 'Inquiry email address',
   '2011-03-03 13:57:49', '2011-03-03 13:57:49');

INSERT INTO m2epro_config (`group`, `key`, `value`, `notice`, `update_date`, `create_date`) VALUES
  ('/synchronization/settings/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-05-12 09:04:57', '2010-12-16 09:14:39'),
  ('/synchronization/settings/feedbacks/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-05-12 09:24:31', '2010-12-16 09:27:15'),
  ('/logs/cleaning/ebay_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-04-01 11:00:32', '2010-12-16 09:57:25'),
  ('/logs/cleaning/ebay_listings/', 'days', '30', 'in days', '2011-06-07 11:06:14', '2010-12-16 09:57:34'),
  ('/logs/cleaning/listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-06-07 11:06:36', '2010-12-16 09:58:38'),
  ('/logs/cleaning/listings/', 'days', '30', 'in days', '2011-06-07 11:06:14', '2010-12-16 09:58:42'),
  ('/synchronization/settings/defaults/update_listings_products/', 'since_time', NULL,
   'eBay time of last check changes on eBay', '2011-08-04 14:50:34', '2010-12-16 10:20:43'),
  ('/ebay/currency/', 'USD', 'US Dollar', NULL, '2011-04-01 10:47:08', '2010-12-16 12:06:22'),
  ('/ebay/currency/', 'CAD', 'Canadian Dollar', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:36'),
  ('/ebay/currency/', 'GBP', 'British Pound', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:43'),
  ('/ebay/currency/', 'AUD', 'Australian Dollar', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:46'),
  ('/ebay/currency/', 'EUR', 'Euro', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:52'),
  ('/ebay/currency/', 'CHF', 'Swiss Franc', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:53'),
  ('/ebay/currency/', 'CNY', 'Chinese Renminbi', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:56'),
  ('/ebay/currency/', 'HKD', 'Hong Kong Dollar', NULL, '2010-12-16 10:20:43', '2010-12-16 12:06:58'),
  ('/ebay/currency/', 'PHP', 'Philippines Peso', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:00'),
  ('/ebay/currency/', 'PLN', 'Polish Zloty', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:02'),
  ('/ebay/currency/', 'SEK', 'Sweden Krona', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:04'),
  ('/ebay/currency/', 'SGD', 'Singapore Dollar', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:06'),
  ('/ebay/currency/', 'TWD', 'Taiwanese Dollar', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:08'),
  ('/ebay/currency/', 'INR', 'Indian Rupees', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:11'),
  ('/ebay/currency/', 'MYR', 'Malaysian Ringgit', NULL, '2010-12-16 10:20:43', '2010-12-16 12:07:13'),
  ('/logs/cleaning/synchronizations/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-06-07 11:06:36', '2010-12-23 09:46:58'),
  ('/logs/cleaning/synchronizations/', 'days', '30', 'in days', '2011-06-07 11:06:14', '2010-12-23 09:47:12'),
  ('/synchronization/settings/defaults/update_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-10 10:36:58'),
  ('/synchronization/settings/defaults/remove_deleted_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-17 02:11:56', '2011-01-14 15:05:01'),
  ('/synchronization/lockItem/', 'max_deactivate_time', '600', 'in seconds',
   '2010-12-16 10:20:43', '2011-01-14 15:23:06'),
  ('/synchronization/profiler/', 'mode', '1', '1 - production, \r\n2 - debugging, \r\n3 - developing',
   '2011-02-18 10:01:54', '2011-01-14 16:20:14'),
  ('/synchronization/profiler/', 'delete_resources', '0', '0 - disable, \r\n1 - enable',
   '2011-02-18 10:01:54', '2011-01-17 17:10:01'),
  ('/synchronization/memory/', 'max_size', '512', 'in Mb', '2010-12-16 10:20:43', '2011-01-18 12:03:07'),
  ('/synchronization/memory/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2010-12-16 10:20:43', '2011-01-18 12:04:24'),
  ('/synchronization/profiler/', 'print_type', '2', '1 - var_dump(), \r\n2 - print + <br/>, \r\n3 - print + EOL',
   '2010-12-16 10:20:43', '2011-01-18 14:52:03'),
  ('/synchronization/settings/marketplaces/default/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2010-12-16 10:20:43', '2011-01-18 16:27:23'),
  ('/synchronization/settings/marketplaces/details/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2010-12-16 10:20:43', '2011-01-18 16:28:01'),
  ('/synchronization/settings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-06-14 13:40:41', '2011-01-18 16:28:55'),
  ('/listings/lockItem/', 'max_deactivate_time', '600', 'in seconds', '2010-12-16 10:20:43', '2011-01-19 15:43:36'),
  ('/synchronization/settings/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/templates/start/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/templates/end/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/feedbacks/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-10 01:44:02', '2011-01-10 01:44:02'),
  ('/synchronization/settings/feedbacks/response/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-10 01:44:02', '2011-01-10 01:44:02'),
  ('/synchronization/settings/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-01-12 02:55:16', '2011-01-12 02:55:16'),
  ('/synchronization/settings/marketplaces/default/', 'last_time', NULL, 'Next time update marketplaces list',
   '2011-06-29 14:33:35', '2010-12-16 10:20:43'),
  ('/synchronization/settings/orders/', 'since_time', NULL, 'eBay time of last check changes on eBay',
   '2011-05-11 09:10:33', '2011-04-27 09:37:01'),
  ('/synchronization/settings/ebay_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2011-06-07 10:05:39', '2011-01-12 02:55:16'),
  ('/logs/cleaning/listings/', 'default', '30', 'in days', '2010-12-23 02:54:16', '2010-12-23 02:54:16'),
  ('/logs/cleaning/ebay_listings/', 'default', '30', 'in days', '2010-12-23 02:54:16', '2010-12-23 02:54:16'),
  ('/logs/cleaning/synchronizations/', 'default', '30', 'in days', '2010-12-23 02:54:16', '2010-12-23 02:54:16'),
  ('/products/settings/', 'show_thumbnails', '1', 'Visibility thumbnails into grid',
   '2011-07-08 09:58:37', '2010-12-23 02:54:16'),
  ('/block_notices/settings/', 'show', '1', '0 - disable, \r\n1 - enable',
   '2011-07-18 15:57:34', '2010-12-23 02:54:16'),
  ('/debug/exceptions/', 'send_to_server', '1', '0 - disable, \r\n1 - enable',
   '2011-07-18 15:57:34', '2011-07-18 15:57:34'),
  ('/debug/fatal_error/', 'send_to_server', '1', '0 - disable, \r\n1 - enable',
   '2011-07-18 15:57:34', '2011-07-18 15:57:34'),
  ('/feedbacks/notification/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2011-07-26 14:05:06', '2011-07-26 13:26:47'),
  ('/feedbacks/notification/', 'last_check', NULL, 'Date last check new buyers feedbacks',
   '2011-08-17 12:11:19', '2011-07-26 12:55:44'),
  ('/wizard/', 'status', '0', '0 - None, 99 - Skip, 100 - Complete', '2011-08-02 14:50:28', '2011-07-27 10:37:00'),
  ('/migrate/', 'already_worked', '0', '', '2011-07-29 13:46:24', '2011-07-29 10:36:48'),
  ('/migrate/', 'custom_user_interface', '0', '', '2011-07-29 11:04:54', '2011-07-29 10:55:04'),
  ('/synchronization/settings/messages/receive/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2011-01-10 01:44:02', '2011-01-10 01:44:02'),
  ('/synchronization/settings/messages/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2011-08-10 09:24:28', '2011-08-10 09:19:42'),
  ('/messages/notification/', 'mode', '0', '0 - disable, \r\n1 - enable', '2011-08-08 12:06:42',
   '2011-08-11 12:06:40'),
  ('/messages/notification/', 'last_check', NULL, 'Time of last check for new eBay messages',
   '2011-08-17 12:11:19', '2011-08-11 12:12:17'),
  ('/messages/', 'mode', '0', '0 - disable, \r\n1 - enable', '2011-08-17 12:11:19', '2011-08-11 12:12:17'),
  ('/cron/', 'last_access', NULL, 'Time of last cron synchronization', '2011-08-17 12:11:19', '2011-08-11 12:12:17'),
  ('/cron/notification/', 'mode', '1', '0 - disable, \r\n1 - enable', '2011-08-17 12:11:19', '2011-08-11 12:12:17'),
  ('/cron/notification/', 'inactive_hours', '12', 'Allowed number of hours cron could be inactive',
   '2011-08-17 12:11:19', '2011-08-11 12:12:17'),
  ('/templates/description/', 'convert_linebreaks', '1', '0 - No\r\n1 - Yes',
   '2011-08-17 12:11:19', '2011-08-11 12:12:17');

INSERT INTO m2epro_marketplaces VALUES
  (0, 'United States', 'US', 'ebay.com', 0, 3, 0, 'America', '2011-06-29 14:33:33', '2011-05-05 06:55:43'),
  (2, 'Canada', 'Canada', 'ebay.ca', 0, 1, 0, 'America', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (3, 'United Kingdom', 'UK', 'ebay.co.uk', 0, 16, 0, 'Europe', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (15, 'Australia', 'Australia', 'ebay.com.au', 0, 17, 0, 'Asia / Pacific',
   '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (16, 'Austria', 'Austria', 'ebay.at', 0, 4, 0, 'Europe', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (23, 'Belgium (French)', 'Belgium_French', 'befr.ebay.be', 0, 6, 0, 'Europe',
   '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (71, 'France', 'France', 'ebay.fr', 0, 7, 0, 'Europe', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (77, 'Germany', 'Germany', 'ebay.de', 0, 8, 0, 'Europe', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (100, 'eBay Motors', 'eBayMotors', 'motors.ebay.com', 0, 23, 0, 'Other',
   '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (101, 'Italy', 'Italy', 'ebay.it', 0, 10, 0, 'Europe', '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (123, 'Belgium (Dutch)', 'Belgium_Dutch', 'benl.ebay.be', 0, 5, 0, 'Europe',
   '2011-06-29 14:33:34', '2011-05-05 06:55:43'),
  (146, 'Netherlands', 'Netherlands', 'ebay.nl', 0, 11, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:43'),
  (186, 'Spain', 'Spain', 'ebay.es', 0, 15, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:43'),
  (193, 'Switzerland', 'Switzerland', 'ebay.ch', 0, 13, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:43'),
  (201, 'Hong Kong', 'HongKong', 'ebay.com.hk', 0, 18, 0, 'Asia / Pacific',
   '2011-06-29 14:33:35', '2011-05-05 06:55:43'),
  (203, 'India', 'India', 'ebay.in', 0, 19, 0, 'Asia / Pacific', '2011-06-29 14:33:35', '2011-05-05 06:55:43'),
  (205, 'Ireland', 'Ireland', 'ebay.ie', 0, 9, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (207, 'Malaysia', 'Malaysia', 'ebay.com.my', 0, 20, 0, 'Asia / Pacific',
   '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (210, 'Canada (French)', 'CanadaFrench', 'cafr.ebay.ca', 0, 2, 0, 'America',
   '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (211, 'Philippines', 'Philippines', 'ebay.ph', 0, 21, 0, 'Asia / Pacific',
   '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (212, 'Poland', 'Poland', 'ebay.pl', 0, 12, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (216, 'Singapore', 'Singapore', 'ebay.com.sg', 0, 22, 0, 'Asia / Pacific',
   '2011-06-29 14:33:35', '2011-05-05 06:55:44'),
  (218, 'Sweden', 'Sweden', 'ebay.se', 0, 14, 0, 'Europe', '2011-06-29 14:33:35', '2011-05-05 06:55:44');

SQL
);

//#############################################

Mage::register('M2EPRO_IS_INSTALLATION',true);
$installer->endSetup();

//#############################################