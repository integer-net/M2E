<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion4
{
    const DEVELOPMENT = false;

    const PREFIX_TABLE_BACKUP = '__backup';
    const PREFIX_TABLE_FROM = '__from';
    const PREFIX_TABLE_TO = '__to';

    private $prefixFrom = '';
    private $prefixTo = '';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    private $marketplaceIdTempShift = 1000;
    private $marketplacesWithMultivariation = array(1,2,3,4,5,7,8,9,10,14,16,17,18,20);
    private $marketplacesConverter = array(
        0 => 1,
        2 => 2,
        3 => 3,
        15 => 4,
        16 => 5,
        23 => 6,
        71 => 7,
        77 => 8,
        100 => 9,
        101 => 10,
        123 => 11,
        146 => 12,
        186 => 13,
        193 => 14,
        201 => 15,
        203 => 16,
        205 => 17,
        207 => 18,
        210 => 19,
        211 => 20,
        212 => 21,
        216 => 22,
        218 => 23
    );

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

        !$this->checkToSkipStep('m2epro'.self::PREFIX_TABLE_BACKUP.'_templates_attribute_sets') &&
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

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_locked_object') &&
            $this->createNewVersionTables();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_config') &&
            $this->processEssConfigTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_marketplace') &&
            $this->processM2eProConfigTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_account') &&
            $this->processM2eProMarketplacesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_account_store_category') &&
            $this->processM2eProAccountsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_attribute_set') &&
            $this->processM2eProAccountsStoreCategoriesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_selling_format') &&
            $this->processM2eProTemplatesAttributeSetsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_description') &&
            $this->processM2eProSellingFormatsTemplatesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_synchronization') &&
            $this->processM2eProDescriptionsTemplatesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_general') &&
            $this->processM2eProSynchronizationsTemplatesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_general_calculated_shipping') &&
            $this->processM2eProListingsTemplatesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_general_payment') &&
            $this->processM2eProListingsTemplatesCalculatedShippingTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_general_shipping') &&
            $this->processM2eProListingsTemplatesPaymentsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_template_general_specific') &&
            $this->processM2eProListingsTemplatesShippingsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_listing') &&
            $this->processM2eProListingsTemplatesSpecificsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_listing_category') &&
            $this->processM2eProListingsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_listing_product') &&
            $this->processM2eProListingsCategoriesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_listing_product_variation') &&
            $this->processM2eProListingsProductsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_listing_product_variation_option') &&
            $this->processM2eProListingsProductsVariationsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_listing_other') &&
            $this->processM2eProListingsProductsVariationsOptionsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_item') &&
            $this->processM2eProEbayListingsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_feedback') &&
            $this->processM2eProEbayItemsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_feedback_template') &&
            $this->processM2eProFeedbacksTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_message') &&
            $this->processM2eProFeedbacksTemplatesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_product_change') &&
            $this->processM2eProMessagesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_dictionary_category') &&
            $this->processM2eProProductsChangesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_dictionary_marketplace') &&
            $this->processM2eProDictionaryCategoriesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_dictionary_shipping') &&
            $this->processM2eProDictionaryMarketplacesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_dictionary_shipping_category') &&
            $this->processM2eProDictionaryShippingsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_listing_log') &&
            $this->processM2eProDictionaryShippingsCategoriesTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_synchronization_log') &&
            $this->processM2eProListingsLogsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_listing_other_log') &&
            $this->processM2eProSynchronizationsLogsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_synchronization_run') &&
            $this->processM2eProEbayListingsLogsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_order') &&
            $this->processM2eProSynchronizationsRunsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_order_item') &&
            $this->processM2eProEbayOrdersTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_order_log') &&
            $this->processM2eProEbayOrdersItemsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_order_external_transaction') &&
            $this->processM2eProEbayOrdersLogsTable();

        !$this->checkToSkipStep('m2epro'.$this->prefixTo.'_ebay_order_external_transaction') &&
            $this->processM2eProEbayOrdersExternalTransactionsTable();

        if (self::DEVELOPMENT) {
            echo 'Total Migration Time: '.(string)round(microtime(true) - $startTime,2).'s.';
        }
    }

    //####################################

    private function backupOldVersionTables()
    {
        $tempTables = array(
            'ess'.$this->prefixFrom.'_config',
            'm2epro'.$this->prefixFrom.'_accounts',
            'm2epro'.$this->prefixFrom.'_accounts_store_categories',
            'm2epro'.$this->prefixFrom.'_config',
            'm2epro'.$this->prefixFrom.'_descriptions_templates',
            'm2epro'.$this->prefixFrom.'_dictionary_categories',
            'm2epro'.$this->prefixFrom.'_dictionary_marketplaces',
            'm2epro'.$this->prefixFrom.'_dictionary_shippings',
            'm2epro'.$this->prefixFrom.'_dictionary_shippings_categories',
            'm2epro'.$this->prefixFrom.'_ebay_items',
            'm2epro'.$this->prefixFrom.'_ebay_listings',
            'm2epro'.$this->prefixFrom.'_ebay_listings_logs',
            'm2epro'.$this->prefixFrom.'_ebay_orders',
            'm2epro'.$this->prefixFrom.'_ebay_orders_external_transactions',
            'm2epro'.$this->prefixFrom.'_ebay_orders_items',
            'm2epro'.$this->prefixFrom.'_ebay_orders_logs',
            'm2epro'.$this->prefixFrom.'_feedbacks',
            'm2epro'.$this->prefixFrom.'_feedbacks_templates',
            'm2epro'.$this->prefixFrom.'_listings',
            'm2epro'.$this->prefixFrom.'_listings_categories',
            'm2epro'.$this->prefixFrom.'_listings_logs',
            'm2epro'.$this->prefixFrom.'_listings_products',
            'm2epro'.$this->prefixFrom.'_listings_products_variations',
            'm2epro'.$this->prefixFrom.'_listings_products_variations_options',
            'm2epro'.$this->prefixFrom.'_listings_templates',
            'm2epro'.$this->prefixFrom.'_listings_templates_calculated_shipping',
            'm2epro'.$this->prefixFrom.'_listings_templates_payments',
            'm2epro'.$this->prefixFrom.'_listings_templates_shippings',
            'm2epro'.$this->prefixFrom.'_listings_templates_specifics',
            'm2epro'.$this->prefixFrom.'_lock_items',
            'm2epro'.$this->prefixFrom.'_marketplaces',
            'm2epro'.$this->prefixFrom.'_messages',
            'm2epro'.$this->prefixFrom.'_migration_temp',
            'm2epro'.$this->prefixFrom.'_products_changes',
            'm2epro'.$this->prefixFrom.'_selling_formats_templates',
            'm2epro'.$this->prefixFrom.'_synchronizations_logs',
            'm2epro'.$this->prefixFrom.'_synchronizations_runs',
            'm2epro'.$this->prefixFrom.'_synchronizations_templates',
            'm2epro'.$this->prefixFrom.'_templates_attribute_sets'
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

    private function createNewVersionTables()
    {
        $tempTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_lock_item');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$tempTable};
CREATE TABLE {$tempTable} (
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

SQL
);

        $tempTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_locked_object');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$tempTable};
CREATE TABLE {$tempTable} (
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

SQL
);
    }

    //####################################

    private function processEssConfigTable()
    {
        $newTable = $this->installer->getTable('ess'.$this->prefixTo.'_config');
        $oldTable = $this->installer->getTable('ess'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` LIKE `{$oldTable}`;
INSERT `{$newTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

UPDATE `{$newTable}`
SET `group` = '/M2ePro/license/ebay/'
WHERE `group` = '/M2ePro/license/'
AND   `key` = 'mode';

UPDATE `{$newTable}`
SET `group` = '/M2ePro/license/ebay/'
WHERE `group` = '/M2ePro/license/'
AND   `key` = 'status';

UPDATE `{$newTable}`
SET `group` = '/M2ePro/license/ebay/',
    `key` = 'expiration_date'
WHERE `group` = '/M2ePro/license/'
AND   `key` = 'expired_date';

DELETE FROM `{$newTable}`
WHERE `group` = '/M2ePro/license/'
AND   `key` = 'component';

INSERT INTO `{$newTable}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/M2ePro/license/amazon/', 'mode',
     '0', '0 - None\r\n1 - Trial\r\n2 - Live', '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
    ('/M2ePro/license/amazon/', 'expiration_date',
     NULL, NULL, '2012-05-25 07:52:31', '2012-05-21 10:47:49'),
    ('/M2ePro/license/amazon/', 'status',
     '0', '0 - None\r\n1 - Active\r\n2 - Suspended\r\n3 - Closed', '2012-05-25 07:52:31', '2012-05-21 10:47:49');

SQL
);
    }

    private function processM2eProConfigTable()
    {
        $newTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` LIKE `{$oldTable}`;
INSERT `{$newTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

UPDATE `{$newTable}`
SET `group` = '/logs/cleaning/other_listings/'
WHERE `group` = '/logs/cleaning/ebay_listings/';

UPDATE `{$newTable}`
SET `group` = '/support/form/'
WHERE `group` = '/support/';

UPDATE `{$newTable}`
SET `value` = '900'
WHERE `group` = '/synchronization/lockItem/'
AND   `key` = 'max_deactivate_time';

UPDATE `{$newTable}`
SET `value` = '900'
WHERE `group` = '/listings/lockItem/'
AND   `key` = 'max_deactivate_time';

UPDATE `{$newTable}`
SET `group` = '/synchronization/cron/distribution/'
WHERE `group` = '/cron/distribution/';

UPDATE `{$newTable}`
SET `group` = CONCAT('/ebay',`group`)
WHERE `group` LIKE '/synchronization/settings%';

UPDATE `{$newTable}`
SET `group` = '/ebay/synchronization/settings/other_listings/'
WHERE `group` = '/ebay/synchronization/settings/ebay_listings/';

UPDATE `{$newTable}`
SET `group` = '/synchronization/settings/defaults/inspector/'
WHERE `group` = '/ebay/synchronization/settings/templates/inspector/'
AND   `key` = 'mode';

UPDATE `{$newTable}`
SET `value` = 'http://docs.m2epro.com/display/m2eproV32/M2E+Pro'
WHERE `group` = '/documentation/'
AND `key` = 'baseurl';

DELETE FROM `{$newTable}`
WHERE `group` = '/ebay/synchronization/settings/defaults/remove_deleted_products/'
OR    `group` = '/ebay/synchronization/settings/marketplaces/default/'
OR    `group` = '/ebay/synchronization/settings/templates/inspector/'
OR    (`group` = '/ebay/synchronization/settings/orders/'
        AND
       `key` = 'since_time')
OR   `group` = '/migrate/'
OR   `group` = '/messages/';

INSERT INTO `{$newTable}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
    ('/component/', 'default', 'ebay', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/component/amazon/', 'mode', '0', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/component/ebay/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/ebay/synchronization/settings/', 'mode',
     '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/synchronization/settings/', 'mode',
     '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/synchronization/settings/defaults/', 'mode',
     '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
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
    ('/cron/', 'mode', '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/synchronization/', 'mode',
    '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/synchronization/', 'last_access',
     NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/synchronization/', 'interval', '300', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/logs_cleaning/', 'mode',
     '1', '0 - disable, \r\n1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/logs_cleaning/', 'last_access',
     NULL, 'date of last access', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/cron/task/logs_cleaning/', 'interval',
     '86400', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49');

SQL
);
    }

    private function processM2eProMarketplacesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_marketplaces');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_marketplace');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_marketplace');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,`categories_version`, 0
FROM `{$oldTable}`;

SQL
);

        // General table

        $this->installer->getConnection()->addColumn($newGeneralTable,'native_id','INT(11) UNSIGNED NOT NULL AFTER id');
        $this->installer->getConnection()->update(
            $newGeneralTable, array('native_id' => new Zend_Db_Expr("`id`"))
        );
        $this->installer->getConnection()->update(
            $newGeneralTable,
            array('id' => new Zend_Db_Expr("`id` + {$this->marketplaceIdTempShift}")),
            array('id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newGeneralTable, array('id' => $newId), array('id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'id', 'INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'
        );
        $this->installer->getConnection()->dropColumn($newGeneralTable,'categories_version');
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'component_mode','VARCHAR(10) DEFAULT NULL AFTER group_title'
        );
        $this->installer->getConnection()->update($newGeneralTable, array('component_mode' => 'ebay'));
        $this->installer->getConnection()->addKey($newGeneralTable, 'native_id', 'native_id', 'index');
        $this->installer->getConnection()->addKey($newGeneralTable, 'component_mode', 'component_mode', 'index');

        // Component table

        $this->installer->getConnection()->update(
            $newComponentTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newComponentTable,
                array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
            if (in_array($newId,$this->marketplacesWithMultivariation)) {
                $this->installer->getConnection()->update(
                    $newComponentTable, array('is_multivariation' => 1), array('marketplace_id = ?'=>$newId)
                );
            }
        }
    }

    private function processM2eProAccountsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_accounts');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_account');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_account');

        $configTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $synchronizationSettingsOrdersSinceTime = NULL;
        $tempStmt = $this->installer->getConnection()
                              ->query("SELECT *
                                       FROM `{$configTable}`
                                       WHERE `group` = '/synchronization/settings/orders/'
                                       AND `key` = 'since_time'");
        $tempConfigRow = $tempStmt->fetch();
        isset($tempConfigRow) && $synchronizationSettingsOrdersSinceTime = $tempConfigRow['value'];

        if (is_string($synchronizationSettingsOrdersSinceTime)) {
            $synchronizationSettingsOrdersSinceTime = "'".$synchronizationSettingsOrdersSinceTime."'";
        } else {
            $synchronizationSettingsOrdersSinceTime = 'NULL';
        }

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}`(
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `title`,
       '{$tempComponentNick}',
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}`(
  `account_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(2) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `token_session` VARCHAR(255) NOT NULL,
  `token_expired_date` DATETIME NOT NULL,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_last_synchronization` DATETIME DEFAULT NULL,
  `feedbacks_receive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response_only_positive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_last_used_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `ebay_store_title` VARCHAR(255) NOT NULL,
  `ebay_store_url` TEXT NOT NULL,
  `ebay_store_subscription_level` VARCHAR(255) NOT NULL,
  `ebay_store_description` TEXT NOT NULL,
  `ebay_info` TEXT DEFAULT NULL,
  `orders_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `orders_last_synchronization` DATETIME DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `messages_receive` TINYINT(2) NOT NULL DEFAULT 0,
  INDEX other_listings_last_synchronization (other_listings_last_synchronization),
  INDEX other_listings_synchronization (other_listings_synchronization),
  INDEX ebay_store_subscription_level (ebay_store_subscription_level),
  INDEX ebay_store_title (ebay_store_title),
  INDEX feedbacks_auto_response (feedbacks_auto_response),
  INDEX feedbacks_auto_response_only_positive (feedbacks_auto_response_only_positive),
  INDEX feedbacks_last_used_id (feedbacks_last_used_id),
  INDEX feedbacks_receive (feedbacks_receive),
  INDEX messages_receive (messages_receive),
  INDEX mode (mode),
  INDEX orders_mode (orders_mode),
  INDEX orders_last_synchronization (orders_last_synchronization),
  INDEX server_hash (server_hash),
  INDEX token_expired_date (token_expired_date),
  INDEX token_session (token_session)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newComponentTable}`
SELECT `id`,
       `mode`,
       `server_hash`,
       `token_session`,
       `token_expired_date`,
       `ebay_listings_synchronization`,
       `ebay_listings_last_synchronization`,
       `feedbacks_receive`,
       `feedbacks_auto_response`,
       `feedbacks_auto_response_only_positive`,
       `feedbacks_last_used_id`,
       `ebay_store_title`,
       `ebay_store_url`,
       `ebay_store_subscription_level`,
       `ebay_store_description`,
       `ebay_info`,
       `orders_mode`,
       {$synchronizationSettingsOrdersSinceTime},
       '',
       `messages_receive`
FROM `{$oldTable}`;

SQL
);

        // Component table

        $result = $this->installer->getConnection()->query(<<<SQL

SELECT  `id`,
        `orders_listings_mode`,
        `orders_listings_store_mode`,
        `orders_listings_store_id`,
        `orders_ebay_mode`,
        `orders_ebay_create_product`,
        `orders_ebay_store_id`,
        `orders_customer_mode`,
        `orders_customer_exist_id`,
        `orders_customer_new_website`,
        `orders_customer_new_group`,
        `orders_customer_new_subscribe_news`,
        `orders_customer_new_send_notifications`,
        `orders_status_mode`,
        `orders_status_checkout_incomplete`,
        `orders_status_payment_complete_mode`,
        `orders_combined_mode`,
        `orders_status_checkout_completed`,
        `orders_status_payment_completed`,
        `orders_status_shipping_completed`,
        `orders_status_invoice`,
        `orders_status_shipping`
FROM {$oldTable}

SQL
);

        $updateSql = '';
        $ordersSettings = $result->fetchAll();

        foreach ($ordersSettings as $singleAccountSettings) {
            $convertedSettings = $this->convertMagentoOrdersSettings($singleAccountSettings);
            $convertedSettingsSql = $this->installer->getConnection()->quote($convertedSettings);

            $updateSql .= <<<SQL

UPDATE `{$newComponentTable}`
SET `magento_orders_settings` = {$convertedSettingsSql}
WHERE `account_id` = {$singleAccountSettings['id']};

SQL;
        }

        $this->installer->getConnection()->multi_query($updateSql);
    }

    private function processM2eProAccountsStoreCategoriesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_accounts_store_categories');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_account_store_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProTemplatesAttributeSetsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_templates_attribute_sets');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_attribute_set');

        $this->installer->getConnection()->multi_query(<<<SQL

    DROP TABLE IF EXISTS `{$newGeneralTable}`;
    CREATE TABLE `{$newGeneralTable}` (
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

    INSERT `{$newGeneralTable}` (`object_id`,`object_type`,`attribute_set_id`,`update_date`,`create_date`)
    SELECT `template_id`,
           `template_type`,
           `attribute_set_id`,
           `update_date`,
           `create_date`
    FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newGeneralTable, array('object_type' => new Zend_Db_Expr("`object_type` + 5"))
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('object_type' => 3), array('`object_type` = ?'=>1+5)
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('object_type' => 4), array('`object_type` = ?'=>2+5)
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('object_type' => 2), array('`object_type` = ?'=>3+5)
        );
    }

    private function processM2eProSellingFormatsTemplatesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_selling_formats_templates');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_template_selling_format');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_selling_format');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `title`,
       '{$tempComponentNick}',
       `synch_date`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

UPDATE `{$newGeneralTable}`
SET `synch_date` = `update_date`;

SQL
);

        // Component table

        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'id', 'template_selling_format_id', 'INT(11) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropColumn($newComponentTable,'title');
        $this->installer->getConnection()->dropColumn($newComponentTable,'synch_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'update_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'create_date');

        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'duration_ebay', 'duration_mode', 'TINYINT(4) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropKey($newComponentTable,'duration_ebay');
        $this->installer->getConnection()->addKey($newComponentTable, 'duration_mode', 'duration_mode', 'index');
    }

    private function processM2eProDescriptionsTemplatesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_descriptions_templates');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_template_description');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_description');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `title`,
       '{$tempComponentNick}',
       `synch_date`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

UPDATE `{$newGeneralTable}`
SET `synch_date` = `update_date`;

SQL
);

        // Component table

        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'id', 'template_description_id', 'INT(11) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropColumn($newComponentTable,'title');
        $this->installer->getConnection()->dropColumn($newComponentTable,'synch_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'update_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'create_date');
    }

    private function processM2eProSynchronizationsTemplatesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_synchronizations_templates');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_template_synchronization');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_synchronization');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `title`,
       `revise_change_selling_format_template`,
       `revise_change_description_template`,
       `revise_change_listing_template`,
       '{$tempComponentNick}',
       NULL,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

UPDATE `{$newGeneralTable}`
SET `synch_date` = `update_date`;

SQL
);

        // Component table

        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'id', 'template_synchronization_id', 'INT(11) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->addColumn(
            $newComponentTable, 'revise_update_ebay_qty', 'TINYINT(2) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'revise_update_ebay_qty', 'revise_update_qty', 'TINYINT(2) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'revise_update_ebay_price', 'revise_update_price', 'TINYINT(2) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropColumn($newComponentTable,'title');
        $this->installer->getConnection()->dropColumn($newComponentTable,'revise_change_selling_format_template');
        $this->installer->getConnection()->dropColumn($newComponentTable,'revise_change_description_template');
        $this->installer->getConnection()->dropColumn($newComponentTable,'revise_change_listing_template');
        $this->installer->getConnection()->dropColumn($newComponentTable,'update_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'create_date');

        $this->installer->getConnection()->dropKey($newComponentTable,'revise_update_ebay_price');
        $this->installer->getConnection()->dropKey($newComponentTable,'revise_update_ebay_qty');
        $this->installer->getConnection()->addKey(
            $newComponentTable, 'revise_update_price', 'revise_update_price', 'index'
        );
        $this->installer->getConnection()->addKey(
            $newComponentTable, 'revise_update_qty', 'revise_update_qty', 'index'
        );
        $this->installer->getConnection()->addKey($newComponentTable, 'relist_list_mode', 'relist_list_mode', 'index');
    }

    private function processM2eProListingsTemplatesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_templates');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_template_general');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_general');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `account_id`,
       `marketplace_id`,
       `title`,
       '{$tempComponentNick}',
       `synch_date`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

UPDATE `{$newGeneralTable}`
SET `synch_date` = `update_date`;

SQL
);

        // General table

        $this->installer->getConnection()->update(
            $newGeneralTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newGeneralTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }

        // Component table
        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'id', 'template_general_id', 'INT(11) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropColumn($newComponentTable,'title');
        $this->installer->getConnection()->dropColumn($newComponentTable,'account_id');
        $this->installer->getConnection()->dropColumn($newComponentTable,'marketplace_id');
        $this->installer->getConnection()->dropColumn($newComponentTable,'synch_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'update_date');
        $this->installer->getConnection()->dropColumn($newComponentTable,'create_date');

        $this->installer->getConnection()->modifyColumn($newComponentTable, 'vat_percent', 'TINYINT(2) NOT NULL');
    }

    private function processM2eProListingsTemplatesCalculatedShippingTable()
    {
        $oldTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listings_templates_calculated_shipping'
        );
        $newComponentTable = $this->installer->getTable(
            'm2epro'.$this->prefixTo.'_ebay_template_general_calculated_shipping'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->changeColumn(
            $newComponentTable, 'listing_template_id', 'template_general_id', 'INT(11) UNSIGNED NOT NULL'
        );
    }

    private function processM2eProListingsTemplatesPaymentsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_templates_payments');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_general_payment');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `listing_template_id`,
       `payment_id`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsTemplatesShippingsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_templates_shippings');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_general_shipping');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `listing_template_id`,
       `priority`,
       `locations`,
       `cost_mode`,
       `cost_value`,
       `cost_additional_items`,
       `shipping_type`,
       `shipping_value`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsTemplatesSpecificsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_templates_specifics');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_template_general_specific');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `listing_template_id`,
       `mode`,
       `mode_relation_id`,
       `attribute_id`,
       `attribute_title`,
       `value_mode`,
       `value_ebay_recommended`,
       `value_custom_value`,
       `value_custom_attribute`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_listing');

        $tableAttributeSet = $this->installer->getTable('m2epro'.$this->prefixTo.'_attribute_set');
        $tempObjectType = 1;

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

INSERT INTO `{$tableAttributeSet}` (`object_id`,`object_type`,`attribute_set_id`,`update_date`,`create_date`)
SELECT `id`,
       {$tempObjectType},
       `attribute_set_id`,
       `update_date`,
       `create_date`
FROM `{$newGeneralTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
  listing_id INT(11) UNSIGNED NOT NULL,
  products_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (listing_id),
  INDEX products_sold_count (products_sold_count)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newComponentTable}`
SELECT `id`,
       `products_sold_count`
FROM `{$oldTable}`;

SQL
);

        // General table

        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'selling_format_template_id', 'template_selling_format_id', 'INT(11) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'listing_template_id', 'template_general_id', 'INT(11) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'description_template_id', 'template_description_id', 'INT(11) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'synchronization_template_id', 'template_synchronization_id', 'INT(11) UNSIGNED NOT NULL'
        );

        $this->installer->getConnection()->dropColumn($newGeneralTable, 'attribute_set_id');
        $this->installer->getConnection()->dropColumn($newGeneralTable, 'products_sold_count');

        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'component_mode','VARCHAR(10) DEFAULT NULL AFTER hide_products_others_listings'
        );
        $this->installer->getConnection()->update($newGeneralTable, array('component_mode' => 'ebay'));
        $this->installer->getConnection()->addKey($newGeneralTable, 'component_mode', 'component_mode', 'index');

        $this->installer->getConnection()->dropKey($newGeneralTable, 'selling_format_template_id');
        $this->installer->getConnection()->dropKey($newGeneralTable, 'listing_template_id');
        $this->installer->getConnection()->dropKey($newGeneralTable, 'description_template_id');
        $this->installer->getConnection()->dropKey($newGeneralTable, 'synchronization_template_id');

        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'template_general_id', 'template_general_id', 'index'
        );
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'template_selling_format_id', 'template_selling_format_id', 'index'
        );
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'template_description_id', 'template_description_id', 'index'
        );
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'template_synchronization_id', 'template_synchronization_id', 'index'
        );
    }

    private function processM2eProListingsCategoriesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_categories');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsProductsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_products');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_listing_product');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `listing_id`,
       `product_id`,
       `status`,
       `status_changer`,
       '{$tempComponentNick}',
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `ebay_items_id`,
       `ebay_start_price`,
       `ebay_reserve_price`,
       `ebay_buyitnow_price`,
       `ebay_qty`,
       `ebay_qty_sold`,
       `ebay_bids`,
       `ebay_start_date`,
       `ebay_end_date`,
       `additional_data`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsProductsVariationsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_products_variations');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_product_variation');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_listing_product_variation');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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
AUTO_INCREMENT = 1
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newGeneralTable}`
SELECT `id`,
       `listing_product_id`,
       `add`,
       `delete`,
       `status`,
       '{$tempComponentNick}',
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `ebay_price`,
       `ebay_qty`,
       `ebay_qty_sold`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProListingsProductsVariationsOptionsTable()
    {
        $oldTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listings_products_variations_options'
        );
        $newGeneralTable = $this->installer->getTable(
            'm2epro'.$this->prefixTo.'_listing_product_variation_option'
        );
        $newComponentTable = $this->installer->getTable(
            'm2epro'.$this->prefixTo.'_ebay_listing_product_variation_option'
        );

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `listing_product_variation_id`,
       `product_id`,
       `product_type`,
       `attribute`,
       `option`,
       '{$tempComponentNick}',
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
  listing_product_variation_option_id INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (listing_product_variation_option_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newComponentTable}`
SELECT `id` FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProEbayListingsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listings');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_other');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_listing_other');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
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

INSERT `{$newGeneralTable}`
SELECT `id`,
       `account_id`,
       `marketplace_id`,
       NULL,
       `status`,
       '{$tempComponentNick}',
       NOW(),
       NOW()
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `ebay_title`,
       `ebay_item`,
       `ebay_old_items`,
       `ebay_currency`,
       `ebay_price`,
       `ebay_qty`,
       `ebay_qty_sold`,
       `ebay_bids`,
       `ebay_start_date`,
       `ebay_end_date`
FROM `{$oldTable}`;

SQL
);

        // General table

        $this->installer->getConnection()->update(
            $newGeneralTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newGeneralTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProEbayItemsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_items');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_item');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProFeedbacksTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_feedbacks');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_feedback');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProFeedbacksTemplatesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_feedbacks_templates');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_feedback_template');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProMessagesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_messages');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_message');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}`(
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

SQL
);
    }

    private function processM2eProProductsChangesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_products_changes');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_product_change');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProDictionaryCategoriesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_dictionary_categories');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_dictionary_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newComponentTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newComponentTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProDictionaryMarketplacesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_dictionary_marketplaces');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_dictionary_marketplace');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newComponentTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newComponentTable,
                array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProDictionaryShippingsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_dictionary_shippings');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_dictionary_shipping');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newComponentTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newComponentTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProDictionaryShippingsCategoriesTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_dictionary_shippings_categories');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_dictionary_shipping_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` LIKE `{$oldTable}`;
