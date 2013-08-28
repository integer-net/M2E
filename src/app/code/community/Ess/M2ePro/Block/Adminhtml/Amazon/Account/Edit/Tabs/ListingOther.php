<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit_Tabs_ListingOther extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonAccountEditTabsListingOther');
        //------------------------------

        $this->setTemplate('M2ePro/amazon/account/tabs/listing_other.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->attributes = Mage::helper('M2ePro/Magento')->getAttributesByAllAttributeSets();

        $marketplacesData = array();

        $marketplaces = Mage::helper('M2ePro/Component_Amazon')->getCollection('Marketplace')
                                                               ->addFieldToFilter('status',
                                                                            Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
                                                               ->addFieldToFilter('developer_key',
                                                                                  array('notnull' => true))
                                                               ->getItems();

        foreach ($marketplaces as $marketplaceObj) {

            /** @var $marketplaceObj Ess_M2ePro_Model_Marketplace */

            $tempNewItem = $marketplaceObj->getData();

            $tempNewItem['account_data'] = array(
                'mode' => false,
                'related_store_id' => 0,
            );

            if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
                Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
            ) {

                /** @var $accountObj Ess_M2ePro_Model_Account */
                $accountObj = Mage::helper('M2ePro')->getGlobalValue('temp_data');

                $accountMarketplaceData = $accountObj->getChildObject()
                                                ->getMarketplaceItem($marketplaceObj->getId());

                if (!is_null($accountMarketplaceData)) {
                    $tempNewItem['account_data']['mode'] = true;
                    $tempNewItem['account_data']['related_store_id'] = $accountMarketplaceData['related_store_id'];
                }
            }

            $marketplacesData[] = $tempNewItem;
        }

        $this->marketplaces = $marketplacesData;

        return parent::_beforeToHtml();
    }
}