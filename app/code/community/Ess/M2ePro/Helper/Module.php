<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module extends Mage_Core_Helper_Abstract
{
    const SERVER_LOCK_NO = 0;
    const SERVER_LOCK_YES = 1;

    const SERVER_MESSAGE_TYPE_NOTICE = 0;
    const SERVER_MESSAGE_TYPE_ERROR = 1;
    const SERVER_MESSAGE_TYPE_WARNING = 2;
    const SERVER_MESSAGE_TYPE_SUCCESS = 3;

    const MAINTENANCE_COOKIE_KEY = 'M2ePro_maintenance';
    const MAINTENANCE_COOKIE_DURATION = 3600;

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Config_Module
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Module');
    }

    // ########################################

    public function getName()
    {
        return 'm2epro';
    }

    public function getVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Ess_M2ePro/version');
        $version = strtolower($version);

        if (Mage::helper('M2ePro')->getCacheValue('MODULE_VERSION_UPDATER') === false) {
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/modules/',$this->getName(),$version.'.r'.$this->getRevision()
            );
            Mage::helper('M2ePro')->setCacheValue('MODULE_VERSION_UPDATER',array(),array(),60*60*24);
        }

        return $version;
    }

    public function getRevision()
    {
        $revision = '5006';

        if ($revision == str_replace('|','#','|REVISION_VERSION|')) {
            $revision = (int)exec('svnversion');
            $revision == 0 && $revision = 'N/A';
            $revision .= '-dev';
        }

        return $revision;
    }

    //----------------------------------------

    public function getVersionWithRevision()
    {
        return $this->getVersion().'r'.$this->getRevision();
    }

    // ########################################

    public function getMenuRootNodeLabel()
    {
        $componentsLabels = array();

        if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        }

        if (Mage::helper('M2ePro/Component_Amazon')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
        }

        if (Mage::helper('M2ePro/Component_Buy')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
        }

        if (Mage::helper('M2ePro/Component_Play')->isActive()) {
            $componentsLabels[] = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
        }

        if (count($componentsLabels) <= 0 || count($componentsLabels) > 2) {
            return Mage::helper('M2ePro')->__('Sell On Multi-Channels');
        }

        return implode(' / ', $componentsLabels);
    }

    public function isLockedByServer()
    {
        $lock = (int)Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'lock'
        );

        $validValues = array(self::SERVER_LOCK_NO, self::SERVER_LOCK_YES);

        if (in_array($lock,$validValues)) {
            return $lock;
        }

        return self::SERVER_LOCK_NO;
    }

    public function getServerMessages()
    {
        $messages = Mage::helper('M2ePro/Ess')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'messages'
        );

        $messages = (!is_null($messages) && $messages != '') ?
                    (array)json_decode((string)$messages,true) :
                    array();

        function getServerMessagesFilterModuleMessages($message) {

            if (!isset($message['title']) || !isset($message['text']) || !isset($message['type'])) {
                return false;
            }

            return true;
        }

        $messages = array_filter($messages,'getServerMessagesFilterModuleMessages');
        !is_array($messages) && $messages = array();

        return $messages;
    }

    // ########################################

    public function getMySqlTables()
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

    public function getHorizontalTables()
    {
        $components = Mage::helper('M2ePro/Component')->getComponents();
        $mySqlTables = Mage::helper('M2ePro/Module')->getMySqlTables();

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

    public function isMaintenanceEnabled()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/debug/maintenance/', 'mode');
    }

    public function isMaintenanceOwner()
    {
        return (bool)Mage::app()->getCookie()->get(self::MAINTENANCE_COOKIE_KEY);
    }

    //-----------------------------------------

    public function isMaintenanceExpired()
    {
        $restoreDate = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/debug/maintenance/', 'restore_date'
        );

        if (!$restoreDate) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if ($currentTimeStamp >= strtotime($restoreDate)) {
            return true;
        }

        return false;
    }

    public function prolongMaintenanceRestoreDate()
    {
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $restoreDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + self::MAINTENANCE_COOKIE_DURATION);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', $restoreDate);
    }

    //-----------------------------------------

    public function enableMaintenance()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'mode', 1);

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $restoreDate = Mage::helper('M2ePro')->getDate($currentTimeStamp + self::MAINTENANCE_COOKIE_DURATION);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', $restoreDate);

        Mage::app()->getCookie()->set(Ess_M2ePro_Helper_Module::MAINTENANCE_COOKIE_KEY, 'true', 60*60*24);
    }

    public function disableMaintenance()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'mode', 0);
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/debug/maintenance/', 'restore_date', null);
        Mage::app()->getCookie()->set(Ess_M2ePro_Helper_Module::MAINTENANCE_COOKIE_KEY, '', 0);
    }

    // ########################################

    public function clearCache()
    {
        Mage::helper('M2ePro')->removeAllCacheValues();
    }

    // ########################################
}