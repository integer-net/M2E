<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_View_Fee_Errors extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingViewFeeErrors');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/view/fee/errors.phtml');
    }

    public function getErrors()
    {
        if (empty($this->_data['errors']) || !is_array($this->_data['errors'])) {
            throw new LogicException('Errors are not set.');
        }

        return $this->_data['errors'];
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $data = array(
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();',
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}