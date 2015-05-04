<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Magento_Product_Rule_Custom_PlayOnlinePriceEuro
    extends Ess_M2ePro_Model_Magento_Product_Rule_Custom_Abstract
{
    // #################################################

    public function getAttributeCode()
    {
        return 'play_online_price_euro';
    }

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__('Online Price EUR');
    }

    public function getValueByProductInstance(Mage_Catalog_Model_Product $product)
    {
        return $product->getData('online_price_euro');
    }

    // #################################################
}