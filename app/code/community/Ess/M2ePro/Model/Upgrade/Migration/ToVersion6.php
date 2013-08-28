<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion6
{
    const DEVELOPMENT = true;

    const PREFIX_TABLE_BACKUP = '__backup_v5';
    const PREFIX_TABLE_FROM = '';
    const PREFIX_TABLE_TO = '__to';

    private $prefixFrom = '';
    private $prefixTo = '';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //####################################

    public function __construct()
    {
        $this->prefixFrom = self::DEVELOPMENT ? self::PREFIX_TABLE_FROM : '';
        $this->prefixTo = self::DEVELOPMENT ? self::PREFIX_TABLE_TO : '';
    }

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //------------------------------------

    public function startSetup()
    {
        $this->installer->startSetup();
    }

    public function endSetup()
    {
        $this->installer->endSetup();
    }

    //####################################

    public function backup()
    {
        if (self::DEVELOPMENT) {
            $startTime = microtime(true);
        }

        !$this->checkToSkipStep('m2epro'.self::PREFIX_TABLE_BACKUP.'_translation_text') &&
            $this->backupOldVersionTables();


        if (self::DEVELOPMENT) {
            echo 'Total Backup Time: '.(string)round(microtime(true) - $startTime,2).'s.';
        }
    }

    public function migrate()
    {
        if (self::DEVELOPMENT) {
            $startTime = microtime(true);
        }

        $this->processConfig();
        $this->processSynchronizationConfig();
        $this->processCacheConfig();

        exit();

        $this->processAmazonAccountTable();

        $this->processEbayAccountStoreCategoryTable();

        $this->processListingTable();
        $this->processAmazonListingTable();
        $this->processBuyListingTable();
        $this->processPlayListingTable();
        // todo ebay listing table
        $this->processEbayListingTable();

        $this->processListingProductTable();
        $this->processAmazonListingProductTable();
        $this->processBuyListingProductTable();
        $this->processPlayListingProductTable();
        // todo ebay listing product table
        $this->processEbayListingProductTable();

        $this->processListingProductVariationTable();
        $this->processEbayListingProductVariationTable();

        // todo template description ?

        // todo template selling format
        $this->processTemplateSellingFormatTable();
        $this->processAmazonTemplateSellingFormatTable();
        $this->processBuyTemplateSellingFormatTable();
        $this->processPlayTemplateSellingFormatTable();
        // todo ebay template selling format
        $this->processEbayTemplateSellingFormatTable();

        $this->processTemplateSynchronizationTable();
        $this->processAmazonTemplateSynchronizationTable();
        $this->processBuyTemplateSynchronizationTable();
        $this->processPlayTemplateSynchronizationTable();
        // todo ebay template synchronization
        $this->processEbayTemplateSynchronizationTable();

        $this->processWizardTable();

        $this->processEbayMarketplaceTable();

        if (self::DEVELOPMENT) {
            echo 'Total Migration Time: '.(string)round(microtime(true) - $startTime,2).'s.';
        }
    }

    //####################################

    private function backupOldVersionTables()
    {
        $tempTables = array(
            'ess'.   $this->prefixFrom.'_config',
            'm2epro'.$this->prefixFrom.'_config',

            'm2epro'.$this->prefixFrom.'_amazon_account',
            'm2epro'.$this->prefixFrom.'_amazon_listing',
            'm2epro'.$this->prefixFrom.'_amazon_listing_product',
            'm2epro'.$this->prefixFrom.'_amazon_template_general',
            'm2epro'.$this->prefixFrom.'_amazon_template_selling_format',
            'm2epro'.$this->prefixFrom.'_amazon_template_synchronization',

            'm2epro'.$this->prefixFrom.'_buy_listing',
            'm2epro'.$this->prefixFrom.'_buy_listing_product',
            'm2epro'.$this->prefixFrom.'_buy_template_general',
            'm2epro'.$this->prefixFrom.'_buy_template_selling_format',
            'm2epro'.$this->prefixFrom.'_buy_template_synchronization',

            'm2epro'.$this->prefixFrom.'_ebay_account_store_category',
            'm2epro'.$this->prefixFrom.'_ebay_listing',
            'm2epro'.$this->prefixFrom.'_ebay_listing_product',
            'm2epro'.$this->prefixFrom.'_ebay_listing_product_variation',
            'm2epro'.$this->prefixFrom.'_ebay_marketplace',
            'm2epro'.$this->prefixFrom.'_ebay_template_description',
            'm2epro'.$this->prefixFrom.'_ebay_template_general',
            'm2epro'.$this->prefixFrom.'_ebay_template_general_calculated_shipping',
            'm2epro'.$this->prefixFrom.'_ebay_template_general_payment',
            'm2epro'.$this->prefixFrom.'_ebay_template_general_shipping',
            'm2epro'.$this->prefixFrom.'_ebay_template_general_specific',
            'm2epro'.$this->prefixFrom.'_ebay_template_selling_format',
            'm2epro'.$this->prefixFrom.'_ebay_template_synchronization',

            'm2epro'.$this->prefixFrom.'_listing',
            'm2epro'.$this->prefixFrom.'_listing_product',
            'm2epro'.$this->prefixFrom.'_listing_product_variation',

            'm2epro'.$this->prefixFrom.'_play_listing',
            'm2epro'.$this->prefixFrom.'_play_listing_product',
            'm2epro'.$this->prefixFrom.'_play_template_general',
            'm2epro'.$this->prefixFrom.'_play_template_selling_format',
            'm2epro'.$this->prefixFrom.'_play_template_synchronization',

            'm2epro'.$this->prefixFrom.'_template_description', // todo ask (sycnh date)
            'm2epro'.$this->prefixFrom.'_template_general',
            'm2epro'.$this->prefixFrom.'_template_selling_format', // todo ask (sycnh date)
            'm2epro'.$this->prefixFrom.'_template_synchronization', // todo ask (sycnh date)
        );

        foreach ($tempTables as $oldTable) {

            $newTable = str_replace('|ess'.$this->prefixFrom,'|ess'.self::PREFIX_TABLE_BACKUP,'|'.$oldTable);
            $newTable = str_replace('|m2epro'.$this->prefixFrom,'|m2epro'.self::PREFIX_TABLE_BACKUP,$newTable);

            $newTable = ltrim($newTable,'|');

            $oldTable = $this->installer->getTable($oldTable);
            $newTable = $this->installer->getTable($newTable);

            $this->installer->getConnection()->query("DROP TABLE IF EXISTS `{$newTable}`");
            $this->installer->getConnection()->query("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
        }
    }

    //####################################

    private function checkToSkipStep($nextTable)
    {
        $nextTable = $this->installer->getTable($nextTable);
        return (bool)$this->installer->tableExists($nextTable);
    }

    //####################################

    private function processConfig()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
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

SQL
);

        $oldData = $this->installer->getConnection()->query(<<<SQL
SELECT *
FROM `{$oldTable}`
SQL
)->fetchAll();

        $newData = array();

        // settings, where only group name (or nothing at all) has been changed
        $groupConversion = array(
            '/feedbacks/notification/' => '/view/ebay/feedbacks/notification/', // (mode & last_check)
            '/cron/notification/' => '/view/ebay/cron/notification/', // (mode & max_inactive_hours)
            '/cron/error/' => '/view/common/cron/error/', // (mode & max_inactive_hours)
            '/logs/cleaning/listings/' => '/logs/cleaning/listings/',
            '/logs/cleaning/other_listings/' => '/logs/cleaning/other_listings/',
            '/logs/cleaning/orders/' => '/logs/cleaning/orders/',
            '/logs/cleaning/synchronizations/' => '/logs/cleaning/synchronizations/',
            '/license/validation/domain/notification/' => '/license/validation/domain/notification/',
            '/license/validation/ip/notification/' => '/license/validation/ip/notification/',
            '/license/validation/directory/notification/' => '/license/validation/directory/notification/',
            '/component/ebay/' => '/component/ebay/', // (mode, allowed)
            '/component/amazon/' => '/component/amazon/', // (mode, allowed)
            '/component/buy/' => '/component/buy/', // (mode, allowed)
            '/component/play/' => '/component/play/', // (mode, allowed)
            '/amazon/' => '/amazon/', // (application name)
            '/debug/exceptions/' => '/debug/exceptions/', // (send_to_server)
            '/debug/fatal_error/' => '/debug/fatal_error/', // (send_to_server)
            '/debug/maintenance/' => '/debug/maintenance/', // (mode, restore_date)
            '/renderer/description/' => '/renderer/description/', // (convert_linebreaks)
            '/autocomplete/' => '/autocomplete/', // (max_records_quantity)
            '/cron/task/listings/' => '/cron/task/listings/', // (mode, interval, last_access)
            '/cron/task/other_listings/' => '/cron/task/other_listings/', // (mode, interval, last_access)
            '/cron/task/orders/' => '/cron/task/orders/', // (mode, interval, last_access)
            '/cron/task/synchronizations/' => '/cron/task/synchronizations/', // (mode, interval, last_access)
            '/cron/' => '/cron/', // (mode, last_access, double_run_protection)
            '/other/paypal/' => '/other/paypal/', // (url)
            '/listings/lockItem/' => '/listings/lockItem/', // (max_deactivate_time)
            '/logs/listings/' => '/logs/listings/', // (last_action_id)
            '/logs/other_listings/' => '/logs/other_listings/', // (last_action_id)
            '/product/index/cataloginventory_stock/' => '/product/index/cataloginventory_stock/', // (disabled)
            '/product/index/' => '/product/index/', // (mode)
            '/order/magento/settings/' => '/order/magento/settings/', // (create_with_first_product_options_when_variation_unavailable)
        );

        foreach ($oldData as $oldRow) {
            $newRow = NULL;
            $oldRow['id'] = NULL;

            // notices & thumbnails
            //------------------------------
            if ($oldRow['group'] == '/block_notices/settings/' && $oldRow['key'] == 'show') {
                $newRow = $oldRow;
                $newRow['group'] = '/view/';
                $newRow['key'] = 'show_block_notices';
            }

            if ($oldRow['group'] == '/products/settings/' && $oldRow['key'] == 'show_thumbnails') {
                $newRow = $oldRow;
                $newRow['group'] = '/view/';
                $newRow['key'] = 'show_products_thumbnails';
            }
            //------------------------------

            // default component
            //------------------------------
            if ($oldRow['group'] == '/component/' && $oldRow['key'] == 'default') {
                if ($oldRow['value'] == 'ebay') {
                    // todo
                }

                $newRow = $oldRow;
                $newRow['group'] = '/view/common/component/';
            }
            //------------------------------

            // /ebay|amazon/order/settings/marketplace_%id%/ (use_first_street_line_as_company)
            //------------------------------
            if (stripos($oldRow['group'], 'order/settings/marketplace_') !== false) {
                $newRow = $oldRow;
            }
            //------------------------------

            //------------------------------
            if (isset($groupConversion[$oldRow['group']])) {
                $newRow = $oldRow;
                $newRow['group'] = $groupConversion[$oldRow['group']];
            }
            //------------------------------

            if (!is_null($newRow)) {
                $newData[] = $newRow;
            }
        }

        // urls (documentation, uservoice, etc)
        // /view/ebay/template/category/

        $this->installer->getConnection()->query("TRUNCATE {$newTable}");
        $this->installer->getConnection()->insertMultiple($newTable, $newData);

        $this->installer->getConnection()->query(<<<SQL
INSERT INTO `{$newTable}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/view/ebay/template/category/', 'mode', 0, '', '', ''),
('/support/', 'documentation_url', '', '', '', ''),
('/support/', 'video_tutorials_url', '', '', '', ''),
('/support/', 'knowledge_base_url', '', '', '', ''),
('/support/', 'clients_portal_url', '', '', '', ''),
('/support/', 'main_website_url', '', '', '', ''),
('/support/', 'main_support_url', '', '', '', ''),
('/support/', 'magento_connect_url', '', '', '', ''),
('/support/', 'contact_email', '', '', '', ''),
('/support/uservoice/', 'api_url', '', '', '', ''),
('/support/uservoice/', 'api_client_key', '', '', '', '');
SQL
);
    }

    private function processSynchronizationConfig()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_synchronization_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
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

SQL
);

        $data = $this->installer->getConnection()->query(<<<SQL
SELECT *
FROM `{$oldTable}`
WHERE `group` LIKE '%synchronization/settings/%'
SQL
)->fetchAll();

        foreach ($data as &$item) {
            $item['id'] = NULL;

            if ($item['group'] == '/synchronization/settings/' && $item['key'] == 'mode') {
                $item['group'] = '/global/';
            } else {
                $item['group'] = str_replace('synchronization/settings/', '', $item['group']);
            }
        }

        $this->installer->getConnection()->insertMultiple($newTable, $data);
    }

    private function processCacheConfig()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_cache_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
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

SQL
);

        $whereConditions = array(
            array(
                'group' => '/cache/amazon/listing/',
                'key' => 'tutorial_shown'
            ),
            array(
                'group' => '/cache/buy/listing/',
                'key' => 'tutorial_shown'
            ),
            array(
                'group' => '/cache/play/listing/',
                'key' => 'tutorial_shown'
            ),
            array(
                'group' => '/cache/location_info/'
            ),
            array(
                'group' => '/cache/servicing/'
            ),
            array(
                'group' => '/backup/settings/'
            )
        );

        $whereSql = '';
        foreach ($whereConditions as $where) {
            $currentWhereSql = '';

            $currentWhereSql .= ' `group` = ' . $this->installer->getConnection()->quote($where['group']) . ' ';
            if (!empty($where['key'])) {
                $currentWhereSql .= ' AND `key` = ' . $this->installer->getConnection()->quote($where['key']) . ' ';
            }

            if ($whereSql) {
                $whereSql .= ' OR ';
            }

            $whereSql .= '(' . $currentWhereSql . ')';
        }

        $data = $this->installer->getConnection()->query(<<<SQL
SELECT *
FROM `{$oldTable}`
WHERE {$whereSql}
SQL
)->fetchAll();

        if (empty($data)) {
            return;
        }

        foreach ($data as &$item) {
            $item['group'] = str_replace('/cache', '', $item['group']);
        }

        $this->installer->getConnection()->insertMultiple($newTable, $data);
    }

    private function processPrimaryConfig()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_primary_config');
        $oldTable = $this->installer->getTable('ess'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
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

INSERT `{$newTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    //####################################

    private function processAmazonAccountTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_amazon_account');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_account');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `account_id` int(11) unsigned NOT NULL,
  `server_hash` varchar(255) NOT NULL,
  `marketplace_id` int(11) unsigned NOT NULL,
  `merchant_id` varchar(255) NOT NULL,
  `related_store_id` int(11) NOT NULL DEFAULT '0',
  `other_listings_synchronization` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `other_listings_mapping_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `other_listings_mapping_settings` varchar(255) DEFAULT NULL,
  `other_listings_move_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `other_listings_move_settings` varchar(255) DEFAULT NULL,
  `orders_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `orders_last_synchronization` datetime DEFAULT NULL,
  `magento_orders_settings` text NOT NULL,
  `info` text,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
        $select = $this->installer->getConnection()->select()->from($oldTable,'*');

        foreach ($this->installer->getConnection()->fetchAll($select) as $row) {

            $row['marketplaces_data'] = json_decode($row['marketplaces_data'], true);
            $row['marketplace_id'] = key($row['marketplaces_data']);
            $row['server_hash'] = $row['marketplaces_data'][$row['marketplace_id']]['server_hash'];
            $row['merchant_id'] = $row['marketplaces_data'][$row['marketplace_id']]['merchant_id'];
            $row['related_store_id'] = $row['marketplaces_data'][$row['marketplace_id']]['related_store_id'];
            $row['info'] = json_encode($row['marketplaces_data'][$row['marketplace_id']]['info']);
            unset($row['marketplaces_data']);

            $this->installer->getConnection()->insert($newTable, $row);
        }

    }

    //####################################

    private function processListingTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned NOT NULL,
  `marketplace_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `products_total_count` int(11) unsigned NOT NULL DEFAULT '0',
  `products_active_count` int(11) unsigned NOT NULL DEFAULT '0',
  `products_inactive_count` int(11) unsigned NOT NULL DEFAULT '0',
  `items_active_count` int(11) unsigned NOT NULL DEFAULT '0',
  `source_products` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `categories_add_action` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `categories_delete_action` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `component_mode` (`component_mode`),
  KEY `marketplace_id` (`marketplace_id`),
  KEY `store_id` (`store_id`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $tmpTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`id`,
       `{$tmpTable}`.`account_id`,
       `{$tmpTable}`.`marketplace_id`,
       `{$oldTable}`.`title`,
       `{$oldTable}`.`store_id`,
       `{$oldTable}`.`products_total_count`,
       `{$oldTable}`.`products_active_count`,
       `{$oldTable}`.`products_inactive_count`,
       `{$oldTable}`.`items_active_count`,
       `{$oldTable}`.`source_products`,
       `{$oldTable}`.`categories_add_action`,
       `{$oldTable}`.`categories_delete_action`,
       `{$oldTable}`.`component_mode`,
       `{$oldTable}`.`update_date`,
       `{$oldTable}`.`create_date`
FROM `{$oldTable}`
INNER JOIN {$tmpTable}
ON {$tmpTable}.id = {$oldTable}.template_general_id;

SQL
);

    }

    //----------------------------------------------

    private function processAmazonListingTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_amazon_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `worldwide_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `worldwide_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` varchar(2000) NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  `handling_time_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `handling_time_value` int(11) unsigned NOT NULL DEFAULT '1',
  `handling_time_custom_attribute` varchar(255) NOT NULL,
  `restock_date_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `restock_date_value` datetime NOT NULL,
  `restock_date_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_mode`,
       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`worldwide_id_mode`,
       `{$templateGeneralTable}`.`worldwide_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`,
       `{$templateGeneralTable}`.`handling_time_mode`,
       `{$templateGeneralTable}`.`handling_time_value`,
       `{$templateGeneralTable}`.`handling_time_custom_attribute`,
       `{$templateGeneralTable}`.`restock_date_mode`,
       `{$templateGeneralTable}`.`restock_date_value`,
       `{$templateGeneralTable}`.`restock_date_custom_attribute`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    //----------------------------------------------

    private function processBuyListingTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_buy_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` text NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  `shipping_standard_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_standard_value` decimal(12,4) unsigned NOT NULL,
  `shipping_standard_custom_attribute` varchar(255) NOT NULL,
  `shipping_expedited_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_expedited_value` decimal(12,4) unsigned NOT NULL,
  `shipping_expedited_custom_attribute` varchar(255) NOT NULL,
  `shipping_one_day_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_one_day_value` decimal(12,4) unsigned NOT NULL,
  `shipping_one_day_custom_attribute` varchar(255) NOT NULL,
  `shipping_two_day_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_two_day_value` decimal(12,4) unsigned NOT NULL,
  `shipping_two_day_custom_attribute` varchar(255) NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_standard_mode`,
       `{$templateGeneralTable}`.`shipping_standard_value`,
       `{$templateGeneralTable}`.`shipping_standard_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_expedited_mode`,
       `{$templateGeneralTable}`.`shipping_expedited_value`,
       `{$templateGeneralTable}`.`shipping_expedited_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_one_day_mode`,
       `{$templateGeneralTable}`.`shipping_one_day_value`,
       `{$templateGeneralTable}`.`shipping_one_day_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_two_day_mode`,
       `{$templateGeneralTable}`.`shipping_two_day_value`,
       `{$templateGeneralTable}`.`shipping_two_day_custom_attribute`,
       `{$templateGeneralTable}`.`sku_mode`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    //----------------------------------------------

    private function processPlayListingTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_play_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` varchar(255) NOT NULL,
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `dispatch_to_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `dispatch_to_value` varchar(255) NOT NULL,
  `dispatch_to_custom_attribute` varchar(255) NOT NULL,
  `dispatch_from_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `dispatch_from_value` varchar(255) NOT NULL,
  `shipping_price_gbr_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_price_gbr_value` decimal(12,2) unsigned NOT NULL,
  `shipping_price_gbr_custom_attribute` varchar(255) NOT NULL,
  `shipping_price_euro_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_price_euro_value` decimal(12,2) unsigned NOT NULL,
  `shipping_price_euro_custom_attribute` varchar(255) NOT NULL,
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` text NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_mode`,
       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`dispatch_to_mode`,
       `{$templateGeneralTable}`.`dispatch_to_value`,
       `{$templateGeneralTable}`.`dispatch_to_custom_attribute`,
       `{$templateGeneralTable}`.`dispatch_from_mode`,
       `{$templateGeneralTable}`.`dispatch_from_value`,
       `{$templateGeneralTable}`.`shipping_price_gbr_mode`,
       `{$templateGeneralTable}`.`shipping_price_gbr_value`,
       `{$templateGeneralTable}`.`shipping_price_gbr_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_price_euro_mode`,
       `{$templateGeneralTable}`.`shipping_price_euro_value`,
       `{$templateGeneralTable}`.`shipping_price_euro_custom_attribute`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    //----------------------------------------------

    private function processEbayListingTable()
    {
        // todo ebay listing table
    }

    //####################################

    private function processListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status_changer` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `component_mode` varchar(10) DEFAULT NULL,
  `additional_data` text,
  `tried_to_list` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `listing_id` (`listing_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  KEY `status_changer` (`status_changer`),
  KEY `tried_to_list` (`tried_to_list`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //------------------------------------

    private function processAmazonListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_amazon_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `template_new_product_id` int(11) unsigned DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id` varchar(255) DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `worldwide_id` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_sale_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `is_afn_channel` tinyint(2) unsigned DEFAULT NULL,
  `is_isbn_general_id` tinyint(2) unsigned DEFAULT NULL,
  `is_upc_worldwide_id` tinyint(2) unsigned DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `is_afn_channel` (`is_afn_channel`),
  KEY `is_isbn_general_id` (`is_isbn_general_id`),
  KEY `is_upc_worldwide_id` (`is_upc_worldwide_id`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `online_sale_price` (`online_sale_price`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `template_new_product_id` (`template_new_product_id`),
  KEY `worldwide_id` (`worldwide_id`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `template_new_product_id`,
       `is_variation_product`,
       `is_variation_matched`,
       `general_id`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `worldwide_id`,
       `sku`,
       `online_price`,
       `online_sale_price`,
       `online_qty`,
       `is_afn_channel`,
       `is_isbn_general_id`,
       `is_upc_worldwide_id`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);

    }

    //------------------------------------

    private function processBuyListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_buy_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `template_new_product_id` int(11) unsigned DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id` int(11) unsigned DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `sku` varchar(255) DEFAULT NULL,
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `condition` tinyint(4) unsigned DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `shipping_standard_rate` decimal(12,4) unsigned DEFAULT NULL,
  `shipping_expedited_mode` tinyint(2) unsigned DEFAULT NULL,
  `shipping_expedited_rate` decimal(12,4) unsigned DEFAULT NULL,
  `ignore_next_inventory_synch` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `condition` (`condition`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `ignore_next_inventory_synch` (`ignore_next_inventory_synch`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `shipping_expedited_mode` (`shipping_expedited_mode`),
  KEY `shipping_expedited_rate` (`shipping_expedited_rate`),
  KEY `shipping_standard_rate` (`shipping_standard_rate`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `template_new_product_id` (`template_new_product_id`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `template_new_product_id`,
       `is_variation_product`,
       `is_variation_matched`,
       `general_id`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `sku`,
       `online_price`,
       `online_qty`,
       `condition`,
       `condition_note`,
       `shipping_standard_rate`,
       `shipping_expedited_mode`,
       `shipping_expedited_rate`,
       `ignore_next_inventory_synch`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);

    }

    //------------------------------------

    private function processPlayListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_play_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `general_id` varchar(20) DEFAULT NULL,
  `general_id_type` varchar(255) DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `play_listing_id` int(11) unsigned DEFAULT NULL,
  `link_info` varchar(255) DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `sku` varchar(255) DEFAULT NULL,
  `dispatch_to` varchar(255) DEFAULT NULL,
  `dispatch_from` varchar(255) DEFAULT NULL,
  `online_price_gbr` decimal(12,4) unsigned DEFAULT NULL,
  `online_price_euro` decimal(12,4) unsigned DEFAULT NULL,
  `online_shipping_price_gbr` decimal(12,4) unsigned DEFAULT NULL,
  `online_shipping_price_euro` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `condition` varchar(255) DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `ignore_next_inventory_synch` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `condition` (`condition`),
  KEY `dispatch_from` (`dispatch_from`),
  KEY `dispatch_to` (`dispatch_to`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `general_id_type` (`general_id_type`),
  KEY `ignore_next_inventory_synch` (`ignore_next_inventory_synch`),
  KEY `online_price_euro` (`online_price_euro`),
  KEY `online_price_gbr` (`online_price_gbr`),
  KEY `online_qty` (`online_qty`),
  KEY `online_shipping_price_euro` (`online_shipping_price_euro`),
  KEY `online_shipping_price_gbr` (`online_shipping_price_gbr`),
  KEY `play_listing_id` (`play_listing_id`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `general_id`,
       `general_id_type`,
       `is_variation_product`,
       `is_variation_matched`,
       `play_listing_id`,
       `link_info`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `sku`,
       `dispatch_to`,
       `dispatch_from`,
       `online_price_gbr`,
       `online_price_euro`,
       `online_shipping_price_gbr`,
       `online_shipping_price_euro`,
       `online_qty`,
       `condition`,
       `condition_note`,
       `ignore_next_inventory_synch`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);

    }

    //------------------------------------

    private function processEbayListingProductTable()
    {
        // todo ebay listing product table
    }

    //####################################

    private function processListingProductVariationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product_variation');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product_variation');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `listing_product_id` int(11) unsigned NOT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `listing_product_id` (`listing_product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `id`,
       `listing_product_id`,
       `component_mode`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    //-----------------------------------

    private function processEbayListingProductVariationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_listing_product_variation');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listing_product_variation');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_variation_id` int(11) unsigned NOT NULL,
  `add` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `delete` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `online_qty_sold` int(11) unsigned DEFAULT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`listing_product_variation_id`),
  KEY `add` (`add`),
  KEY `delete` (`delete`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `online_qty_sold` (`online_qty_sold`),
  KEY `status` (`status`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $tmpTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product_variation');
        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_product_variation_id`,
       `{$tmpTable}`.`add`,
       `{$tmpTable}`.`delete`,
       `{$oldTable}`.`online_price`,
       `{$oldTable}`.`online_qty`,
       `{$oldTable}`.`online_qty_sold`,
       `{$tmpTable}`.`status`
FROM `{$oldTable}`
INNER JOIN `{$tmpTable}`
ON `{$tmpTable}`.`id` = `{$oldTable}`.`listing_product_variation_id`;

SQL
);
    }

    //####################################

    private function processTemplateSellingFormatTable()
    {
        // todo template selling format
    }

    //------------------------------------

    private function processAmazonTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_amazon_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `currency` varchar(50) NOT NULL,
  `price_mode` tinyint(2) unsigned NOT NULL,
  `price_custom_attribute` varchar(255) NOT NULL,
  `price_coefficient` varchar(255) NOT NULL,
  `sale_price_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_custom_attribute` varchar(255) NOT NULL,
  `sale_price_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_start_date_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_start_date_value` datetime NOT NULL,
  `sale_price_start_date_custom_attribute` varchar(255) NOT NULL,
  `sale_price_end_date_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_end_date_value` datetime NOT NULL,
  `sale_price_end_date_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `currency`,
       `price_mode`,
       `price_custom_attribute`,
       `price_coefficient`,
       `sale_price_mode`,
       `sale_price_custom_attribute`,
       `sale_price_coefficient`,
       `price_variation_mode`,
       `sale_price_start_date_mode`,
       `sale_price_start_date_value`,
       `sale_price_start_date_custom_attribute`,
       `sale_price_end_date_mode`,
       `sale_price_end_date_value`,
       `sale_price_end_date_custom_attribute`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processBuyTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_buy_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `price_mode` tinyint(2) unsigned NOT NULL,
  `price_custom_attribute` varchar(255) NOT NULL,
  `price_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `price_mode`,
       `price_custom_attribute`,
       `price_coefficient`,
       `price_variation_mode`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processPlayTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_play_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `price_gbr_mode` tinyint(2) unsigned NOT NULL,
  `price_gbr_custom_attribute` varchar(255) NOT NULL,
  `price_gbr_coefficient` varchar(255) NOT NULL,
  `price_euro_mode` tinyint(2) unsigned NOT NULL,
  `price_euro_custom_attribute` varchar(255) NOT NULL,
  `price_euro_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `price_gbr_mode`,
       `price_gbr_custom_attribute`,
       `price_gbr_coefficient`,
       `price_euro_mode`,
       `price_euro_custom_attribute`,
       `price_euro_coefficient`,
       `price_variation_mode`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateSellingFormatTable()
    {
        // todo ebay template selling format
    }

    //####################################

    private function processTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `revise_change_listing` tinyint(2) unsigned NOT NULL,
  `revise_change_selling_format_template` tinyint(2) unsigned NOT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `revise_change_listing` (`revise_change_listing`),
  KEY `revise_change_selling_format_template` (`revise_change_selling_format_template`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `id`,
       `title`,
       `revise_change_general_template`,
       `revise_change_selling_format_template`,
       `component_mode`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processAmazonTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_amazon_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processBuyTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_buy_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processPlayTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_play_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateSynchronizationTable()
    {
        // todo
    }

    //####################################

    private function processWizardTable()
    {
        // todo, orm model

        $newWizardTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_wizard');
        $configTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newWizardTable};
CREATE TABLE {$newWizardTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `status` int(11) unsigned NOT NULL,
  `step` varchar(255) DEFAULT NULL,
  `priority` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/main/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/main/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'installationCommon',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 1
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/ebay/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/ebay/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'installationEbay',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 1
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazon/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazon/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'amazon',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 2
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buy/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buy/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'buy',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 3
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazonNewAsin/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazonNewAsin/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'amazonNewAsin',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 4
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buyNewSku/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buyNewSku/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'buyNewSku',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 5
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/play/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/play/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'play',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'priority' => 6
        ));

        //----------------------------------------

    }

    //####################################

    private function processEbayAccountStoreCategoryTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_account_store_category');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_account_store_category');

        // todo is leaf

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `account_id` int(11) unsigned NOT NULL,
  `category_id` decimal(20,0) unsigned NOT NULL,
  `parent_id` decimal(20,0) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `is_leaf` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sorder` int(11) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`category_id`),
  KEY `parent_id` (`parent_id`),
  KEY `sorder` (`sorder`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

    }

    //####################################

    private function processEbayMarketplaceTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_marketplace');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `marketplace_id` int(11) unsigned NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `categories_version` int(11) unsigned NOT NULL DEFAULT '0',
  `is_multivariation` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_freight_shipping` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_calculated_shipping` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_tax` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_vat` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_local_shipping_rate_table` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_international_shipping_rate_table` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_get_it_fast` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_english_measurement_system` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_cash_on_delivery` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`marketplace_id`),
  KEY `is_calculated_shipping` (`is_calculated_shipping`),
  KEY `is_cash_on_delivery` (`is_cash_on_delivery`),
  KEY `is_english_measurement_system` (`is_english_measurement_system`),
  KEY `is_freight_shipping` (`is_freight_shipping`),
  KEY `is_get_it_fast` (`is_get_it_fast`),
  KEY `is_international_shipping_rate_table` (`is_international_shipping_rate_table`),
  KEY `is_local_shipping_rate_table` (`is_local_shipping_rate_table`),
  KEY `is_tax` (`is_tax`),
  KEY `is_vat` (`is_vat`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO {$newTable} VALUES
  (1, 'USD', 0, 1, 1, 1, 1, 0, 1, 0, 1, 1, 0),
  (2, 'CAD', 0, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0),
  (3, 'GBP', 0, 1, 1, 0, 0, 1, 1, 1, 1, 0, 0),
  (4, 'AUD', 0, 1, 1, 1, 0, 0, 0, 0, 1, 0, 0),
  (5, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (6, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (7, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (8, 'EUR', 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0),
  (9, 'USD', 0, 1, 0, 1, 1, 0, 0, 0, 1, 1, 0),
  (10, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1),
  (11, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (12, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (13, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0),
  (14, 'CHF', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (15, 'HKD', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (16, 'USD', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 0),
  (17, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 0),
  (18, 'MYR', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0),
  (19, 'CAD', 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0),
  (20, 'PHP', 0, 1,  0, 0, 0, 0, 0, 0, 0, 0, 0),
  (21, 'PLN', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
  (22, 'SGD', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0),
  (23, 'SEK', 0, 0,  0, 0, 0, 0, 0, 0, 1, 0, 0);

SQL
);
    }

    //####################################
}