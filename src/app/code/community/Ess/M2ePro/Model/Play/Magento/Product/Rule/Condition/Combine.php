<?php

class Ess_M2ePro_Model_Play_Magento_Product_Rule_Condition_Combine
    extends Ess_M2ePro_Model_Magento_Product_Rule_Condition_Combine
{
    // ####################################

    public function __construct()
    {
        parent::__construct();
        $this->setType('M2ePro/Play_Magento_Product_Rule_Condition_Combine');
    }

    // ####################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|play|';
    }

    // ------------------------------------

    protected function getCustomLabel()
    {
        return Mage::helper('M2ePro')->__('Play Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
            $this->getOptions('M2ePro/Play_Magento_Product_Rule_Condition_Product', $attributes, array('play'))
            : array();
    }

    protected function getCustomOptionsAttributes()
    {
        return array(
            'play_sku' => Mage::helper('M2ePro')->__('Reference Code'),
            'play_general_id' => Mage::helper('M2ePro')->__('Identifier'),
            'play_online_qty' => Mage::helper('M2ePro')->__('Online QTY'),
            'play_online_price_gbr' => Mage::helper('M2ePro')->__('Online Price GBR'),
            'play_online_price_euro' => Mage::helper('M2ePro')->__('Online Price EUR'),
            'play_status' => Mage::helper('M2ePro')->__('Status')
        );
    }

    // ####################################
}