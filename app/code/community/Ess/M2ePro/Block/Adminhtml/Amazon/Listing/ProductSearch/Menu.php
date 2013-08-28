<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_ProductSearch_Menu extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/product_search/menu.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonCancelBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'productSearchMenu_cancel_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'class' => 'productSearchMenu_cancel_button'
        ) );
        $this->setChild('productSearchMenu_cancel_button', $buttonCancelBlock);
        //------------------------------

        parent::_beforeToHtml();
    }
}