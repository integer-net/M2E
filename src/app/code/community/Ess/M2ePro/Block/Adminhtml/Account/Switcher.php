<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Account_Switcher extends Ess_M2ePro_Block_Adminhtml_Component_Switcher
{
    protected $paramName = 'account';

    // ########################################

    public function getLabel()
    {
        return Mage::helper('M2ePro')->__($this->getComponentLabel('%component% Account'));
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
            /** @var $account Ess_M2ePro_Model_Account */

            if (!isset($items[$account->getComponentMode()]['label'])) {
                $label = '';
                if (isset($componentTitles[$account->getComponentMode()])) {
                    $label = $componentTitles[$account->getComponentMode()];
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