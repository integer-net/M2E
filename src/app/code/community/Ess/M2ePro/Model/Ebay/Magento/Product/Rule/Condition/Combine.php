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

    protected function getEbaySelectOptions()
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

    public function getNewChildSelectOptions()
    {
        $productAttributes = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule_Condition_Product')->getAttributeOption();

        $ebayValues = array();
        foreach ($this->getEbaySelectOptions() as $code => $label) {
            unset($productAttributes[$code]);

            $ebayValues[] = array(
                'value' => 'M2ePro/Ebay_Magento_Product_Rule_Condition_Product|ebay|'.$code,
                'label' => $label,
            );
        }

        $attributes = array();
        foreach ($productAttributes as $code => $label) {
            $attributes[] = array(
                'value' => 'M2ePro/Magento_Product_Rule_Condition_Product|'.$code,
                'label' => $label
            );
        }

        return array(
            array('value'=>'', 'label'=>Mage::helper('rule')->__('Please choose a condition to add...')),
            array(
                'label' => Mage::helper('M2ePro')->__('Conditions Combination'),
                'value' => 'M2ePro/Ebay_Magento_Product_Rule_Condition_Combine|ebay|',
            ),
            array('label' => Mage::helper('M2ePro')->__('eBay Values'), 'value' => $ebayValues),
            array('label' => Mage::helper('M2ePro')->__('Product Attribute'), 'value' => $attributes),
        );
    }

    // ####################################
}