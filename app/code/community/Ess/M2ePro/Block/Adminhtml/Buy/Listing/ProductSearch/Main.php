<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_ProductSearch_Main extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/buy/listing/product_search/main.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonSubmitBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearch_submit_button',
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'class' => 'productSearch_submit_button submit'
        ) );
        $this->setChild('productSearch_submit_button', $buttonSubmitBlock);

        //------------------------------
        $buttonSubmitBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearch_reset_button',
            'label'   => Mage::helper('M2ePro')->__('Reset'),
            'class' => 'productSearch_reset_button submit'
        ) );
        $this->setChild('productSearch_reset_button', $buttonSubmitBlock);

        //------------------------------
        $buttonBackBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearch_back_button',
            'label'   => Mage::helper('M2ePro')->__('Back'),
            'class' => 'productSearch_back_button'
        ) );
        $this->setChild('productSearch_back_button', $buttonBackBlock);

        //------------------------------
        $buttonCancelBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearch_cancel_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearch_cancel_button'
        ) );
        $this->setChild('productSearch_cancel_button', $buttonCancelBlock);

        //------------------------------

        parent::_beforeToHtml();
    }
}