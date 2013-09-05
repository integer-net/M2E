<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Same_Chooser extends Mage_Adminhtml_Block_Widget_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingCategorySameChooser');
        //------------------------------

        $this->_headerText = Mage::helper('M2ePro')->__('eBay Same Categories');

        $this->setTemplate('M2ePro/ebay/listing/category/same/chooser.phtml');

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/*', array('_current' => true, 'step' => 1)) . '\');'
        ));

        $url = $this->getUrl('*/*/*', array(
            'step' => 2,
            '_current' => true
        ));
        $redirectUrl = $this->getUrl('*/*/*', array(
            'step' => 3,
            '_current' => true
        ));
        $this->_addButton('next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "EbayListingCategoryChooserHandlerObj.submitData('".$url."', '".$redirectUrl."');"
        ));
    }

    // ####################################

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        // --------------------------------------
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', $this->getRequest()->getParam('listing_id')
        );
        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header','',
            array('listing' => $listing)
        );
        $this->setChild('view_header', $viewHeaderBlock);
        // --------------------------------------

        // --------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $internalData = $this->getData('internal_data');
        $attributes = $this->getData('attributes');

        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setMarketplaceId($listingData['marketplace_id']);
        $chooserBlock->setAccountId($listingData['account_id']);
        $chooserBlock->setAttributes(array_unique($attributes));

        if (!empty($internalData)) {
            $chooserBlock->setInternalData($internalData);
        }

        $this->setChild('category_chooser', $chooserBlock);
        // --------------------------------------
    }

    // ####################################
}