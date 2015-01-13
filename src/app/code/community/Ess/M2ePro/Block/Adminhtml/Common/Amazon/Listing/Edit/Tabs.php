<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingEditTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Configuration'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('settings', array(
            'label'   => Mage::helper('M2ePro')->__('Settings'),
            'title'   => Mage::helper('M2ePro')->__('Settings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_listing_edit_tabs_settings')
                              ->toHtml(),
        ));

        $this->addTab('channel', array(
            'label'   => Mage::helper('M2ePro')->__('Channel Settings'),
            'title'   => Mage::helper('M2ePro')->__('Channel Settings'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_listing_edit_tabs_channelSettings')
                              ->toHtml(),
        ));

        $this->addTab('products_filter', array(
            'label'   => Mage::helper('M2ePro')->__('Products Filter'),
            'title'   => Mage::helper('M2ePro')->__('Products Filter'),
            'content' => $this->getLayout()
                              ->createBlock('M2ePro/adminhtml_common_amazon_listing_edit_tabs_ProductsFilter')
                              ->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }
}