INSERT `{$newComponentTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newComponentTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newComponentTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProListingsLogsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listings_logs');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_log');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'action_id', 'INT(11) UNSIGNED DEFAULT NULL AFTER product_title'
        );
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'component_mode','VARCHAR(10) DEFAULT NULL AFTER description'
        );
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'action','TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER action_id'
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('component_mode' => 'ebay')
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'action', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER action_id'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'initiator', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER action'
        );

        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'component_mode', 'component_mode', 'index'
        );
    }

    private function processM2eProSynchronizationsLogsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_synchronizations_logs');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_synchronization_log');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'synchronizations_runs_id', 'synchronization_run_id', 'INT(11) UNSIGNED NOT NULL'
        );
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'initiator','TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER synch_task'
        );
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'component_mode','VARCHAR(10) DEFAULT NULL AFTER description'
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('component_mode' => 'ebay')
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'creator', 'VARCHAR(255) DEFAULT NULL'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'creator', 'VARCHAR(255) DEFAULT NULL'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'type', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'priority', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 3'
        );
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'component_mode', 'component_mode', 'index'
        );
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'initiator', 'initiator', 'index'
        );
        $this->installer->getConnection()->dropKey($newGeneralTable,'synchronizations_runs_id');
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'synchronization_run_id', 'synchronization_run_id', 'index'
        );
    }

    private function processM2eProEbayListingsLogsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listings_logs');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_listing_other_log');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->changeColumn(
            $newGeneralTable, 'ebay_listing_id', 'listing_other_id', 'INT(11) UNSIGNED DEFAULT NULL'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'title', 'VARCHAR(255) DEFAULT NULL AFTER listing_other_id'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'action', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 AFTER action_id'
        );
        $this->installer->getConnection()->modifyColumn(
            $newGeneralTable, 'initiator', 'TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 AFTER action'
        );
        $this->installer->getConnection()->addColumn(
            $newGeneralTable,'component_mode','VARCHAR(10) DEFAULT NULL AFTER description'
        );
        $this->installer->getConnection()->update(
            $newGeneralTable, array('component_mode' => 'ebay')
        );

        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'component_mode', 'component_mode', 'index'
        );
        $this->installer->getConnection()->dropKey($newGeneralTable,'ebay_listing_id');
        $this->installer->getConnection()->addKey(
            $newGeneralTable, 'listing_other_id', 'listing_other_id', 'index'
        );
    }

    private function processM2eProSynchronizationsRunsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_synchronizations_runs');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_synchronization_run');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` LIKE `{$oldTable}`;
