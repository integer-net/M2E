<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Account_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $paramName = 'account';

    // ########################################

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__($this->getComponentLabel('Choose %component% Account'));
    }

    public function getItems()
    {
        $collection = Mage::getModel('M2ePro/Account')->getCollection()
                                                      ->setOrder('component_mode', 'ASC')
                                                      ->setOrder('title', 'ASC');

        if (!is_null($this->getData('component_mode'))) {
            $collection->addFieldToFilter('component_mode', $this->getData('component_mode'));
        }

        if ($collection->getSize() < 2) {
            return array();
        }

        $items = array();

        foreach ($collection as $account) {
            if (!isset($items[$account->getComponentMode()]['label'])) {
                $label = '';
                if ($account->isComponentModeEbay()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
                }
                if ($account->isComponentModeAmazon()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
                }
                if ($account->isComponentModeBuy()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
                }
                if ($account->isComponentModePlay()) {
                    $label = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
                }

                $items[$account->getComponentMode()]['label'] = $label;
            }

            $items[$account->getComponentMode()]['value'][] = array(
                'value' => $account->getId(),
                'label' => $account->getTitle()
            );
        }

        return $items;
    }

    // ########################################

    public function getDefaultOptionName()
    {
        return Mage::helper('M2ePro')->__('All Accounts');
    }

    // ########################################
}