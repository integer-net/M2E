<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonAccountEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/account/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        $marketplacesData = array();
        $applicationName = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/amazon/', 'application_name');

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
                    Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Account',$accountId)
                );
            }
        } else {
            $this->synchronizeProcessing = false;
        }

        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
                                                               ->addFieldToFilter('status',
                                                                            Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                                                               ->addFieldToFilter('developer_key',
                                                                                  array('notnull' => true))
                                                               ->getItems();

        foreach ($marketplaces as $marketplaceObj) {

            /** @var $marketplaceObj Ess_M2ePro_Model_Marketplace */

            /** @var $amazonMarketplaceObj Ess_M2ePro_Model_Amazon_Marketplace */
            $amazonMarketplaceObj = $marketplaceObj->getChildObject();

            $tempNewItem = $marketplaceObj->getData();
            $tempNewItem['application_name'] = $applicationName;
            $tempNewItem['locked'] = false;

            $tempNewItem['account_data'] = array(
                'mode' => false,
                'server_hash' => '',
                'merchant_id' => '',
                'related_store_id' => 0,
                'register_url' => Mage::helper('M2ePro/Component_Amazon')->getRegisterUrl($marketplaceObj->getId())
            );

            if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
                Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
            ) {

                /** @var $accountObj Ess_M2ePro_Model_Account */
                $accountObj = Mage::helper('M2ePro')->getGlobalValue('temp_data');

                /** @var $amazonAccountObj Ess_M2ePro_Model_Amazon_Account */
                $amazonAccountObj = $accountObj->getChildObject();

                $accountMarketplaceData = $amazonAccountObj->getMarketplaceItem($marketplaceObj->getId());

                if (!is_null($accountMarketplaceData)) {
                    $tempNewItem['locked'] = Mage::getModel('M2ePro/Template_General')->getCollection()
                                                                                    ->addFieldToFilter('account_id',
                                                                                                   $accountObj->getId())
                                                                                    ->addFieldToFilter('marketplace_id',
                                                                                               $marketplaceObj->getId())
                                                                                    ->getSize();
                    $tempNewItem['account_data']['mode'] = true;
                    $tempNewItem['account_data']['server_hash'] = $accountMarketplaceData['server_hash'];
                    $tempNewItem['account_data']['merchant_id'] = $accountMarketplaceData['merchant_id'];
                    $tempNewItem['account_data']['related_store_id'] = $accountMarketplaceData['related_store_id'];
                }
            }

            $marketplacesData[] = $tempNewItem;
        }

        $this->marketplaces = $marketplacesData;
        //var_dump($this->marketplaces); exit();

        return parent::_beforeToHtml();
    }
}