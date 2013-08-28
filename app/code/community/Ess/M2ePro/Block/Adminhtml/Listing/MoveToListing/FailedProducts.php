<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Listing_MoveToListing_FailedProducts extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/listing/moveToListing/failedProducts.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id'    => 'failedProducts_continue_button',
                'label' => Mage::helper('M2ePro')->__('Continue'),
                'class' => 'submit'
        ) );
        $this->setChild('failedProducts_continue_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id'    => 'failedProducts_back_button',
                'label' => Mage::helper('M2ePro')->__('Back'),
                'class' => 'scalable back',
        ) );
        $this->setChild('failedProducts_back_button',$buttonBlock);
        //------------------------------

        //------------------------------

        $this->setChild(
            'failedProducts_grid',
            $this->getLayout()->createBlock('M2ePro/adminhtml_listing_moveToListing_failedProducts_grid')
        );
        //------------------------------

        parent::_beforeToHtml();
    }
}