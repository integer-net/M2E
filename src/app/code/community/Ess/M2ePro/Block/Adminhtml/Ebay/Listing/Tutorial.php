<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Tutorial extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTutorial');
        $this->setContainerId('ebay_listing_tutorial');
        $this->setTemplate('M2ePro/ebay/listing/tutorial.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                ->createBlock('adminhtml/widget_button')
                ->setData( array(
                    'label'   => Mage::helper('M2ePro')->__('Continue'),
                    'onclick' => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_listing/confirmTutorial').'\');',
                    'class' => 'confirm_tutorial'
                ) );
        $this->setChild('confirm_tutorial',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}