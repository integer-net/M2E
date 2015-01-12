<?php

class Ess_M2ePro_Model_Ebay_Magento_Product_Rule extends Ess_M2ePro_Model_Magento_Product_Rule
{
    // ####################################

    protected function getConditionInstance($prefix)
    {
        $conditionInstance = Mage::getModel('M2ePro/Ebay_Magento_Product_Rule_Condition_Combine')
            ->setRule($this)
            ->setPrefix($prefix)
            ->setValue(true)
            ->setId(1)
            ->setData($prefix, array());

        return $conditionInstance;
    }

    // ####################################
}