<?php

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
{
    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Ebay_Magento_Product_Rule_Condition_Combine');
    }

    // ####################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|ebay|';
    }

    protected function getCustomLabel()
    {
        return Mage::helper('M2ePro')->__('eBay Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
            $this->getOptions('M2ePro/Ebay_Magento_Product_Rule_Condition_Product', $attributes, array('ebay'))
            : array();
    }

    protected function getCustomOptionsAttributes()
    {
        return array(
            'ebay_item_id' => Mage::helper('M2ePro')->__('Item ID'),
            'ebay_online_title' => Mage::helper('M2ePro')->__('Title'),
            'ebay_online_sku' => Mage::helper('M2ePro')->__('SKU'),
            'ebay_online_category_id' => Mage::helper('M2ePro')->__('Category ID'),
            'ebay_online_category_path' => Mage::helper('M2ePro')->__('Category Path'),
            'ebay_available_qty' => Mage::helper('M2ePro')->__('Available QTY'),
            'ebay_sold_qty' => Mage::helper('M2ePro')->__('Sold QTY'),
            'ebay_online_buyitnow_price' => Mage::helper('M2ePro')->__('"Buy It Now" Price'),
            'ebay_online_start_price' => Mage::helper('M2ePro')->__('Start Price'),
            'ebay_online_reserve_price' => Mage::helper('M2ePro')->__('Reserve Price'),
            'ebay_status' => Mage::helper('M2ePro')->__('Status'),
            'ebay_start_date' => Mage::helper('M2ePro')->__('Start Date'),
            'ebay_end_date' => Mage::helper('M2ePro')->__('End Date'),
        );
    }

    // ####################################
}