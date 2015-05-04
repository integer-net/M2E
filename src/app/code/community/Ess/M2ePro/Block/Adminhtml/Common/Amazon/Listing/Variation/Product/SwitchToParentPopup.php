<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Variation_Product_SwitchToParentPopup
    extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingAddNewAsinManualPopup');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/variation/product/switch_to_parent_popup.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $data = array(
            'class'   => 'switch-to-parent-btn',
            'label'   => Mage::helper('M2ePro')->__('Yes')
        );
        $this->setChild(
            'yes_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        $data = array(
            'class'   => 'switch-to-parent-popup-close',
            'label'   => Mage::helper('M2ePro')->__('No')
        );
        $this->setChild(
            'no_btn',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data)
        );

        return $this;
    }
}