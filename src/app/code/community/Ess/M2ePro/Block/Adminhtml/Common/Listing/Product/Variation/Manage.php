<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation_Manage
    extends Ess_M2ePro_Block_Adminhtml_Common_Listing_Product_Variation
{
    //##############################################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('listingProductVariationEdit');
        //------------------------------

        $this->setTemplate('M2ePro/common/listing/product/variation/manage.phtml');
    }

    //##############################################################

    protected function _beforeToHtml()
    {
        $this->_prepareButtons();

        return parent::_beforeToHtml();
    }

    //##############################################################

    protected function _prepareButtons()
    {
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Add More Variations'),
                'onclick' => '',
                'class' => 'add',
                'id' => 'add_more_variation_button'
            ));
        $this->setChild('add_more_variation_button', $buttonBlock);

        //------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => '',
                'class' => 'confirm',
                'id' => 'variation_manage_confirm'
            ));
        $this->setChild('variation_manage_confirm', $buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Close'),
                'onclick' => 'ListingProductVariationHandlerObj.managePopup.close();',
                'class' => 'close',
                'id' => 'variation_manage_close'
            ));
        $this->setChild('variation_manage_close', $buttonBlock);

        //------------------------------

        $onClick = 'ListingProductVariationHandlerObj.manageGenerateAction(false);';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Generate All Combinations'),
                'onclick' => $onClick,
                'class' => 'button',
                'id' => 'variation_manage_generate_all'
            ));
        $this->setChild('variation_manage_generate_all', $buttonBlock);

        $onClick = 'ListingProductVariationHandlerObj.manageGenerateAction(true);';
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('M2ePro')->__('Generate Non-Existing Combinations'),
                'onclick' => $onClick,
                'class' => 'button',
                'id' => 'variation_manage_generate_unique'
            ));
        $this->setChild('variation_manage_generate_unique', $buttonBlock);
    }

    //##############################################################
}