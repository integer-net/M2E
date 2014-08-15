<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Edit_Tabs_ChannelSettings extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingEditTabsChannelSettings');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/tabs/channel_settings.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $accounts = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account')
                                                           ->setOrder('title', 'ASC')
                                                           ->toArray();
        $this->setData('accounts', $accounts['items']);
        //-------------------------------

        //-------------------------------
        $formData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $this->setData('attributes',
                       Mage::helper('M2ePro/Magento_Attribute')->getByAttributeSets($formData['attribute_sets']));
        //-------------------------------

        return parent::_beforeToHtml();
    }
}