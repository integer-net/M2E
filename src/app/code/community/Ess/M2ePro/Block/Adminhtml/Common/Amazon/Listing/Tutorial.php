<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Tutorial extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingTutorial');
        $this->setContainerId('amazon_listing_tutorial');
        $this->setTemplate('M2ePro/common/amazon/listing/tutorial.phtml');
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_listing/confirmTutorial',
            array(
                'component' => Ess_M2ePro_Helper_Component_Amazon::NICK
            )
        );
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Continue'),
            'onclick' => 'setLocation(\''.$url.'\');',
            'class' => 'confirm_tutorial'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_tutorial',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}