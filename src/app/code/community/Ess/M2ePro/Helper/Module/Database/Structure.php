<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Helper_Module_Database_Structure extends Mage_Core_Helper_Abstract
{
    const TABLE_GROUP_CONFIGS        = 'configs';
    const TABLE_GROUP_ACCOUNTS       = 'accounts';
    const TABLE_GROUP_MARKETPLACES   = 'marketplaces';
    const TABLE_GROUP_LISTINGS       = 'listings';
    const TABLE_GROUP_LISTINGS_OTHER = 'listings_other';
    const TABLE_GROUP_LOGS           = 'logs';
    const TABLE_GROUP_ITEMS          = 'items';
    const TABLE_GROUP_DICTIONARY     = 'dictionary';
    const TABLE_GROUP_ORDERS         = 'orders';
    const TABLE_GROUP_TEMPLATES      = 'templates';
    const TABLE_GROUP_OTHER          = 'other';

    //#############################################

    public function getMySqlTables()
    {
        return array(
            'm2epro_primary_config',
            'm2epro_config',
            'm2epro_cache_config',
            'm2epro_synchronization_config',

            'm2epro_lock_item',
            'm2epro_locked_object',
            'm2epro_product_change',
            'm2epro_operation_history',
            'm2epro_processing_request',
            'm2epro_synchronization_log',

            'm2epro_attribute_set',
            'm2epro_exceptions_filters',
            'm2epro_stop_queue',
            'm2epro_migration_v6',
            'm2epro_wizard',

            'm2epro_account',
            'm2epro_marketplace',

            'm2epro_template_selling_format',
            'm2epro_template_synchronization',

            'm2epro_listing',
            'm2epro_listing_category',
            'm2epro_listing_log',
            'm2epro_listing_other',
            'm2epro_listing_other_log',
            'm2epro_listing_product',
            'm2epro_listing_product_variation',
            'm2epro_listing_product_variation_option',

            'm2epro_order',
            'm2epro_order_change',
            'm2epro_order_item',
            'm2epro_order_log',
            'm2epro_order_repair',

            'm2epro_ebay_account',
            'm2epro_ebay_account_store_category',
            'm2epro_ebay_account_policy',
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
            'm2epro_ebay_dictionary_motor_specific',
            'm2epro_ebay_dictionary_motor_ktype',
            'm2epro_ebay_dictionary_shipping',
            'm2epro_ebay_dictionary_shipping_category',
            'm2epro_ebay_feedback',
            'm2epro_ebay_feedback_template',
            'm2epro_ebay_item',
            'm2epro_ebay_listing',
            'm2epro_ebay_listing_auto_category',
            'm2epro_ebay_listing_auto_category_group',
            'm2epro_ebay_listing_other',
            'm2epro_ebay_listing_product',
            'm2epro_ebay_listing_product_variation',
            'm2epro_ebay_listing_product_variation_option',
            'm2epro_ebay_marketplace',
            'm2epro_ebay_order',
            'm2epro_ebay_order_item',
            'm2epro_ebay_order_external_transaction',
            'm2epro_ebay_template_category',
            'm2epro_ebay_template_category_specific',
            'm2epro_ebay_template_description',
            'm2epro_ebay_template_other_category',
            'm2epro_ebay_template_payment',
            'm2epro_ebay_template_payment_service',
            'm2epro_ebay_template_policy',
            'm2epro_ebay_template_return',
            'm2epro_ebay_template_shipping',
            'm2epro_ebay_template_shipping_calculated',
            'm2epro_ebay_template_shipping_service',
            'm2epro_ebay_template_selling_format',
            'm2epro_ebay_template_synchronization',

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
            'm2epro_amazon_template_new_product',
            'm2epro_amazon_template_new_product_description',
            'm2epro_amazon_template_new_product_specific',
            'm2epro_amazon_template_selling_format',
            'm2epro_amazon_template_synchronization',

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
            'm2epro_play_template_selling_format',
            'm2epro_play_template_synchronization'
        );
    }

    public function getGroupedMySqlTables()
    {
        $mySqlGroups = array(
            self::TABLE_GROUP_CONFIGS        => '/_config$/',
            self::TABLE_GROUP_ACCOUNTS       => '/_account/',
            self::TABLE_GROUP_MARKETPLACES   => '/(?<!dictionary)_marketplace$/',
            self::TABLE_GROUP_LISTINGS       => '/_listing$/',
            self::TABLE_GROUP_LISTINGS_OTHER => '/_listing_other$/',
            self::TABLE_GROUP_LOGS           => '/_log$/',
            self::TABLE_GROUP_ITEMS          => '/(?<!lock)(?<!order)_item$/',
            self::TABLE_GROUP_DICTIONARY     => '/_dictionary_/',
            self::TABLE_GROUP_ORDERS         => '/_order/',
            self::TABLE_GROUP_TEMPLATES      => '/_template_/',
            self::TABLE_GROUP_OTHER          => '/.+/'
        );

        $result = array();
        foreach ($this->getMySqlTables() as $table) {
            foreach ($mySqlGroups as $group => $expression) {

                if (preg_match($expression, $table)) {
                    $result[$table] = $group;
                    break;
                }
            }
        }
        return $result;
    }

    public function getHorizontalTables()
    {
        $components = Mage::helper('M2ePro/Component')->getComponents();
        $mySqlTables = $this->getMySqlTables();

        // minimal amount of child tables to be a horizontal table
        $minimalAmount = 2;

        $result = array();
        foreach ($mySqlTables as $mySqlTable) {

            $tempComponentTables = array();
            $mySqlTableCropped = str_replace('m2epro_','',$mySqlTable);

            foreach ($components as $component) {

                $needComponentTable = "m2epro_{$component}_{$mySqlTableCropped}";

                if (in_array($needComponentTable, $mySqlTables)) {
                    $tempComponentTables[$component] = $needComponentTable;
                } else {
                    break;
                }
            }

            if (count($tempComponentTables) >= $minimalAmount) {
                $result[$mySqlTable] = $tempComponentTables;
            }
        }

        return $result;
    }

    // --------------------------------------------

    public function isTableExists($tableName)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $databaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $result = $connRead->query("SHOW TABLE STATUS FROM `{$databaseName}` WHERE `name` = '{$tableName}'")
                           ->fetch() ;

        return $result !== false;
    }

    public function isTableStatusOk($tableName)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        if (!$this->isTableExists($tableName)) {
            throw new Exception("Table '{$tableName}' is not exists.");
        }

        $tableStatus = true;

        try {

            $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);
            $connRead->select()->from($tableName, new Zend_Db_Expr('1'))
                     ->limit(1)
                     ->query();

        } catch (Exception $e) {
            $tableStatus = false;
        }

        return $tableStatus;
    }

    public function isTableReady($tableName)
    {
        return $this->isTableExists($tableName) && $this->isTableStatusOk($tableName);
    }

    // --------------------------------------------

    public function getCountOfRecords($tableName)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $count = $connRead->select()->from($tableName, new Zend_Db_Expr('COUNT(*)'))
                          ->query()
                          ->fetchColumn();

        return (int)$count;
    }

    public function getDataLength($tableName)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $databaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $dataLength = $connRead->select()->from('information_schema.tables', array('data_length'))
                               ->where('`table_name` = ?', $tableName)
                               ->where('`table_schema` = ?', $databaseName)
                               ->query()
                               ->fetchColumn();

        return round($dataLength / 1024 / 1024, 2);
    }

    // --------------------------------------------

    public function getTablesInfo()
    {
        $tablesInfo = array();
        foreach ($this->getMySqlTables() as $currentTable) {
            $currentTableInfo = $this->getTableInfo($currentTable);
            $currentTableInfo && $tablesInfo[$currentTable] = $currentTableInfo;
        }

        return $tablesInfo;
    }

    public function getTableInfo($tableName)
    {
        if (!$this->isTableExists($tableName)) {
            return false;
        }

        $moduleTableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        $stmtQuery = Mage::getResourceModel('core/config')->getReadConnection()->query(
            "SHOW COLUMNS FROM {$moduleTableName}"
        );

        $result = array();
        $afterPosition = '';

        while ($row = $stmtQuery->fetch()) {

            $result[strtolower($row['Field'])] = array(
                'name'     => strtolower($row['Field']),
                'type'     => strtolower($row['Type']),
                'null'     => strtolower($row['Null']),
                'key'      => strtolower($row['Key']),
                'default'  => strtolower($row['Default']),
                'extra'    => strtolower($row['Extra']),
                'after'    => $afterPosition
            );

            $afterPosition = strtolower($row['Field']);
        }

        return $result;
    }

    public function getTableModel($tableName)
    {
        $tableModels = Mage::getConfig()->getNode('global/models/M2ePro_mysql4/entities');

        foreach ($tableModels->asArray() as $model => $infoData) {
            if ($infoData['table'] == $tableName) {
                return $model;
            }
        }

        return null;
    }

    public function getIdColumn($table)
    {
        $tableModel = $this->getTableModel($table);
        $tableModel = Mage::getModel('M2ePro/'.$tableModel);

        return $tableModel->getIdFieldName();
    }

    // --------------------------------------------

    public function getConfigSnapshot($table)
    {
        $tableModel = $this->getTableModel($table);

        $tableModel = Mage::getModel('M2ePro/'.$tableModel);
        $collection = $tableModel->getCollection()->toArray();

        $result = array();
        foreach ($collection['items'] as $item) {

            $codeHash = strtolower($item['group']).'#'.strtolower($item['key']);
            $result[$codeHash] = array(
                'group'  => $item['group'],
                'key'    => $item['key'],
                'value'  => $item['value'],
            );
        }

        return $result;
    }

    // --------------------------------------------

    public function getStoreRelatedColumns()
    {
        $result = array();

        $simpleColumns = array('store_id', 'related_store_id');
        $jsonColumns   = array('magento_orders_settings', 'marketplaces_data');

        foreach ($this->getTablesInfo() as $tableName => $tableInfo) {
            foreach ($tableInfo as $columnName => $columnInfo) {

                if (in_array($columnName, $simpleColumns)) {
                    $result[$tableName][] = array('name' => $columnName, 'type' => 'int');
                }

                if (in_array($columnName, $jsonColumns)) {
                    $result[$tableName][] = array('name' => $columnName, 'type' => 'json');
                }
            }
        }

        return $result;
    }

    //#############################################
}