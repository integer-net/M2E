<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Marketplace_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $paramName = 'marketplace';

    // ########################################

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__($this->getComponentLabel('Choose %component% Marketplace'));
    }

    public function getItems()
    {
        $collection = Mage::getModel('M2ePro/Marketplace')->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('component_mode', 'ASC')
            ->setOrder('title', 'ASC');

        if (!is_null($this->componentMode)) {
            $collection->addFieldToFilter('component_mode', $this->componentMode);
        }

        if ($collection->getSize() < 2) {
            return array();
        }

        $items = array();

        foreach ($collection as $marketplace) {
            if (!isset($items[$marketplace->getComponentMode()]['label'])) {
                $label = '';
                if ($marketplace->isComponentModeEbay()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
                }
                if ($marketplace->isComponentModeAmazon()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
                }
                if ($marketplace->isComponentModeBuy()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
                }
                if ($marketplace->isComponentModePlay()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
                }
                $items[$marketplace->getComponentMode()]['label'] = $label;
            }

            $items[$marketplace->getComponentMode()]['value'][] = array(
                'value' => $marketplace->getId(),
                'label' => $marketplace->getTitle()
            );
        }

        return $items;
    }

    // ########################################

    public function getDefaultOptionName()
    {
        return Mage::helper('M2ePro')->__('All Marketplaces');
    }

    // ########################################
}