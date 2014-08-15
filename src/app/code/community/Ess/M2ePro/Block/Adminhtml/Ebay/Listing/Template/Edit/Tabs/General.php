<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Template_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateEditTabsGeneral');
        //------------------------------
    }

    // ####################################

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_edit_general_help');
        $this->setChild('help', $helpBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('payment', $switcherBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('shipping', $switcherBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'template_nick' => Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN,
        );
        $switcherBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_template_switcher');
        $switcherBlock->setData($data);

        $this->setChild('return', $switcherBlock);
        //------------------------------

        return $this;
    }

    // ####################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('help')
            . $this->getChildHtml('payment')
            . $this->getChildHtml('shipping')
            . $this->getChildHtml('return')
        ;
    }

    // ####################################
}