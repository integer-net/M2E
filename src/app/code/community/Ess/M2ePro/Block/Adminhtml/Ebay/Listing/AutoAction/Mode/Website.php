<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Website extends Mage_Adminhtml_Block_Widget_Form
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAutoActionModeWebsite');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/auto_action/mode/website.phtml');
    }

    // ####################################

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/*/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    // ####################################

    public function hasFormData()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        return $listing->getData('auto_mode') == Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE;
    }

    public function getFormData()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        return array(
            'auto_website_adding_mode' => $listing->getData('auto_website_adding_mode'),
            'auto_website_adding_template_category_id' => $listing->getData('auto_website_adding_template_category_id'),
            'auto_website_deleting_mode' => $listing->getData('auto_website_deleting_mode')
        );
    }

    public function getDefault()
    {
        return array(
            'auto_website_adding_mode' => Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE,
            'auto_website_adding_template_category_id' => NULL,
            'auto_website_deleting_mode' => Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_STOP_REMOVE,
        );
    }

    // ####################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'id'      => 'confirm_button',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Save'),
            'onclick' => 'EbayListingAutoActionHandlerObj.confirm();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'continue_button',
            'class'   => 'continue_button next',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'style'   => 'display: none;',
            'onclick' => '',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        //------------------------------

        //------------------------------
        $breadcrumb = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_autoAction_mode_breadcrumb');
        $breadcrumb->setData('step', 1);
        $this->setChild('breadcrumb', $breadcrumb);
        //------------------------------
    }

    // ####################################

    public function getWebsiteName()
    {
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('listing');

        return Mage::helper('M2ePro/Magento_Store')->getWebsiteName($listing->getStoreId());
    }

    // ####################################
}
