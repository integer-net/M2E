<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Listing_ProductSearch_Main extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/common/play/listing/product_search/main.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'id'    => 'productSearch_submit_button',
            'label' => Mage::helper('M2ePro')->__('Search'),
            'class' => 'productSearch_submit_button submit'
        );
        $buttonSubmitBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_submit_button', $buttonSubmitBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'    => 'productSearch_back_button',
            'label' => Mage::helper('M2ePro')->__('Back'),
            'class' => 'productSearch_back_button'
        );
        $buttonBackBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_back_button', $buttonBackBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'    => 'productSearch_cancel_button',
            'label' => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearch_cancel_button'
        );
        $buttonCancelBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('productSearch_cancel_button', $buttonCancelBlock);
        //------------------------------

        parent::_beforeToHtml();
    }
}