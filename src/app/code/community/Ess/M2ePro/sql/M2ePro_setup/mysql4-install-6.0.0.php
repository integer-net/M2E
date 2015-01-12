<?php

//#############################################

/** @var $installer Ess_M2ePro_Model_Upgrade_MySqlSetup */
$installer = $this;
$installer->startSetup();

//#############################################

// GENERAL SCRIPT

$tempMagentoConnectUrl = 'http://www.magentocommerce.com/magento-connect/customer-experience/alternative-sales-models/';
$tempMagentoConnectUrl .= 'ebay-magento-integration-order-importing-and-stock-level-synchronization-9193.html';

$installer->run(<<<SQL

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

DROP TABLE IF EXISTS m2epro_cache_config;
CREATE TABLE m2epro_cache_config (
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

DROP TABLE IF EXISTS m2epro_exceptions_filters;
CREATE TABLE m2epro_exceptions_filters (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  preg_match TEXT NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  create_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing;
CREATE TABLE m2epro_listing (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  products_total_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_active_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  products_inactive_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  items_active_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  source_products TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  categories_add_action TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  categories_delete_action TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  additional_data TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX marketplace_id (marketplace_id),
  INDEX store_id (store_id),
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
  listing_product_id INT(11) UNSIGNED DEFAULT NULL,
  listing_title VARCHAR(255) NOT NULL,
  product_title VARCHAR(255) DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  action TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  creator VARCHAR(255) DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX action (action),
  INDEX action_id (action_id),
  INDEX component_mode (component_mode),
  INDEX creator (creator),
  INDEX initiator (initiator),
  INDEX listing_id (listing_id),
  INDEX listing_product_id (listing_product_id),
  INDEX listing_title (listing_title),
  INDEX priority (priority),
  INDEX product_id (product_id),
  INDEX product_title (product_title),
  INDEX type (type)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_other;
CREATE TABLE m2epro_listing_other (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  status_changer TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX marketplace_id (marketplace_id),
  INDEX product_id (product_id),
  INDEX status (status),
  INDEX status_changer (status_changer)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_other_log;
CREATE TABLE m2epro_listing_other_log (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_other_id INT(11) UNSIGNED DEFAULT NULL,
  identifier VARCHAR(32) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  action_id INT(11) UNSIGNED DEFAULT NULL,
  action TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  creator VARCHAR(255) DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  description TEXT DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX action (action),
  INDEX action_id (action_id),
  INDEX component_mode (component_mode),
  INDEX creator (creator),
  INDEX initiator (initiator),
  INDEX listing_other_id (listing_other_id),
  INDEX priority (priority),
  INDEX title (title),
  INDEX type (type)
)
ENGINE = MYISAM
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
  additional_data TEXT DEFAULT NULL,
  tried_to_list TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_need_synchronize tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  synch_reasons TEXT NULL DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX listing_id (listing_id),
  INDEX product_id (product_id),
  INDEX status (status),
  INDEX status_changer (status_changer),
  INDEX tried_to_list (tried_to_list),
  INDEX is_need_synchronize (is_need_synchronize)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_listing_product_variation;
CREATE TABLE m2epro_listing_product_variation (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_product_id INT(11) UNSIGNED NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX listing_product_id (listing_product_id)
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
  data TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX nick (nick)
)
ENGINE = MYISAM
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
ENGINE = MYISAM
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
  INDEX component_mode (component_mode)
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
  state TINYINT(2) UNSIGNED DEFAULT 0,
  reservation_state TINYINT(2) UNSIGNED DEFAULT 0,
  reservation_start_date DATETIME DEFAULT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX component_mode (component_mode),
  INDEX magento_order_id (magento_order_id),
  INDEX marketplace_id (marketplace_id),
  INDEX reservation_state (reservation_state),
  INDEX state (state)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order_change;
CREATE TABLE m2epro_order_change (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  component VARCHAR(10) NOT NULL,
  order_id INT(11) UNSIGNED NOT NULL,
  action VARCHAR(50) NOT NULL,
  params LONGTEXT NOT NULL,
  creator_type TINYINT(2) NOT NULL DEFAULT 0,
  hash VARCHAR(50) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX action (action),
  INDEX creator_type (creator_type),
  INDEX hash (hash),
  INDEX order_id (order_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order_item;
CREATE TABLE m2epro_order_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT(11) UNSIGNED NOT NULL,
  product_id INT(11) UNSIGNED DEFAULT NULL,
  product_details TEXT DEFAULT NULL,
  state TINYINT(2) UNSIGNED DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX order_id (order_id),
  INDEX product_id (product_id),
  INDEX state (state)
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
  initiator TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  component_mode VARCHAR(10) DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX initiator (initiator),
  INDEX order_id (order_id),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_order_repair;
CREATE TABLE m2epro_order_repair (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT(11) UNSIGNED NOT NULL,
  input_data TEXT DEFAULT NULL,
  output_data TEXT DEFAULT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  hash VARCHAR(50) DEFAULT NULL,
  component VARCHAR(10) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component (component),
  INDEX hash (hash),
  INDEX product_id (product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_primary_config;
CREATE TABLE m2epro_primary_config (
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

DROP TABLE IF EXISTS m2epro_processing_request;
CREATE TABLE m2epro_processing_request (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  component VARCHAR(10) NOT NULL,
  perform_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  next_part INT(11) UNSIGNED DEFAULT NULL,
  hash VARCHAR(255) NOT NULL,
  processing_hash VARCHAR(255) NOT NULL,
  request_body LONGTEXT NOT NULL,
  responser_model VARCHAR(255) NOT NULL,
  responser_params LONGTEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component (component),
  INDEX hash (hash),
  INDEX next_part (next_part),
  INDEX perform_type (perform_type),
  INDEX processing_hash (processing_hash),
  INDEX responser_model (responser_model)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_product_change;
CREATE TABLE m2epro_product_change (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED DEFAULT NULL,
  action VARCHAR(255) NOT NULL,
  attribute VARCHAR(255) DEFAULT NULL,
  value_old LONGTEXT DEFAULT NULL,
  value_new LONGTEXT DEFAULT NULL,
  creator_type TINYINT(2) UNSIGNED NOT NULL,
  count_changes INT(11) UNSIGNED DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX action (action),
  INDEX attribute (attribute),
  INDEX creator_type (creator_type),
  INDEX product_id (product_id),
  INDEX store_id (store_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_stop_queue;
CREATE TABLE m2epro_stop_queue (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  item_data TEXT NOT NULL,
  account_hash VARCHAR(255) NOT NULL,
  marketplace_id INT(11) UNSIGNED DEFAULT NULL,
  component_mode VARCHAR(255) NOT NULL,
  is_processed TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_hash (account_hash),
  INDEX component_mode (component_mode),
  INDEX is_processed (is_processed),
  INDEX marketplace_id (marketplace_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronization_config;
CREATE TABLE m2epro_synchronization_config (
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
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_synchronization_run;
CREATE TABLE m2epro_synchronization_run (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  initiator TINYINT(2) UNSIGNED NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  kill_now TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX initiator (initiator)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_template_selling_format;
CREATE TABLE m2epro_template_selling_format (
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

DROP TABLE IF EXISTS m2epro_template_synchronization;
CREATE TABLE m2epro_template_synchronization (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  revise_change_listing TINYINT(2) UNSIGNED NOT NULL,
  revise_change_selling_format_template TINYINT(2) UNSIGNED NOT NULL,
  component_mode VARCHAR(10) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX component_mode (component_mode),
  INDEX revise_change_listing (revise_change_listing),
  INDEX revise_change_selling_format_template (revise_change_selling_format_template),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_wizard;
CREATE TABLE m2epro_wizard (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  nick VARCHAR(255) NOT NULL,
  view VARCHAR(255) NOT NULL,
  status INT(11) UNSIGNED NOT NULL,
  step VARCHAR(255) DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL,
  priority INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX nick (nick)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_migration_v6;
CREATE TABLE m2epro_migration_v6 (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  component VARCHAR(32) NOT NULL,
  `group` VARCHAR(255) NOT NULL,
  data TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/', 'key', NULL, 'License Key', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'domain', NULL, 'Valid domain', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'ip', NULL, 'Valid ip', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/', 'directory', NULL, 'Valid directory', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'lock', '0', '0 - No\r\n1 - Yes', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'messages', '[]', 'Server messages', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'directory', '/server/', 'Server scripts directory',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/server/', 'application_key', 'b79a495170da3b081c9ebae6c255c7fbe1b139b5', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/modules/', 'M2ePro', '0.0.0.r0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/server/', 'baseurl', 'https://m2epro.com/', 'Support server base url',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/cron/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/', 'last_access', NULL, 'Time of last cron synchronization', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/', 'double_run_protection', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/logs_cleaning/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/logs_cleaning/', 'interval', '86400', 'in seconds', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/logs_cleaning/', 'last_access', NULL, 'date of last access', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/processing/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/processing/', 'interval', '240', 'in seconds', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/processing/', 'last_access', NULL, 'date of last access', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/synchronization/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/synchronization/', 'interval', '300', 'in seconds', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/synchronization/', 'last_access', NULL, 'date of last access', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/cron/task/servicing/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/servicing/', 'interval', '3600', 'in seconds', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/cron/task/servicing/', 'last_access', NULL, 'date of last access', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/listings/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/cleaning/listings/', 'default', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/listings/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/cleaning/other_listings/', 'default', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/other_listings/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/synchronizations/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/logs/cleaning/synchronizations/', 'default', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/synchronizations/', 'days', '30', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/orders/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/orders/', 'default', '90', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/cleaning/orders/', 'days', '90', 'in days', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/listings/', 'last_action_id', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/logs/other_listings/', 'last_action_id', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'documentation_url', 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'video_tutorials_url', 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/Video+Tutorials',
   NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'knowledge_base_url', 'http://support.m2epro.com/knowledgebase', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/support/', 'clients_portal_url', 'https://m2epro.com/clients/', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/support/', 'main_website_url', 'http://m2epro.com/', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'main_support_url', 'http://support.m2epro.com/', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'magento_connect_url', '{$tempMagentoConnectUrl}', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/', 'contact_email', 'support@m2epro.com', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/support/uservoice/', 'api_url', 'http://magento2ebay.uservoice.com/api/v1/', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/support/uservoice/', 'api_client_key', 'WEsfO8nFh3FXffUU1Oa7A', NULL, '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/license/validation/directory/notification/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/license/validation/domain/notification/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/license/validation/ip/notification/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/', 'show_block_notices', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/', 'show_products_thumbnails', '1', 'Visibility thumbnails into grid', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/common/component/', 'default', 'amazon', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/common/autocomplete/', 'max_records_quantity', '100', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/common/cron/error/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/common/cron/error/', 'max_inactive_hours', '1', 'Allowed number of hours cron could be inactive',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/', 'mode', 'simple', 'simple, advanced', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/notice/', 'disable_collapse', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/cron/popup/', 'confirm', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/cron/notification/', 'mode', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/cron/notification/', 'max_inactive_hours', '12', 'Allowed number of hours cron could be inactive',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/template/category/', 'show_tax_category', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/feedbacks/notification/', 'mode', '0', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/view/ebay/feedbacks/notification/', 'last_check', NULL, 'Date last check new buyers feedbacks',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/ebay/advanced/autoaction_popup/', 'shown', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/debug/exceptions/', 'send_to_server', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/exceptions/', 'filters_mode', '0', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/fatal_error/', 'send_to_server', '1', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/debug/maintenance/', 'mode', '0', '0 - disable,\r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/debug/maintenance/', 'restore_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/renderer/description/', 'convert_linebreaks', '1', '0 - No\r\n1 - Yes', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  ('/other/paypal/', 'url', 'paypal.com/cgi-bin/webscr/', 'PayPal url', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/listings/lockItem/', 'max_deactivate_time', '900', 'in seconds', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/product/index/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/order/magento/settings/', 'create_with_first_product_options_when_variation_unavailable', '1',
   '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_synchronization_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  (NULL, 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/settings/lockItem/', 'max_deactivate_time', '900', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/settings/profiler/', 'mode', '1', '1 - production, \r\n2 - debugging, \r\n3 - developing',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/settings/profiler/', 'delete_resources', '0', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/settings/profiler/', 'print_type', '2', '1 - var_dump(), \r\n2 - print + <br/>, \r\n3 - print + EOL',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/settings/product_change/', 'max_count_per_one_time', '500', NULL,
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/settings/product_change/', 'max_lifetime', '86400', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/settings/product_change/', 'max_count', '10000', NULL,
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/processing/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/deleted_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/deleted_products/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/deleted_products/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/stop_queue/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/defaults/stop_queue/', 'interval', '3600', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/defaults/stop_queue/', 'last_time', NULL, 'Last check time',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/defaults/inspector/', 'mode', 'circle', 'circle',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'last_listing_product_id', NULL, NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'min_interval_between_circles', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'max_count_times_for_full_circle', '50', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'min_count_items_per_one_time', '100', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'max_count_items_per_one_time', '500', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/defaults/inspector/circle/', 'last_time_start_circle', NULL, NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/feedbacks/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_wizard VALUES
  (1, 'migrationToV6', '*', 3, NULL, 1, 1),
  (2, 'installationEbay', 'ebay', 0, NULL, 1, 2),
  (3, 'installationCommon', 'common', 0, NULL, 1, 2),
  (4, 'amazon', 'common', 0, NULL, 0, 3),
  (5, 'buy', 'common', 0, NULL, 0, 4),
  (6, 'amazonNewAsin', 'common', 0, NULL, 0, 5),
  (7, 'buyNewSku', 'common', 0, NULL, 0, 6),
  (8, 'play', 'common', 0, NULL, 0, 7);

SQL
);

//#############################################

// EBAY SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS m2epro_ebay_account;
CREATE TABLE m2epro_ebay_account (
  account_id INT(11) UNSIGNED NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  token_session VARCHAR(255) NOT NULL,
  token_expired_date DATETIME NOT NULL,
  marketplaces_data TEXT DEFAULT NULL,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_mapping_mode TINYINT(2) NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_synchronization_mapped_items_mode TINYINT(2) NOT NULL DEFAULT 1,
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
  ebay_shipping_discount_profiles TEXT DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  messages_receive TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (account_id)
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
  is_leaf TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
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
  tax_categories LONGTEXT NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_dictionary_policy;
CREATE TABLE m2epro_ebay_dictionary_policy (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  api_name VARCHAR(255) NOT NULL,
  api_identifier VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX api_identifier (api_identifier),
  INDEX api_name (api_name),
  INDEX type (type)
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
  data LONGTEXT NOT NULL,
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
  ebay_transaction_id VARCHAR(20) NOT NULL,
  buyer_name VARCHAR(200) NOT NULL,
  buyer_feedback_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  buyer_feedback_text VARCHAR(255) NOT NULL,
  buyer_feedback_date DATETIME NOT NULL,
  buyer_feedback_type VARCHAR(20) NOT NULL,
  seller_feedback_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  seller_feedback_text VARCHAR(255) NOT NULL,
  seller_feedback_date DATETIME NOT NULL,
  seller_feedback_type VARCHAR(20) NOT NULL,
  last_response_attempt_date DATETIME DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX buyer_feedback_id (buyer_feedback_id),
  INDEX ebay_item_id (ebay_item_id),
  INDEX ebay_transaction_id (ebay_transaction_id),
  INDEX seller_feedback_id (seller_feedback_id)
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
  items_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  auto_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_global_adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_global_adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  auto_website_adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_website_adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  auto_website_deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_payment_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_payment_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_shipping_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_return_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_description_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_selling_format_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_synchronization_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_custom_id INT(11) UNSIGNED DEFAULT NULL,
  product_add_ids TEXT DEFAULT NULL,
  PRIMARY KEY (listing_id),
  INDEX auto_global_adding_mode (auto_global_adding_mode),
  INDEX auto_global_adding_template_category_id (auto_global_adding_template_category_id),
  INDEX auto_mode (auto_mode),
  INDEX auto_website_adding_mode (auto_website_adding_mode),
  INDEX auto_website_adding_template_category_id (auto_website_adding_template_category_id),
  INDEX auto_website_deleting_mode (auto_website_deleting_mode),
  INDEX items_sold_count (items_sold_count),
  INDEX products_sold_count (products_sold_count),
  INDEX template_description_custom_id (template_description_custom_id),
  INDEX template_description_id (template_description_id),
  INDEX template_description_mode (template_description_mode),
  INDEX template_payment_custom_id (template_payment_custom_id),
  INDEX template_payment_id (template_payment_id),
  INDEX template_payment_mode (template_payment_mode),
  INDEX template_payment_policy_id (template_payment_policy_id),
  INDEX template_return_custom_id (template_return_custom_id),
  INDEX template_return_id (template_return_id),
  INDEX template_return_mode (template_return_mode),
  INDEX template_return_policy_id (template_return_policy_id),
  INDEX template_selling_format_custom_id (template_selling_format_custom_id),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_selling_format_mode (template_selling_format_mode),
  INDEX template_shipping_custom_id (template_shipping_custom_id),
  INDEX template_shipping_id (template_shipping_id),
  INDEX template_shipping_mode (template_shipping_mode),
  INDEX template_shipping_policy_id (template_shipping_policy_id),
  INDEX template_synchronization_custom_id (template_synchronization_custom_id),
  INDEX template_synchronization_id (template_synchronization_id),
  INDEX template_synchronization_mode (template_synchronization_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_auto_category;
CREATE TABLE m2epro_ebay_listing_auto_category (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  group_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  adding_duplicate TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX adding_template_category_id (adding_template_category_id),
  INDEX category_id (category_id),
  INDEX group_id (group_id),
  INDEX listing_id (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_auto_category_group;
CREATE TABLE m2epro_ebay_listing_auto_category_group (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX listing_id (listing_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_auto_filter;
CREATE TABLE m2epro_ebay_listing_auto_filter (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  listing_id INT(11) UNSIGNED NOT NULL,
  rule_data TEXT NOT NULL,
  adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  adding_duplicate TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX adding_template_category_id (adding_template_category_id),
  INDEX listing_id (listing_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_other;
CREATE TABLE m2epro_ebay_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  old_items TEXT DEFAULT NULL,
  currency VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  online_qty_sold INT(11) UNSIGNED NOT NULL DEFAULT 0,
  online_bids INT(11) UNSIGNED DEFAULT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX currency (currency),
  INDEX end_date (end_date),
  INDEX item_id (item_id),
  INDEX online_bids (online_bids),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_product;
CREATE TABLE m2epro_ebay_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  template_category_id INT(11) UNSIGNED DEFAULT NULL,
  ebay_item_id INT(11) UNSIGNED DEFAULT NULL,
  online_start_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_reserve_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_buyitnow_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  online_bids INT(11) UNSIGNED DEFAULT NULL,
  online_category VARCHAR(255) DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  is_m2epro_listed_item TINYINT(2) UNSIGNED DEFAULT NULL,
  template_payment_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_payment_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_shipping_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_return_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_description_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_selling_format_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_synchronization_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_custom_id INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX ebay_item_id (ebay_item_id),
  INDEX end_date (end_date),
  INDEX is_m2epro_listed_item (is_m2epro_listed_item),
  INDEX online_bids (online_bids),
  INDEX online_buyitnow_price (online_buyitnow_price),
  INDEX online_category (online_category),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX online_reserve_price (online_reserve_price),
  INDEX online_start_price (online_start_price),
  INDEX start_date (start_date),
  INDEX template_category_id (template_category_id),
  INDEX template_description_custom_id (template_description_custom_id),
  INDEX template_description_id (template_description_id),
  INDEX template_description_mode (template_description_mode),
  INDEX template_payment_custom_id (template_payment_custom_id),
  INDEX template_payment_id (template_payment_id),
  INDEX template_payment_mode (template_payment_mode),
  INDEX template_payment_policy_id (template_payment_policy_id),
  INDEX template_return_custom_id (template_return_custom_id),
  INDEX template_return_id (template_return_id),
  INDEX template_return_mode (template_return_mode),
  INDEX template_return_policy_id (template_return_policy_id),
  INDEX template_selling_format_custom_id (template_selling_format_custom_id),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_selling_format_mode (template_selling_format_mode),
  INDEX template_shipping_custom_id (template_shipping_custom_id),
  INDEX template_shipping_id (template_shipping_id),
  INDEX template_shipping_mode (template_shipping_mode),
  INDEX template_shipping_policy_id (template_shipping_policy_id),
  INDEX template_synchronization_custom_id (template_synchronization_custom_id),
  INDEX template_synchronization_id (template_synchronization_id),
  INDEX template_synchronization_mode (template_synchronization_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_listing_product_variation;
CREATE TABLE m2epro_ebay_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  `add` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `delete` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (listing_product_variation_id),
  INDEX `add` (`add`),
  INDEX `delete` (`delete`),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX status (status)
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
  currency VARCHAR(10) NOT NULL DEFAULT 'USD',
  categories_version INT(11) UNSIGNED NOT NULL DEFAULT 0,
  is_multivariation TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_freight_shipping TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_calculated_shipping TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_tax TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_vat TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_local_shipping_rate_table TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_international_shipping_rate_table TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_english_measurement_system TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_metric_measurement_system TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_cash_on_delivery TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (marketplace_id),
  INDEX is_calculated_shipping (is_calculated_shipping),
  INDEX is_cash_on_delivery (is_cash_on_delivery),
  INDEX is_english_measurement_system (is_english_measurement_system),
  INDEX is_freight_shipping (is_freight_shipping),
  INDEX is_get_it_fast (is_get_it_fast),
  INDEX is_international_shipping_rate_table (is_international_shipping_rate_table),
  INDEX is_local_shipping_rate_table (is_local_shipping_rate_table),
  INDEX is_metric_measurement_system (is_metric_measurement_system),
  INDEX is_tax (is_tax),
  INDEX is_vat (is_vat)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_motor_specific;
CREATE TABLE m2epro_ebay_motor_specific (
  epid VARCHAR(255) NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  product_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  make VARCHAR(255) NOT NULL,
  model VARCHAR(255) NOT NULL,
  year SMALLINT(4) UNSIGNED NOT NULL,
  trim VARCHAR(255) DEFAULT NULL,
  engine VARCHAR(255) DEFAULT NULL,
  submodel VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (epid),
  INDEX engine (engine),
  INDEX make (make),
  INDEX model (model),
  INDEX product_type (product_type),
  INDEX submodel (submodel),
  INDEX trim (trim),
  INDEX year (year)
)
ENGINE = MYISAM
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
  checkout_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  checkout_buyer_message VARCHAR(500) DEFAULT NULL,
  payment_method VARCHAR(255) DEFAULT NULL,
  payment_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  payment_status_ebay VARCHAR(255) DEFAULT NULL,
  payment_status_hold VARCHAR(255) DEFAULT NULL,
  payment_date DATETIME DEFAULT NULL,
  shipping_method VARCHAR(255) DEFAULT NULL,
  shipping_method_selected TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_address TEXT NOT NULL,
  shipping_tracking_details VARCHAR(500) DEFAULT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_type VARCHAR(255) DEFAULT NULL,
  shipping_date DATETIME DEFAULT NULL,
  get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  paid_amount DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  saved_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  best_offer TINYINT(2) NOT NULL DEFAULT 0,
  final_fee DECIMAL(12, 4) UNSIGNED NOT NULL,
  tax_rate FLOAT NOT NULL DEFAULT 0,
  tax_state VARCHAR(255) DEFAULT NULL,
  tax_includes_shipping TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
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
  fee DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  sum DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
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
  transaction_id VARCHAR(20) NOT NULL,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(64) DEFAULT NULL,
  condition_display_name VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  buy_it_now_price DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  qty_purchased INT(11) UNSIGNED NOT NULL,
  variation TEXT DEFAULT NULL,
  auto_pay TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  listing_type VARCHAR(255) NOT NULL,
  unpaid_item_process_state TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (order_item_id),
  INDEX item_id (item_id),
  INDEX sku (sku),
  INDEX title (title),
  INDEX transaction_id (transaction_id),
  INDEX unpaid_item_process_state (unpaid_item_process_state)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_category;
CREATE TABLE m2epro_ebay_template_category (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_main_id INT(11) UNSIGNED NOT NULL,
  category_main_path VARCHAR(255) DEFAULT NULL,
  category_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  category_main_attribute VARCHAR(255) NOT NULL,
  category_secondary_id INT(11) UNSIGNED NOT NULL,
  category_secondary_path VARCHAR(255) DEFAULT NULL,
  category_secondary_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  category_secondary_attribute VARCHAR(255) NOT NULL,
  store_category_main_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_category_main_path VARCHAR(255) DEFAULT NULL,
  store_category_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  store_category_main_attribute VARCHAR(255) NOT NULL,
  store_category_secondary_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_category_secondary_path VARCHAR(255) DEFAULT NULL,
  store_category_secondary_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  store_category_secondary_attribute VARCHAR(255) NOT NULL,
  tax_category_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  tax_category_value VARCHAR(255) NOT NULL,
  tax_category_attribute VARCHAR(255) NOT NULL,
  variation_enabled TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  motors_specifics_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_category_specific;
CREATE TABLE m2epro_ebay_template_category_specific (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_category_id INT(11) UNSIGNED NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  mode_relation_id INT(11) UNSIGNED NOT NULL,
  attribute_id VARCHAR(255) NOT NULL,
  attribute_title VARCHAR(255) NOT NULL,
  value_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  value_ebay_recommended LONGTEXT DEFAULT NULL,
  value_custom_value VARCHAR(255) DEFAULT NULL,
  value_custom_attribute VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX template_category_id (template_category_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_description;
CREATE TABLE m2epro_ebay_template_description (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  subtitle_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subtitle_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value INT(11) UNSIGNED NOT NULL DEFAULT 0,
  condition_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_template TEXT NOT NULL,
  product_details TEXT DEFAULT NULL,
  cut_long_titles TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  hit_counter VARCHAR(255) NOT NULL,
  editor_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  enhancement VARCHAR(255) NOT NULL,
  gallery_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 4,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  gallery_images_limit TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  gallery_images_attribute VARCHAR(255) NOT NULL,
  variation_configurable_images VARCHAR(255) NOT NULL,
  use_supersize_images TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_image LONGBLOB DEFAULT NULL,
  watermark_settings TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_payment;
CREATE TABLE m2epro_ebay_template_payment (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  pay_pal_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  pay_pal_email_address VARCHAR(255) NOT NULL,
  pay_pal_immediate_payment TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_payment_service;
CREATE TABLE m2epro_ebay_template_payment_service (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_payment_id INT(11) UNSIGNED NOT NULL,
  code_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX template_payment_id (template_payment_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_policy;
CREATE TABLE m2epro_ebay_template_policy (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  api_name VARCHAR(255) NOT NULL,
  api_identifier VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_return;
CREATE TABLE m2epro_ebay_template_return (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  accepted VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  within VARCHAR(255) NOT NULL,
  shipping_cost VARCHAR(255) NOT NULL,
  restocking_fee VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_selling_format;
CREATE TABLE m2epro_ebay_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  listing_type TINYINT(2) UNSIGNED NOT NULL,
  listing_type_attribute VARCHAR(255) NOT NULL,
  listing_is_private TINYINT(2) UNSIGNED NOT NULL,
  duration_mode TINYINT(4) UNSIGNED NOT NULL,
  duration_attribute VARCHAR(255) NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
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
  INDEX is_custom_template (is_custom_template)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_shipping;
CREATE TABLE m2epro_ebay_template_shipping (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  country VARCHAR(255) NOT NULL,
  postal_code VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  vat_percent FLOAT UNSIGNED NOT NULL DEFAULT 0,
  dispatch_time INT(11) UNSIGNED NOT NULL DEFAULT 0,
  get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  tax_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_rate_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_rate_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_discount_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_combined_discount_profile_id VARCHAR(255) DEFAULT NULL,
  local_shipping_cash_on_delivery_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_cash_on_delivery_cost_value VARCHAR(255) NOT NULL,
  local_shipping_cash_on_delivery_cost_attribute VARCHAR(255) NOT NULL,
  international_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_discount_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_combined_discount_profile_id VARCHAR(255) DEFAULT NULL,
  international_trade TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX international_trade (international_trade),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_shipping_calculated;
CREATE TABLE m2epro_ebay_template_shipping_calculated (
  template_shipping_id INT(11) UNSIGNED NOT NULL,
  measurement_system TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  originating_postal_code VARCHAR(255) NOT NULL,
  package_size_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  package_size_value VARCHAR(500) NOT NULL,
  package_size_attribute VARCHAR(255) NOT NULL,
  dimension_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dimension_width_value VARCHAR(500) NOT NULL,
  dimension_width_attribute VARCHAR(255) NOT NULL,
  dimension_height_value VARCHAR(500) NOT NULL,
  dimension_height_attribute VARCHAR(255) NOT NULL,
  dimension_depth_value VARCHAR(500) NOT NULL,
  dimension_depth_attribute VARCHAR(255) NOT NULL,
  weight_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  weight_minor VARCHAR(500) NOT NULL,
  weight_major VARCHAR(500) NOT NULL,
  weight_attribute VARCHAR(255) NOT NULL,
  local_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_handling_cost_value VARCHAR(255) NOT NULL,
  local_handling_cost_attribute VARCHAR(255) NOT NULL,
  international_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_handling_cost_value VARCHAR(255) NOT NULL,
  international_handling_cost_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_shipping_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_shipping_service;
CREATE TABLE m2epro_ebay_template_shipping_service (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_shipping_id INT(11) UNSIGNED NOT NULL,
  shipping_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_value VARCHAR(255) NOT NULL,
  cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  cost_value VARCHAR(255) NOT NULL,
  cost_additional_value VARCHAR(255) NOT NULL,
  locations TEXT NOT NULL,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX priority (priority),
  INDEX template_shipping_id (template_shipping_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_ebay_template_synchronization;
CREATE TABLE m2epro_ebay_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value_mode TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  revise_change_category_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_payment_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_return_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_shipping_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_description_template TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_send_data TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  schedule_mode TINYINT(2) UNSIGNED NOT NULL,
  schedule_interval_settings TEXT NULL DEFAULT NULL,
  schedule_week_settings TEXT NULL DEFAULT NULL,
  PRIMARY KEY (template_synchronization_id),
  INDEX is_custom_template (is_custom_template)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/ebay/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/ebay/', 'expiration_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/ebay/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/ebay/', 'is_free', '0', '0 - No\r\n1 - Yes', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/component/ebay/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/ebay/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/order/settings/marketplace_8/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_synchronization_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/ebay/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/defaults/update_listings_products/', 'since_time', NULL, NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/defaults/update_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/', 'mode', '0', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/receive/', 'interval', '10800', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/receive/', 'last_access', NULL, 'date of last access',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/response/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/response/', 'interval', '10800', 'in seconds',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/response/', 'last_access', NULL, 'date of last access',
  '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/feedbacks/response/', 'attempt_interval', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/marketplaces/details/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/marketplaces/motors_specifics/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/marketplaces/motors_specifics/', 'part_size',  '10000',
   'amount of products per request', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/cancellation/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/cancellation/', 'interval', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/cancellation/', 'last_access', NULL, 'date of last access',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/cancellation/', 'start_date', NULL, 'date of first run',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/reserve_cancellation/', 'mode', '1', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/reserve_cancellation/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/orders/reserve_cancellation/', 'last_access', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/update/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listings/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/source/', 'qty', '1',
   '0 - none, \r\n1 - product qty, \r\n2 - custom attribute',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/source/', 'price', '1',
   '0 - none, \r\n1 - product price, \r\n2 - custom attribute, \r\n4 - special price',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/source/', 'title', '1',
   '0 - none, \r\n1 - product title, \r\n2 - custom attribute',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/source/', 'sub_title', '0',
   '0 - none, \r\n1 - product subtitle, \r\n2 - custom attribute',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/source/', 'description', '1',
   '0 - none, \r\n1 - product description, \r\n2 - custom attribute',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/revise/', 'revise_update_qty', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/revise/', 'revise_update_price', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/revise/', 'revise_update_title', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/revise/', 'revise_update_sub_title', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/revise/', 'revise_update_description', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_mode', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_filter_user_lock', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_send_data', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_status_enabled', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_is_in_stock', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_qty', '0',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_qty_value', '1',
   NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/relist/', 'relist_qty_value_max', '10',
   NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/stop/', 'stop_status_disabled', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/stop/', 'stop_out_off_stock', '1',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/stop/', 'stop_qty', '0',
   '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/stop/', 'stop_qty_value', '0',
   NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/other_listing/stop/', 'stop_qty_value_max', '0',
   NULL ,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/ebay/defaults/remove_unused_templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/defaults/remove_unused_templates/', 'interval', '86400', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/ebay/defaults/remove_unused_templates/', 'last_time', NULL, 'Last check time',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49');

INSERT INTO m2epro_marketplace VALUES
  (1, 0, 'United States', 'US', 'ebay.com', 0, 1, 'America', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (2, 2, 'Canada', 'Canada', 'ebay.ca', 0, 8, 'America', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (3, 3, 'United Kingdom', 'UK', 'ebay.co.uk', 0, 2, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (4, 15, 'Australia', 'Australia', 'ebay.com.au', 0, 4, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (5, 16, 'Austria', 'Austria', 'ebay.at', 0, 5, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (6, 23, 'Belgium (French)', 'Belgium_French', 'befr.ebay.be', 0, 7, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (7, 71, 'France', 'France', 'ebay.fr', 0, 10, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (8, 77, 'Germany', 'Germany', 'ebay.de', 0, 3, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (9, 100, 'eBay Motors', 'eBayMotors', 'motors.ebay.com', 0, 23, 'Other', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (10, 101, 'Italy', 'Italy', 'ebay.it', 0, 14, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (11, 123, 'Belgium (Dutch)', 'Belgium_Dutch', 'benl.ebay.be', 0, 6, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (12, 146, 'Netherlands', 'Netherlands', 'ebay.nl', 0, 16, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (13, 186, 'Spain', 'Spain', 'ebay.es', 0, 19, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (14, 193, 'Switzerland', 'Switzerland', 'ebay.ch', 0, 22, 'Europe', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (15, 201, 'Hong Kong', 'HongKong', 'ebay.com.hk', 0, 11, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (16, 203, 'India', 'India', 'ebay.in', 0, 12, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (17, 205, 'Ireland', 'Ireland', 'ebay.ie', 0, 13, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (18, 207, 'Malaysia', 'Malaysia', 'ebay.com.my', 0, 15, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (19, 210, 'Canada (French)', 'CanadaFrench', 'cafr.ebay.ca', 0, 9, 'America', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (20, 211, 'Philippines', 'Philippines', 'ebay.ph', 0, 17, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (21, 212, 'Poland', 'Poland', 'ebay.pl', 0, 18, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (22, 216, 'Singapore', 'Singapore', 'ebay.com.sg', 0, 20, 'Asia / Pacific', 'ebay', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (23, 218, 'Sweden', 'Sweden', 'ebay.se', 0, 21, 'Europe', 'ebay', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_ebay_marketplace VALUES
  (1, 'USD', 0, 1, 1, 1, 1, 0, 1, 0, 1, 1, 0, 0),
  (2, 'CAD', 0, 1, 0, 1, 1, 0, 0, 0, 0, 1, 1, 0),
  (3, 'GBP', 0, 1, 1, 0, 0, 1, 1, 1, 1, 0, 1, 0),
  (4, 'AUD', 0, 1, 1, 1, 0, 0, 0, 0, 1, 0, 1, 0),
  (5, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (6, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (7, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (8, 'EUR', 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 1, 0),
  (9, 'USD', 0, 1, 0, 1, 1, 0, 0, 0, 1, 1, 0, 0),
  (10, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 1),
  (11, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (12, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (13, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
  (14, 'CHF', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (15, 'HKD', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (16, 'USD', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
  (17, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (18, 'MYR', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (19, 'CAD', 0, 0, 0, 1, 1, 0, 0, 0, 0, 1, 1, 0),
  (20, 'PHP', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (21, 'PLN', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0),
  (22, 'SGD', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0),
  (23, 'SEK', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0);

SQL
);

//#############################################

// AMAZON SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS m2epro_amazon_account;
CREATE TABLE m2epro_amazon_account (
  account_id INT(11) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  merchant_id VARCHAR(255) NOT NULL,
  related_store_id INT(11) NOT NULL DEFAULT 0,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_move_settings VARCHAR(255) DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  info TEXT DEFAULT NULL,
  PRIMARY KEY (account_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_dictionary_category;
CREATE TABLE m2epro_amazon_dictionary_category (
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

DROP TABLE IF EXISTS m2epro_amazon_dictionary_marketplace;
CREATE TABLE m2epro_amazon_dictionary_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  nodes LONGTEXT NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_dictionary_specific;
CREATE TABLE m2epro_amazon_dictionary_specific (
  id INT(11) UNSIGNED NOT NULL,
  parent_id INT(11) UNSIGNED DEFAULT NULL,
  xsd_hash VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  xml_tag VARCHAR(255) NOT NULL,
  xpath VARCHAR(255) NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `values` TEXT DEFAULT NULL,
  recommended_values TEXT DEFAULT NULL,
  params TEXT DEFAULT NULL,
  data_definition TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS m2epro_amazon_item;
CREATE TABLE m2epro_amazon_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  variation_options TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS m2epro_amazon_listing;
CREATE TABLE m2epro_amazon_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  generate_sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  worldwide_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  worldwide_id_custom_attribute VARCHAR(255) NOT NULL,
  search_by_magento_title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value VARCHAR(2000) NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  handling_time_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  handling_time_value INT(11) UNSIGNED NOT NULL DEFAULT 1,
  handling_time_custom_attribute VARCHAR(255) NOT NULL,
  restock_date_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  restock_date_value DATETIME NOT NULL,
  restock_date_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (listing_id),
  INDEX generate_sku_mode (generate_sku_mode),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_synchronization_id (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_listing_other;
CREATE TABLE m2epro_amazon_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  general_id VARCHAR(255) NOT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
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
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_listing_product;
CREATE TABLE m2epro_amazon_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  template_new_product_id INT(11) UNSIGNED DEFAULT NULL,
  is_variation_product TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_variation_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id VARCHAR(255) DEFAULT NULL,
  general_id_search_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_search_suggest_data TEXT DEFAULT NULL,
  worldwide_id VARCHAR(255) DEFAULT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_sale_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  is_afn_channel TINYINT(2) UNSIGNED DEFAULT NULL,
  is_isbn_general_id TINYINT(2) UNSIGNED DEFAULT NULL,
  is_upc_worldwide_id TINYINT(2) UNSIGNED DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_search_status (general_id_search_status),
  INDEX is_afn_channel (is_afn_channel),
  INDEX is_isbn_general_id (is_isbn_general_id),
  INDEX is_upc_worldwide_id (is_upc_worldwide_id),
  INDEX is_variation_matched (is_variation_matched),
  INDEX is_variation_product (is_variation_product),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX online_sale_price (online_sale_price),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX template_new_product_id (template_new_product_id),
  INDEX worldwide_id (worldwide_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_listing_product_variation;
CREATE TABLE m2epro_amazon_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_listing_product_variation_option;
CREATE TABLE m2epro_amazon_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_marketplace;
CREATE TABLE m2epro_amazon_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  developer_key VARCHAR(255) DEFAULT NULL,
  default_currency VARCHAR(255) NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_order;
CREATE TABLE m2epro_amazon_order (
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

DROP TABLE IF EXISTS m2epro_amazon_order_item;
CREATE TABLE m2epro_amazon_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  amazon_order_item_id VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  general_id VARCHAR(255) DEFAULT NULL,
  is_isbn_general_id TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  price DECIMAL(12, 4) UNSIGNED NOT NULL,
  gift_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  gift_message VARCHAR(500) DEFAULT NULL,
  gift_type VARCHAR(255) DEFAULT NULL,
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

DROP TABLE IF EXISTS m2epro_amazon_processed_inventory;
CREATE TABLE m2epro_amazon_processed_inventory (
  hash VARCHAR(100) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  INDEX hash (hash),
  INDEX sku (sku)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_template_new_product;
CREATE TABLE m2epro_amazon_template_new_product (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  xsd_hash VARCHAR(255) NOT NULL,
  node_title VARCHAR(255) NOT NULL,
  category_path VARCHAR(255) NOT NULL,
  identifiers VARCHAR(255) NOT NULL,
  registered_parameter VARCHAR(25) DEFAULT NULL,
  worldwide_id_mode TINYINT(2) UNSIGNED DEFAULT 0,
  worldwide_id_custom_attribute VARCHAR(255) DEFAULT NULL,
  item_package_quantity_mode TINYINT(2) UNSIGNED DEFAULT 0,
  item_package_quantity_custom_value VARCHAR(255) DEFAULT NULL,
  item_package_quantity_custom_attribute VARCHAR(255) DEFAULT NULL,
  number_of_items_mode TINYINT(2) UNSIGNED DEFAULT 0,
  number_of_items_custom_value VARCHAR(255) DEFAULT NULL,
  number_of_items_custom_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  title VARCHAR(255) NOT NULL DEFAULT 'Default',
  PRIMARY KEY (id),
  INDEX marketplace_id (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_template_new_product_description;
CREATE TABLE m2epro_amazon_template_new_product_description (
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  brand_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  brand_template VARCHAR(255) NOT NULL,
  manufacturer_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_template VARCHAR(255) NOT NULL,
  manufacturer_part_number_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  manufacturer_part_number_custom_value VARCHAR(255) NOT NULL,
  manufacturer_part_number_custom_attribute VARCHAR(255) NOT NULL,
  package_weight_mode TINYINT(2) UNSIGNED DEFAULT 0,
  package_weight_custom_value VARCHAR(255) DEFAULT NULL,
  package_weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  shipping_weight_mode TINYINT(2) UNSIGNED DEFAULT 0,
  shipping_weight_custom_value VARCHAR(255) DEFAULT NULL,
  shipping_weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  shipping_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 1,
  shipping_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  shipping_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  package_weight_unit_of_measure_mode TINYINT(2) UNSIGNED DEFAULT 1,
  package_weight_unit_of_measure_custom_value VARCHAR(255) DEFAULT NULL,
  package_weight_unit_of_measure_custom_attribute VARCHAR(255) DEFAULT NULL,
  target_audience_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  target_audience_custom_value VARCHAR(255) NOT NULL,
  target_audience_custom_attribute VARCHAR(255) NOT NULL,
  search_terms_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  search_terms TEXT NOT NULL,
  bullet_points_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  bullet_points TEXT NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL,
  gallery_images_limit TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  gallery_images_attribute VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_template_new_product_specific;
CREATE TABLE m2epro_amazon_template_new_product_specific (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  xpath VARCHAR(255) NOT NULL,
  mode VARCHAR(25) NOT NULL,
  recommended_value VARCHAR(255) DEFAULT NULL,
  custom_value VARCHAR(255) DEFAULT NULL,
  custom_attribute VARCHAR(255) DEFAULT NULL,
  type VARCHAR(25) DEFAULT NULL,
  attributes TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX template_new_product_id (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_template_selling_format;
CREATE TABLE m2epro_amazon_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
  price_mode TINYINT(2) UNSIGNED NOT NULL,
  price_custom_attribute VARCHAR(255) NOT NULL,
  price_coefficient VARCHAR(255) NOT NULL,
  sale_price_mode TINYINT(2) UNSIGNED NOT NULL,
  sale_price_custom_attribute VARCHAR(255) NOT NULL,
  sale_price_coefficient VARCHAR(255) NOT NULL,
  price_variation_mode TINYINT(2) UNSIGNED NOT NULL,
  sale_price_start_date_mode TINYINT(2) UNSIGNED NOT NULL,
  sale_price_start_date_value DATETIME NOT NULL,
  sale_price_start_date_custom_attribute VARCHAR(255) NOT NULL,
  sale_price_end_date_mode TINYINT(2) UNSIGNED NOT NULL,
  sale_price_end_date_value DATETIME NOT NULL,
  sale_price_end_date_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX price_variation_mode (price_variation_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_amazon_template_synchronization;
CREATE TABLE m2epro_amazon_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value_mode TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/amazon/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/amazon/', 'expiration_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/amazon/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/amazon/', 'is_free', '0', '0 - No\r\n1 - Yes', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/amazon/', 'application_name', 'M2ePro - Amazon Magento Integration', NULL,
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/amazon/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/amazon/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/common/amazon/listing/', 'tutorial_shown', '0', '',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_synchronization_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/amazon/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/defaults/update_listings_products/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/defaults/update_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/marketplaces/details/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/marketplaces/specifics/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/reserve_cancellation/', 'mode', '1', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/reserve_cancellation/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/reserve_cancellation/', 'last_access', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/update/', 'mode', '1', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/other_listings/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/other_listings/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/other_listings/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/amazon/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_marketplace VALUES
  (24, 4, 'Canada', 'CA', 'amazon.ca', 0, 4, 'America', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (25, 3, 'Germany', 'DE', 'amazon.de', 0, 3, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (26, 5, 'France', 'FR', 'amazon.fr', 0, 5, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (27, 6, 'Japan', 'JP', 'amazon.co.jp', 0, 6, 'Asia / Pacific', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (28, 2, 'United Kingdom', 'UK', 'amazon.co.uk', 0, 2, 'Europe', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (29, 1, 'United States', 'US', 'amazon.com', 0, 1, 'America', 'amazon', '2013-05-08 00:00:00',
   '2013-05-08 00:00:00'),
  (30, 7, 'Spain', 'ES', 'amazon.es', 0, 7, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (31, 8, 'Italy', 'IT', 'amazon.it', 0, 8, 'Europe', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  (32, 9, 'China', 'CN', 'amazon.cn', 0, 9, 'Asia / Pacific', 'amazon', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

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

//#############################################

// BUY SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS m2epro_buy_account;
CREATE TABLE m2epro_buy_account (
  account_id INT(11) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  web_login VARCHAR(255) NOT NULL,
  ftp_login VARCHAR(255) NOT NULL,
  ftp_new_sku_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  ftp_inventory_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  ftp_orders_access TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  related_store_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_first_synchronization DATETIME DEFAULT NULL,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_move_settings VARCHAR(255) DEFAULT NULL,
  other_listings_update_titles_settings VARCHAR(255) DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  info TEXT DEFAULT NULL,
  PRIMARY KEY (account_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_dictionary_category;
CREATE TABLE m2epro_buy_dictionary_category (
  id INT(11) UNSIGNED NOT NULL,
  node_id INT(11) UNSIGNED NOT NULL,
  category_id VARCHAR(255) DEFAULT NULL,
  parent_id INT(11) UNSIGNED DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  is_listable TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  attributes LONGTEXT DEFAULT NULL,
  sorder INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  INDEX is_listable (is_listable),
  INDEX node_id (node_id),
  INDEX parent_id (parent_id),
  INDEX path (path),
  INDEX sorder (sorder),
  INDEX title (title)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_item;
CREATE TABLE m2epro_buy_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  variation_options TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS m2epro_buy_listing;
CREATE TABLE m2epro_buy_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  generate_sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  search_by_magento_title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value TEXT NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  shipping_standard_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_standard_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_standard_custom_attribute VARCHAR(255) NOT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_expedited_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_expedited_custom_attribute VARCHAR(255) NOT NULL,
  shipping_one_day_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_one_day_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_one_day_custom_attribute VARCHAR(255) NOT NULL,
  shipping_two_day_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_two_day_value DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_two_day_custom_attribute VARCHAR(255) NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (listing_id),
  INDEX generate_sku_mode (generate_sku_mode),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_synchronization_id (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_listing_other;
CREATE TABLE m2epro_buy_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  general_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition` TINYINT(4) UNSIGNED NOT NULL,
  condition_note VARCHAR(255) NOT NULL,
  shipping_standard_rate DECIMAL(12, 4) UNSIGNED NOT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED NOT NULL,
  shipping_expedited_rate DECIMAL(12, 4) UNSIGNED NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX `condition` (`condition`),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX shipping_expedited_mode (shipping_expedited_mode),
  INDEX shipping_expedited_rate (shipping_expedited_rate),
  INDEX shipping_standard_rate (shipping_standard_rate),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_listing_product;
CREATE TABLE m2epro_buy_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  template_new_product_id INT(11) UNSIGNED DEFAULT NULL,
  is_variation_product TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_variation_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id INT(11) UNSIGNED DEFAULT NULL,
  general_id_search_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_search_suggest_data TEXT DEFAULT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  online_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  `condition` TINYINT(4) UNSIGNED DEFAULT NULL,
  condition_note VARCHAR(255) DEFAULT NULL,
  shipping_standard_rate DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  shipping_expedited_mode TINYINT(2) UNSIGNED DEFAULT NULL,
  shipping_expedited_rate DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  ignore_next_inventory_synch TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX `condition` (`condition`),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_search_status (general_id_search_status),
  INDEX ignore_next_inventory_synch (ignore_next_inventory_synch),
  INDEX is_variation_matched (is_variation_matched),
  INDEX is_variation_product (is_variation_product),
  INDEX online_price (online_price),
  INDEX online_qty (online_qty),
  INDEX shipping_expedited_mode (shipping_expedited_mode),
  INDEX shipping_expedited_rate (shipping_expedited_rate),
  INDEX shipping_standard_rate (shipping_standard_rate),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX template_new_product_id (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_listing_product_variation;
CREATE TABLE m2epro_buy_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_listing_product_variation_option;
CREATE TABLE m2epro_buy_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_marketplace;
CREATE TABLE m2epro_buy_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_order;
CREATE TABLE m2epro_buy_order (
  order_id INT(11) UNSIGNED NOT NULL,
  seller_id INT(11) UNSIGNED NOT NULL,
  buy_order_id INT(11) UNSIGNED NOT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) DEFAULT NULL,
  billing_address TEXT NOT NULL,
  shipping_address TEXT NOT NULL,
  shipping_method VARCHAR(255) DEFAULT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL,
  paid_amount DECIMAL(12, 4) UNSIGNED NOT NULL,
  currency VARCHAR(10) NOT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX buy_order_id (buy_order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX paid_amount (paid_amount)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_order_item;
CREATE TABLE m2epro_buy_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  buy_order_item_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  general_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  tax_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  qty_shipped INT(11) UNSIGNED NOT NULL DEFAULT 0,
  qty_cancelled INT(11) UNSIGNED NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL,
  product_owed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_owed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  commission DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  shipping_fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  per_item_fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (order_item_id),
  INDEX buy_order_item_id (buy_order_item_id),
  INDEX general_id (general_id),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_template_new_product;
CREATE TABLE m2epro_buy_template_new_product (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT(11) UNSIGNED NOT NULL,
  node_title VARCHAR(255) NOT NULL,
  category_path VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  title VARCHAR(255) NOT NULL DEFAULT 'Default',
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_template_new_product_attribute;
CREATE TABLE m2epro_buy_template_new_product_attribute (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  attribute_name VARCHAR(255) NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  recommended_value TEXT DEFAULT NULL,
  custom_value TEXT DEFAULT NULL,
  custom_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX template_new_product_id (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_template_new_product_core;
CREATE TABLE m2epro_buy_template_new_product_core (
  template_new_product_id INT(11) UNSIGNED NOT NULL,
  seller_sku_custom_attribute VARCHAR(255) DEFAULT NULL,
  gtin_custom_attribute VARCHAR(255) DEFAULT NULL,
  isbn_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  isbn_custom_attribute VARCHAR(255) DEFAULT NULL,
  asin_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  asin_custom_attribute VARCHAR(255) DEFAULT NULL,
  mfg_name_template VARCHAR(255) NOT NULL,
  mfg_part_number_mode TINYINT(2) UNSIGNED NOT NULL,
  mfg_part_number_custom_value VARCHAR(255) DEFAULT NULL,
  mfg_part_number_custom_attribute VARCHAR(255) DEFAULT NULL,
  product_set_id_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  product_set_id_custom_value VARCHAR(255) DEFAULT NULL,
  product_set_id_custom_attribute VARCHAR(255) DEFAULT NULL,
  title_mode TINYINT(2) UNSIGNED NOT NULL,
  title_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL,
  description_template TEXT NOT NULL,
  main_image_mode TINYINT(2) UNSIGNED NOT NULL,
  main_image_attribute VARCHAR(255) NOT NULL,
  additional_images_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  additional_images_attribute VARCHAR(255) DEFAULT NULL,
  additional_images_limit INT(11) UNSIGNED DEFAULT NULL,
  keywords_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  keywords_custom_value VARCHAR(255) DEFAULT NULL,
  keywords_custom_attribute VARCHAR(255) DEFAULT NULL,
  features_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  features_template TEXT DEFAULT NULL,
  weight_mode TINYINT(4) DEFAULT NULL,
  weight_custom_value DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  weight_custom_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (template_new_product_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_template_selling_format;
CREATE TABLE m2epro_buy_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
  price_mode TINYINT(2) UNSIGNED NOT NULL,
  price_custom_attribute VARCHAR(255) NOT NULL,
  price_coefficient VARCHAR(255) NOT NULL,
  price_variation_mode TINYINT(2) UNSIGNED NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX price_variation_mode (price_variation_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_buy_template_synchronization;
CREATE TABLE m2epro_buy_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value_mode TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/buy/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/buy/', 'expiration_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/buy/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/buy/', 'is_free', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/component/buy/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/buy/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/common/buy/listing/', 'tutorial_shown', '0', '','2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/buy/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/defaults/update_listings_products/', 'mode', '1',
   '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/defaults/update_listings_products/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/marketplaces/categories/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/receive/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/receive/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/orders/update/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/other_listings/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/other_listings/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/other_listings/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/buy/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_marketplace VALUES
  (33, 0, 'United States', 'US', 'rakuten.com', 0, 1, 'America', 'buy', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_buy_marketplace VALUES
  (33);

SQL
);

//#############################################

// PLAY SCRIPT
$installer->run(<<<SQL

DROP TABLE IF EXISTS m2epro_play_account;
CREATE TABLE m2epro_play_account (
  account_id INT(11) UNSIGNED NOT NULL,
  server_hash VARCHAR(255) NOT NULL,
  login VARCHAR(255) NOT NULL,
  panel_mode VARCHAR(255) NOT NULL DEFAULT 'trade',
  related_store_id INT(11) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_synchronization TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  other_listings_first_synchronization DATETIME DEFAULT NULL,
  other_listings_mapping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_mapping_settings VARCHAR(255) DEFAULT NULL,
  other_listings_move_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  other_listings_move_settings VARCHAR(255) DEFAULT NULL,
  listings_update_links_settings VARCHAR(255) DEFAULT NULL,
  orders_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  orders_last_synchronization DATETIME DEFAULT NULL,
  magento_orders_settings TEXT NOT NULL,
  info TEXT DEFAULT NULL,
  PRIMARY KEY (account_id),
  INDEX panel_mode (panel_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_item;
CREATE TABLE m2epro_play_item (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) NOT NULL,
  product_id INT(11) UNSIGNED NOT NULL,
  store_id INT(11) UNSIGNED NOT NULL,
  variation_options TEXT DEFAULT NULL,
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

DROP TABLE IF EXISTS m2epro_play_listing;
CREATE TABLE m2epro_play_listing (
  listing_id INT(11) UNSIGNED NOT NULL,
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  sku_custom_attribute VARCHAR(255) NOT NULL,
  generate_sku_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_mode VARCHAR(255) NOT NULL,
  general_id_custom_attribute VARCHAR(255) NOT NULL,
  search_by_magento_title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  dispatch_to_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dispatch_to_value VARCHAR(255) NOT NULL,
  dispatch_to_custom_attribute VARCHAR(255) NOT NULL,
  dispatch_from_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dispatch_from_value VARCHAR(255) NOT NULL,
  shipping_price_gbr_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_price_gbr_value DECIMAL(12, 2) UNSIGNED NOT NULL,
  shipping_price_gbr_custom_attribute VARCHAR(255) NOT NULL,
  shipping_price_euro_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_price_euro_value DECIMAL(12, 2) UNSIGNED NOT NULL,
  shipping_price_euro_custom_attribute VARCHAR(255) NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value VARCHAR(255) NOT NULL,
  condition_custom_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_value TEXT NOT NULL,
  condition_note_custom_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (listing_id),
  INDEX generate_sku_mode (generate_sku_mode),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_synchronization_id (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_listing_other;
CREATE TABLE m2epro_play_listing_other (
  listing_other_id INT(11) UNSIGNED NOT NULL,
  general_id VARCHAR(20) NOT NULL,
  general_id_type VARCHAR(255) NOT NULL,
  play_listing_id INT(11) UNSIGNED NOT NULL,
  link_info VARCHAR(255) DEFAULT NULL,
  sku VARCHAR(255) NOT NULL,
  title VARCHAR(255) NOT NULL,
  dispatch_to VARCHAR(255) NOT NULL,
  dispatch_from VARCHAR(255) NOT NULL,
  online_price_gbr DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_price_euro DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  online_qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition` VARCHAR(255) NOT NULL,
  condition_note TEXT NOT NULL,
  start_date DATETIME NOT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_other_id),
  INDEX `condition` (`condition`),
  INDEX dispatch_from (dispatch_from),
  INDEX dispatch_to (dispatch_to),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_type (general_id_type),
  INDEX online_price_euro (online_price_euro),
  INDEX online_price_gbr (online_price_gbr),
  INDEX online_qty (online_qty),
  INDEX play_listing_id (play_listing_id),
  INDEX sku (sku),
  INDEX start_date (start_date),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_listing_product;
CREATE TABLE m2epro_play_listing_product (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  general_id VARCHAR(20) DEFAULT NULL,
  general_id_type VARCHAR(255) DEFAULT NULL,
  is_variation_product TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  is_variation_matched TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  play_listing_id INT(11) UNSIGNED DEFAULT NULL,
  link_info VARCHAR(255) DEFAULT NULL,
  general_id_search_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  general_id_search_suggest_data TEXT DEFAULT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  dispatch_to VARCHAR(255) DEFAULT NULL,
  dispatch_from VARCHAR(255) DEFAULT NULL,
  online_price_gbr DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_price_euro DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_shipping_price_gbr DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_shipping_price_euro DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  `condition` VARCHAR(255) DEFAULT NULL,
  condition_note VARCHAR(255) DEFAULT NULL,
  ignore_next_inventory_synch TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX `condition` (`condition`),
  INDEX dispatch_from (dispatch_from),
  INDEX dispatch_to (dispatch_to),
  INDEX end_date (end_date),
  INDEX general_id (general_id),
  INDEX general_id_search_status (general_id_search_status),
  INDEX general_id_type (general_id_type),
  INDEX ignore_next_inventory_synch (ignore_next_inventory_synch),
  INDEX is_variation_matched (is_variation_matched),
  INDEX is_variation_product (is_variation_product),
  INDEX online_price_euro (online_price_euro),
  INDEX online_price_gbr (online_price_gbr),
  INDEX online_qty (online_qty),
  INDEX online_shipping_price_euro (online_shipping_price_euro),
  INDEX online_shipping_price_gbr (online_shipping_price_gbr),
  INDEX play_listing_id (play_listing_id),
  INDEX sku (sku),
  INDEX start_date (start_date)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_listing_product_variation;
CREATE TABLE m2epro_play_listing_product_variation (
  listing_product_variation_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_listing_product_variation_option;
CREATE TABLE m2epro_play_listing_product_variation_option (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_marketplace;
CREATE TABLE m2epro_play_marketplace (
  marketplace_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (marketplace_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_order;
CREATE TABLE m2epro_play_order (
  order_id INT(11) UNSIGNED NOT NULL,
  play_order_id INT(11) UNSIGNED NOT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) DEFAULT NULL,
  status TINYINT(2) NOT NULL DEFAULT 0,
  shipping_status TINYINT(2) NOT NULL DEFAULT 0,
  shipping_address TEXT NOT NULL,
  shipping_price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  paid_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX paid_amount (paid_amount),
  INDEX play_order_id (play_order_id),
  INDEX shipping_status (shipping_status),
  INDEX status (status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_order_item;
CREATE TABLE m2epro_play_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  play_order_item_id INT(11) UNSIGNED NOT NULL,
  listing_id INT(11) UNSIGNED NOT NULL,
  sku VARCHAR(255) DEFAULT NULL,
  title VARCHAR(255) DEFAULT NULL,
  price DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  qty INT(11) UNSIGNED NOT NULL DEFAULT 0,
  currency VARCHAR(10) NOT NULL,
  fee DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  exchange_rate DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  proceed DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  PRIMARY KEY (order_item_id),
  INDEX listing_id (listing_id),
  INDEX sku (sku),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_processed_inventory;
CREATE TABLE m2epro_play_processed_inventory (
  hash VARCHAR(100) NOT NULL,
  sku VARCHAR(100) NOT NULL,
  INDEX hash (hash),
  INDEX sku (sku)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_template_selling_format;
CREATE TABLE m2epro_play_template_selling_format (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
  price_gbr_mode TINYINT(2) UNSIGNED NOT NULL,
  price_gbr_custom_attribute VARCHAR(255) NOT NULL,
  price_gbr_coefficient VARCHAR(255) NOT NULL,
  price_euro_mode TINYINT(2) UNSIGNED NOT NULL,
  price_euro_custom_attribute VARCHAR(255) NOT NULL,
  price_euro_coefficient VARCHAR(255) NOT NULL,
  price_variation_mode TINYINT(2) UNSIGNED NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX price_variation_mode (price_variation_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS m2epro_play_template_synchronization;
CREATE TABLE m2epro_play_template_synchronization (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value_mode TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_send_data TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (template_synchronization_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO m2epro_primary_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/M2ePro/license/play/', 'mode', '0', '0 - None\r\n1 - Trial\r\n2 - Live',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/play/', 'expiration_date', NULL, NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/play/', 'status', '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/M2ePro/license/play/', 'is_free', '0', NULL, '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/component/play/', 'mode', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/component/play/', 'allowed', '1', '0 - disable, \r\n1 - enable', '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/view/common/play/listing/', 'tutorial_shown', '0', '','2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
  ('/play/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/update_listings_products/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/update_listings_products/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/update_listings_products/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/update_listings_products/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/defaults/getting_products_links/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/marketplaces/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/orders/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/receive/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/receive/', 'interval', '3600', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/receive/', 'max_deactivate_time', '86400', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/update/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/update/', 'interval', '3600', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/orders/update/', 'max_deactivate_time', '86400', 'in seconds',
   '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
  ('/play/other_listings/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/other_listings/', 'max_deactivate_time', '86400', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/other_listings/', 'last_time', NULL, 'Last check time',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/other_listings/', 'interval', '3600', 'in seconds',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/templates/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/templates/list/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/templates/relist/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/templates/revise/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00'),
  ('/play/templates/stop/', 'mode', '1', '0 - disable, \r\n1 - enable',
   '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_marketplace VALUES
  (34, 0, 'United Kingdom', 'UK', 'play.com', 0, 1, 'Europe', 'play', '2013-05-08 00:00:00', '2013-05-08 00:00:00');

INSERT INTO m2epro_play_marketplace VALUES
  (34);

SQL
);

//#############################################

Mage::register('M2EPRO_IS_INSTALLATION',true);
$installer->endSetup();

//#############################################