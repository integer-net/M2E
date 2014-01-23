<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Database extends Mage_Core_Helper_Abstract
{
    // ########################################

    const TABLE_GROUP_CONFIGS        = 'configs';
    const TABLE_GROUP_ACCOUNTS       = 'accounts';
    const TABLE_GROUP_MARKETPLACES   = 'marketplaces';
    const TABLE_GROUP_LISTINGS       = 'listings';
    const TABLE_GROUP_LISTINGS_OTHER = 'listings_other';
    const TABLE_GROUP_ITEMS          = 'items';
    const TABLE_GROUP_DICTIONARY     = 'dictionary';
    const TABLE_GROUP_ORDERS         = 'orders';
    const TABLE_GROUP_TEMPLATES      = 'templates';
    const TABLE_GROUP_OTHER          = 'other';

    // ########################################

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
            'm2epro_processing_request',
            'm2epro_stop_queue',
            'm2epro_migration_v6',

            'm2epro_account',
            'm2epro_marketplace',
            'm2epro_attribute_set',
            'm2epro_exceptions_filters',
            'm2epro_wizard',

            'm2epro_order',
            'm2epro_order_change',
            'm2epro_order_item',
            'm2epro_order_log',
            'm2epro_order_repair',

            'm2epro_synchronization_log',
            'm2epro_synchronization_run',

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

            'm2epro_ebay_account',
            'm2epro_ebay_account_store_category',
            'm2epro_ebay_account_policy',
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
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
            'm2epro_ebay_motor_specific',
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
        $mySqlTables = $this->getMySqlTables();

        $mySqlGroups = array(
            self::TABLE_GROUP_CONFIGS        => '/_config$/',
            self::TABLE_GROUP_ACCOUNTS       => '/_account/',
            self::TABLE_GROUP_MARKETPLACES   => '/(?<!dictionary)_marketplace$/',
            self::TABLE_GROUP_LISTINGS       => '/_listing(?!_other)/',
            self::TABLE_GROUP_LISTINGS_OTHER => '/_listing_other/',
            self::TABLE_GROUP_ITEMS          => '/(?<!lock)(?<!order)_item$/',
            self::TABLE_GROUP_DICTIONARY     => '/_dictionary_/',
            self::TABLE_GROUP_ORDERS         => '/_order/',
            self::TABLE_GROUP_TEMPLATES      => '/_template_/',
            self::TABLE_GROUP_OTHER          => '/.+/'
        );

        $result = array();
        foreach ($mySqlTables as $table) {

            foreach ($mySqlGroups as $group => $expression) {

                if (preg_match($expression, $table)) {
                    $result[$table] = $group;
                    break;
                }
            }
        }

        return $result;
    }

    // ########################################

    public function getHorizontalTables()
    {
        $components = Mage::helper('M2ePro/Component')->getComponents();
        $mySqlTables = Mage::helper('M2ePro/Module_Database')->getMySqlTables();

        $result = array();

        foreach ($mySqlTables as $mySqlTable) {

            $tempComponentTables = array();
            $mySqlTableCropped = str_replace('m2epro_','',$mySqlTable);

            foreach ($components as $component) {

                $needComponentTable = 'm2epro_'.$component.'_'.$mySqlTableCropped;

                if (in_array($needComponentTable, $mySqlTables)) {
                    $tempComponentTables[$component] = $needComponentTable;
                } else {
                    break;
                }
            }

            if (count($tempComponentTables) == count($components)) {
                $result[$mySqlTable] = $tempComponentTables;
            }
        }

        return $result;
    }

    // ########################################

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

    // ########################################
}