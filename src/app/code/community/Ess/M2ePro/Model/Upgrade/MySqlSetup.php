<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    private $moduleTables = array();

    //####################################

    public function __construct($resourceName)
    {
        // Get needed mysql tables
        $tempTables = Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables();
        $tempTables = array_merge($this->getMySqlTablesV3(),$tempTables);
        $tempTables = array_merge($this->getMySqlTablesV4(),$tempTables);
        $tempTables = array_merge($this->getMySqlTablesV5(),$tempTables);
        $tempTables = array_merge($this->getRemovedMySqlTables(),$tempTables);
        $tempTables = array_values(array_unique($tempTables));

        // Sort by length tables
        do {
            $hasChanges = false;
            for ($i=0;$i<count($tempTables)-1; $i++) {
                if (strlen($tempTables[$i]) < strlen($tempTables[$i+1])) {
                    $temp = $tempTables[$i];
                    $tempTables[$i] = $tempTables[$i+1];
                    $tempTables[$i+1] = $temp;
                    $hasChanges = true;
                }
            }
        } while ($hasChanges);

        // Prepare sql tables
        //--------------------
        foreach ($tempTables as $table) {
            $this->moduleTables[$table] = $this->getTable($table);
        }
        //--------------------

        parent::__construct($resourceName);
    }

    //####################################

    public function startSetup()
    {
        return parent::startSetup();
    }

    public function endSetup()
    {
        $this->removeConfigDuplicates();
        Mage::helper('M2ePro/Module')->clearCache();
        return parent::endSetup();
    }

    // ----------------------------------

    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        parent::_upgradeResourceDb($oldVersion, $newVersion);

        $this->updateInstallationVersionHistory($oldVersion, $newVersion);
        $this->updateCompilation();

        return $this;
    }

    protected function _installResourceDb($newVersion)
    {
        parent::_installResourceDb($newVersion);

        $this->updateInstallationVersionHistory(null, $newVersion);
        $this->updateCompilation();

        return $this;
    }

    //####################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }
        $sql = $this->prepareSql($sql);
        $this->_conn->multi_query($sql);
        return $this;
    }

    public function runSqlFile($path)
    {
        if (!is_file($path)) {
            return $this;
        }
        $sql = file_get_contents($path);
        return $this->run($sql);
    }

    //####################################

    public function getModuleTables()
    {
        return $this->moduleTables;
    }

    public function getRelatedSqlFilePath($pathPhpFile)
    {
        return dirname($pathPhpFile).DS.basename($pathPhpFile,'.php').'.sql';
    }

    //####################################

    public function removeConfigDuplicates()
    {
        $tables = $this->getConfigTablesV5();
        $tables = array_merge($this->getConfigTablesV6(),$tables);
        $tables = array_values(array_unique($tables));

        foreach ($tables as $table) {
            $this->removeConfigDuplicatesByTable($table);
        }
    }

    private function removeConfigDuplicatesByTable($tableName)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTable($tableName);

        if (!in_array($tableName, $connection->listTables())) {
            return;
        }

        $configRows = $connection->query("SELECT `id`, `group`, `key`
                                          FROM `{$tableName}`
                                          ORDER BY `id` ASC")
                                 ->fetchAll();

        $tempData = array();
        $deleteData = array();

        foreach ($configRows as $configRow) {

            $tempName = strtolower($configRow['group'] .'|'. $configRow['key']);

            if (in_array($tempName, $tempData)) {
                $deleteData[] = (int)$configRow['id'];
            } else {
                $tempData[] = $tempName;
            }
        }

        if (!empty($deleteData)) {
            $connection->query("DELETE FROM `{$tableName}`
                                WHERE `id` IN (".implode(',', $deleteData).')');
        }
    }

    //####################################

    private function prepareSql($sql)
    {
        foreach ($this->moduleTables as $tableFrom=>$tableTo) {
            $sql = str_replace(' `'.$tableFrom.'`',' `'.$tableTo.'`',$sql);
            $sql = str_replace(' '.$tableFrom,' `'.$tableTo.'`',$sql);
        }
        return $sql;
    }

    //------------------------------------

    private function getMySqlTablesV3()
    {
        return array(
            'ess_config',
            'm2epro_accounts',
            'm2epro_accounts_store_categories',
            'm2epro_config',
            'm2epro_descriptions_templates',
            'm2epro_dictionary_categories',
            'm2epro_dictionary_marketplaces',
            'm2epro_dictionary_shippings',
            'm2epro_dictionary_shippings_categories',
            'm2epro_ebay_items',
            'm2epro_ebay_listings',
            'm2epro_ebay_listings_logs',
            'm2epro_ebay_orders',
            'm2epro_ebay_orders_external_transactions',
            'm2epro_ebay_orders_items',
            'm2epro_ebay_orders_logs',
            'm2epro_feedbacks',
            'm2epro_feedbacks_templates',
            'm2epro_listings',
            'm2epro_listings_categories',
            'm2epro_listings_logs',
            'm2epro_listings_products',
            'm2epro_listings_products_variations',
            'm2epro_listings_products_variations_options',
            'm2epro_listings_templates',
            'm2epro_listings_templates_calculated_shipping',
            'm2epro_listings_templates_payments',
            'm2epro_listings_templates_shippings',
            'm2epro_listings_templates_specifics',
            'm2epro_lock_items',
            'm2epro_marketplaces',
            'm2epro_messages',
            'm2epro_migration_temp',
            'm2epro_products_changes',
            'm2epro_selling_formats_templates',
            'm2epro_synchronizations_logs',
            'm2epro_synchronizations_runs',
            'm2epro_synchronizations_templates',
            'm2epro_templates_attribute_sets'
        );
    }

    private function getMySqlTablesV4()
    {
        return array(
            'ess_config',
            'm2epro_config',

            'm2epro_lock_item',
            'm2epro_locked_object',
            'm2epro_product_change',
            'm2epro_processing_request',

            'm2epro_account',
            'm2epro_marketplace',
            'm2epro_attribute_set',

            'm2epro_order',
            'm2epro_order_item',
            'm2epro_order_log',

            'm2epro_synchronization_log',
            'm2epro_synchronization_run',

            'm2epro_listing',
            'm2epro_listing_category',
            'm2epro_listing_log',
            'm2epro_listing_other',
            'm2epro_listing_other_log',
            'm2epro_listing_product',
            'm2epro_listing_product_variation',
            'm2epro_listing_product_variation_option',

            'm2epro_template_description',
            'm2epro_template_general',
            'm2epro_template_selling_format',
            'm2epro_template_synchronization',

            'm2epro_translation_custom_suggestion',
            'm2epro_translation_language',
            'm2epro_translation_text',

            'm2epro_amazon_account',
            'm2epro_amazon_category',
            'm2epro_amazon_category_description',
            'm2epro_amazon_category_specific',
            'm2epro_amazon_dictionary_category',
            'm2epro_amazon_dictionary_marketplace',
            'm2epro_amazon_dictionary_specific',
            'm2epro_amazon_item',
            'm2epro_amazon_listing',
            'm2epro_amazon_listing_other',
            'm2epro_amazon_listing_product',
            'm2epro_amazon_listing_product_variation',
            'm2epro_amazon_listing_product_variation_option',
            'm2epro_amazon_marketplace',
            'm2epro_amazon_order',
            'm2epro_amazon_order_item',
            'm2epro_amazon_processed_inventory',
            'm2epro_amazon_template_description',
            'm2epro_amazon_template_general',
            'm2epro_amazon_template_selling_format',
            'm2epro_amazon_template_synchronization',

            'm2epro_ebay_account',
            'm2epro_ebay_account_store_category',
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
            'm2epro_ebay_dictionary_shipping',
            'm2epro_ebay_dictionary_shipping_category',
            'm2epro_ebay_feedback',
            'm2epro_ebay_feedback_template',
            'm2epro_ebay_item',
            'm2epro_ebay_listing',
            'm2epro_ebay_listing_other',
            'm2epro_ebay_listing_product',
            'm2epro_ebay_listing_product_variation',
            'm2epro_ebay_listing_product_variation_option',
            'm2epro_ebay_marketplace',
            'm2epro_ebay_message',
            'm2epro_ebay_motor_specific',
            'm2epro_ebay_order',
            'm2epro_ebay_order_item',
            'm2epro_ebay_order_external_transaction',
            'm2epro_ebay_template_description',
            'm2epro_ebay_template_general',
            'm2epro_ebay_template_general_calculated_shipping',
            'm2epro_ebay_template_general_payment',
            'm2epro_ebay_template_general_shipping',
            'm2epro_ebay_template_general_specific',
            'm2epro_ebay_template_selling_format',
            'm2epro_ebay_template_synchronization'
        );
    }

    private function getMySqlTablesV5()
    {
        return array(
            'ess_config',
            'm2epro_config',
            'm2epro_exceptions_filters',

            'm2epro_lock_item',
            'm2epro_locked_object',
            'm2epro_product_change',
            'm2epro_processing_request',

            'm2epro_account',
            'm2epro_marketplace',
            'm2epro_attribute_set',

            'm2epro_order',
            'm2epro_order_change',
            'm2epro_order_item',
            'm2epro_order_log',
            'm2epro_order_repair',

            'm2epro_synchronization_log',
            'm2epro_synchronization_run',

            'm2epro_listing',
            'm2epro_listing_category',
            'm2epro_listing_log',
            'm2epro_listing_other',
            'm2epro_listing_other_log',
            'm2epro_listing_product',
            'm2epro_listing_product_variation',
            'm2epro_listing_product_variation_option',

            'm2epro_template_description',
            'm2epro_template_general',
            'm2epro_template_selling_format',
            'm2epro_template_synchronization',

            'm2epro_translation_custom_suggestion',
            'm2epro_translation_language',
            'm2epro_translation_text',

            'm2epro_amazon_account',
            'm2epro_amazon_dictionary_category',
            'm2epro_amazon_dictionary_marketplace',
            'm2epro_amazon_dictionary_specific',
            'm2epro_amazon_item',
            'm2epro_amazon_listing',
            'm2epro_amazon_listing_other',
            'm2epro_amazon_listing_product',
            'm2epro_amazon_listing_product_variation',
            'm2epro_amazon_listing_product_variation_option',
            'm2epro_amazon_marketplace',
            'm2epro_amazon_order',
            'm2epro_amazon_order_item',
            'm2epro_amazon_processed_inventory',
            'm2epro_amazon_template_description',
            'm2epro_amazon_template_general',
            'm2epro_amazon_template_new_product',
            'm2epro_amazon_template_new_product_description',
            'm2epro_amazon_template_new_product_specific',
            'm2epro_amazon_template_selling_format',
            'm2epro_amazon_template_synchronization',

            'm2epro_ebay_account',
            'm2epro_ebay_account_store_category',
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
            'm2epro_ebay_dictionary_shipping',
            'm2epro_ebay_dictionary_shipping_category',
            'm2epro_ebay_feedback',
            'm2epro_ebay_feedback_template',
            'm2epro_ebay_item',
            'm2epro_ebay_listing',
            'm2epro_ebay_listing_other',
            'm2epro_ebay_listing_product',
            'm2epro_ebay_listing_product_variation',
            'm2epro_ebay_listing_product_variation_option',
            'm2epro_ebay_marketplace',
            'm2epro_ebay_message',
            'm2epro_ebay_motor_specific',
            'm2epro_ebay_order',
            'm2epro_ebay_order_item',
            'm2epro_ebay_order_external_transaction',
            'm2epro_ebay_template_description',
            'm2epro_ebay_template_general',
            'm2epro_ebay_template_general_calculated_shipping',
            'm2epro_ebay_template_general_payment',
            'm2epro_ebay_template_general_shipping',
            'm2epro_ebay_template_general_specific',
            'm2epro_ebay_template_selling_format',
            'm2epro_ebay_template_synchronization',

            'm2epro_buy_account',
            'm2epro_buy_dictionary_category',
            'm2epro_buy_item',
            'm2epro_buy_listing',
            'm2epro_buy_listing_other',
            'm2epro_buy_listing_product',
            'm2epro_buy_listing_product_variation',
            'm2epro_buy_listing_product_variation_option',
            'm2epro_buy_marketplace',
            'm2epro_buy_order',
            'm2epro_buy_order_item',
            'm2epro_buy_template_description',
            'm2epro_buy_template_general',
            'm2epro_buy_template_new_product',
            'm2epro_buy_template_new_product_core',
            'm2epro_buy_template_new_product_attribute',
            'm2epro_buy_template_selling_format',
            'm2epro_buy_template_synchronization',

            'm2epro_play_account',
            'm2epro_play_item',
            'm2epro_play_listing',
            'm2epro_play_listing_other',
            'm2epro_play_listing_product',
            'm2epro_play_listing_product_variation',
            'm2epro_play_listing_product_variation_option',
            'm2epro_play_marketplace',
            'm2epro_play_order',
            'm2epro_play_order_item',
            'm2epro_play_processed_inventory',
            'm2epro_play_template_description',
            'm2epro_play_template_general',
            'm2epro_play_template_selling_format',
            'm2epro_play_template_synchronization'
        );
    }

    private function getRemovedMySqlTables()
    {
        return array(
            'm2epro_ebay_listing_auto_filter',
            'm2epro_synchronization_run'
        );
    }
    //------------------------------------

    private function getConfigTablesV5()
    {
        return array(
            'ess_config',
            'm2epro_config'
        );
    }

    private function getConfigTablesV6()
    {
        return array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_cache_config',
            'm2epro_synchronization_config'
        );
    }

    //####################################

    private function updateInstallationVersionHistory($oldVersion, $newVersion)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTable('m2epro_cache_config');

        if (!in_array($tableName, $connection->listTables())) {
            return;
        }

        $currentGmtDate = Mage::getModel('core/date')->gmtDate();

        $mysqlColumns = array('group','key','value','update_date','create_date');
        $mysqlData = array(
            'group'       => '/installation/version/history/',
            'key'         => $newVersion,
            'value'       => $oldVersion,
            'update_date' => $currentGmtDate,
            'create_date' => $currentGmtDate
        );

        $connection->insertArray($tableName, $mysqlColumns, array($mysqlData));
    }

    private function updateCompilation()
    {
        defined('COMPILER_INCLUDE_PATH') && Mage::getModel('compiler/process')->run();
    }

    //####################################

    public function generateHash()
    {
        return sha1(microtime(1));
    }

    //####################################
}