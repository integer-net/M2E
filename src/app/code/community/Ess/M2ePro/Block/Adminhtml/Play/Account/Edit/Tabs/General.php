<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Play_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playAccountEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/play/account/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
            Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
        ) {

            /** @var $accountObj Ess_M2ePro_Model_Account */
            $accountObj = Mage::helper('M2ePro')->getGlobalValue('temp_data');

            $this->synchronizeProcessing = $accountObj->isLockedObject('server_synchronize');

            if (!$this->synchronizeProcessing) {
                $accountId = $accountObj->getId();

                Mage::helper('M2ePro')->unsetGlobalValue('temp_data');
                Mage::helper('M2ePro')->setGlobalValue(
                    'temp_data',
                    Mage::helper('M2ePro/Component_Play')->getCachedObject('Account',$accountId)
                );
            }
        } else {
            $this->synchronizeProcessing = false;
        }

        $marketplaces = Mage::helper('M2ePro/Component_Play')->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $this->marketplace = true;
        if ($marketplaces->getSize() <= 0) {
            $this->marketplace = false;
        }

        $onclickAction = 'PlayAccountHandlerObj.update_password()';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Update Password'),
                'onclick' => $onclickAction,
                'class' => 'scalable'
            ));
        $this->setChild('play_update_password', $buttonBlock);

        return parent::_beforeToHtml();
    }
}