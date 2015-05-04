<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Magento_Product_Rule_Custom_BuySku
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'buy_sku';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Reference ID');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('buy_sku');
    }

    // #################################################
}