INSERT `{$newGeneralTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProEbayOrdersTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_orders');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_order');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_order');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED DEFAULT NULL,
  `magento_order_id` INT(11) UNSIGNED DEFAULT NULL,
  `marketplace_id` INT(11) UNSIGNED DEFAULT NULL,
  `store_id` INT(11) UNSIGNED DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `magento_order_id` (`magento_order_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newGeneralTable}`
SELECT `id`,
       `account_id`,
       `magento_order_id`,
       `marketplace_id`,
       NULL,
       '{$tempComponentNick}',
       NULL,
       NULL
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}`(
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `ebay_order_id`,
       `selling_manager_record_number`,
       `buyer_name`,
       `buyer_email`,
       `buyer_userid`,
       `checkout_status`,
       `checkout_message`,
       `payment_used`,
       `payment_status_m2e_code`,
       `payment_status`,
       `payment_hold_status`,
       `payment_time`,
       `shipping_selected_service`,
       `shipping_buyer_selected`,
       `shipping_status`,
       `shipping_address`,
       `shipping_tracking_details`,
       `shipping_selected_cost`,
       `shipping_type`,
       `shipping_time`,
       `get_it_fast`,
       `amount_paid`,
       `amount_saved`,
       `currency`,
       `best_offer_sale`,
       `final_value_fee`,
       `sales_tax_percent`,
       `sales_tax_state`,
       `sales_tax_shipping_included`,
       `sales_tax_amount`,
       `update_time`,
       `created_date`
FROM `{$oldTable}`;

SQL
);

        $this->installer->getConnection()->update(
            $newGeneralTable,
            array('marketplace_id' => new Zend_Db_Expr("`marketplace_id` + {$this->marketplaceIdTempShift}")),
            array('marketplace_id < ?'=>$this->marketplaceIdTempShift)
        );
        foreach ($this->marketplacesConverter as $oldId => $newId) {
            $this->installer->getConnection()->update(
                $newGeneralTable, array('marketplace_id' => $newId),
                array('marketplace_id = ?'=>$oldId+$this->marketplaceIdTempShift)
            );
        }
    }

    private function processM2eProEbayOrdersItemsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_orders_items');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_order_item');
        $newComponentTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_order_item');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_id` INT(11) UNSIGNED DEFAULT NULL,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `product_id` (`product_id`),
  INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newGeneralTable}`
SELECT `id`,
       `ebay_order_id`,
       `product_id`,
       '{$tempComponentNick}'
FROM `{$oldTable}`;

DROP TABLE IF EXISTS `{$newComponentTable}`;
CREATE TABLE `{$newComponentTable}` (
  order_item_id INT(11) UNSIGNED NOT NULL,
  transaction_id DECIMAL(20, 0) UNSIGNED NOT NULL DEFAULT 0,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(64) DEFAULT NULL,
  condition_display_name VARCHAR(255) DEFAULT NULL,
  `price` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `buy_it_now_price` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
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

INSERT `{$newComponentTable}`
SELECT `id`,
       `transaction_id`,
       `item_id`,
       `item_title`,
       `item_sku`,
       `item_condition_display_name`,
       `price`,
       `buy_it_now_price`,
       `currency`,
       `qty_purchased`,
       `variations`,
       `auto_pay`,
       `listing_type`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProEbayOrdersLogsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_orders_logs');
        $newGeneralTable = $this->installer->getTable('m2epro'.$this->prefixTo.'_order_log');

        $tempComponentNick = 'ebay';

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$newGeneralTable}`;
CREATE TABLE `{$newGeneralTable}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `message` TEXT NOT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `component_mode` VARCHAR(10) DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newGeneralTable}`
SELECT `id`,
       `order_id`,
       `message`,
       `code`,
       '{$tempComponentNick}',
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    private function processM2eProEbayOrdersExternalTransactionsTable()
    {
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_orders_external_transactions');
        $tableToChannel = $this->installer->getTable('m2epro'.$this->prefixTo.'_ebay_order_external_transaction');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS `{$tableToChannel}`;
CREATE TABLE `{$tableToChannel}` (
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

INSERT `{$tableToChannel}`
SELECT `id`,
       `order_id`,
       `ebay_id`,
       `fee`,
       `sum`,
       `is_refund`,
       `time`,
       NULL,
       NULL
FROM `{$oldTable}`;

SQL
);
    }

    //####################################

    private function checkToSkipStep($nextTable)
    {
        $nextTable = $this->installer->getTable($nextTable);
        return (bool)$this->installer->tableExists($nextTable);
    }

    private function convertMagentoOrdersSettings(array $singleAccountSettings)
    {
        $customer_created = strpos($singleAccountSettings['orders_customer_new_send_notifications'], 'a1') !== false;
        $invoice_created = strpos($singleAccountSettings['orders_customer_new_send_notifications'], 'i1') !== false;
        $order_created = strpos($singleAccountSettings['orders_customer_new_send_notifications'], 'o1') !== false;

        $newFormat = array(
            'listing' => array(
                'mode' => (int)$singleAccountSettings['orders_listings_mode'],
                'store_mode' => (int)$singleAccountSettings['orders_listings_store_mode'],
                'store_id' => (int)$singleAccountSettings['orders_listings_store_id']
            ),
            'listing_other' => array(
                'mode' => (int)$singleAccountSettings['orders_ebay_mode'],
                'product_mode' => (int)$singleAccountSettings['orders_ebay_create_product'],
                'store_id' => (int)$singleAccountSettings['orders_ebay_store_id'],
            ),
            'customer' => array(
                'mode' => (int)$singleAccountSettings['orders_customer_mode'],
                'id' => (int)$singleAccountSettings['orders_customer_exist_id'],
                'website_id' => (int)$singleAccountSettings['orders_customer_new_website'],
                'group_id' => (int)$singleAccountSettings['orders_customer_new_group'],
                'subscription_mode' => (int)$singleAccountSettings['orders_customer_new_subscribe_news'],
                'notifications' => array(
                    'customer_created' => $customer_created,
                    'invoice_created' => $invoice_created,
                    'order_created' => $order_created,
                )
            ),
            'rules' => array(
                'checkout_mode' => (int)$singleAccountSettings['orders_status_checkout_incomplete'],
                'payment_mode' => (int)$singleAccountSettings['orders_status_payment_complete_mode'],
                'transaction_mode' => (int)$singleAccountSettings['orders_combined_mode'],
            ),
            'tax' => array(
                'mode' => 3
            ),
            'status_mapping' => array(
                'mode' => (int)$singleAccountSettings['orders_status_mode'],
                'new' => !empty($singleAccountSettings['orders_status_checkout_completed'])
                    ? $singleAccountSettings['orders_status_checkout_completed'] : 'pending',
                'paid' => !empty($singleAccountSettings['orders_status_payment_completed'])
                    ? $singleAccountSettings['orders_status_payment_completed'] : 'processing',
                'shipped' => !empty($singleAccountSettings['orders_status_shipping_completed'])
                    ? $singleAccountSettings['orders_status_shipping_completed'] : 'complete',
            ),
            'invoice_mode' => (int)$singleAccountSettings['orders_status_invoice'],
            'shipment_mode' => (int)$singleAccountSettings['orders_status_shipping']
        );
        return json_encode($newFormat);
    }

    //####################################
}