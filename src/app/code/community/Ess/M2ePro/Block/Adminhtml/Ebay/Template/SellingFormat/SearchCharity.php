<?php

/*
* @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_SearchCharity
    extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingTemplateSearchCharity');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/sellingFormat/searchCharity.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        //------------------------------
        $data = array(
            'id'      => 'close_button',
            'class'   => 'close_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('close_button', $buttonBlock);

        //------------------------------

        $dataSubmit = array(
            'id'    => 'searchCharity_submit',
            'class' => 'submit_button',
            'label' => Mage::helper('M2ePro')->__('Search'),
            'onclick' => 'EbayTemplateSellingFormatHandlerObj.searchCharity()'
        );
        $buttonSubmitBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($dataSubmit);
        $this->setChild('submit_button', $buttonSubmitBlock);

        $dataReset = array(
            'id'    => 'searchCharity_reset',
            'class' => 'reset_button',
            'label' => Mage::helper('M2ePro')->__('Reset'),
        );
        $buttonResetBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($dataReset);
        $this->setChild('reset_button', $buttonResetBlock);
    }

}