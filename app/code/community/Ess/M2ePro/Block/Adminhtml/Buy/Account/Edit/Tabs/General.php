<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyAccountEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/buy/account/tabs/general.phtml');
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
                    Mage::helper('M2ePro/Component_Buy')->getCachedObject('Account',$accountId)
                );
            }
        } else {
            $this->synchronizeProcessing = false;
        }

        $marketplaces = Mage::helper('M2ePro/Component_Buy')->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);

        $this->marketplace = true;
        if ($marketplaces->getSize() <= 0) {
            $this->marketplace = false;
        }
        //var_dump($this->marketplace); exit();

        $onclickAction = 'BuyAccountHandlerObj.update_password(\'ftp\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
            'label' => Mage::helper('M2ePro')->__('Update FTP Password'),
            'onclick' => $onclickAction,
            'class' => 'scalable'
        ));
        $this->setChild('buy_update_ftp_password', $buttonBlock);

        $onclickAction = 'BuyAccountHandlerObj.update_password(\'web\')';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
            'label' => Mage::helper('M2ePro')->__('Update Web Password'),
            'onclick' => $onclickAction,
            'class' => 'scalable'
        ));
        $this->setChild('buy_update_web_password', $buttonBlock);

        return parent::_beforeToHtml();
    }
}