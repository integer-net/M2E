<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Test_Amazon_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('testAmazonTabs');
        //------------------------------

        $this->setTitle(Mage::helper('M2ePro')->__('Test Amazon Tabs'));
        $this->setDestElementId('edit_form');
    }

    protected function _beforeToHtml()
    {
        $this->addTab('general', array(
            'label'   => Mage::helper('M2ePro')->__('General'),
            'title'   => Mage::helper('M2ePro')->__('General'),
            'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_test_amazon_tabs_general')->toHtml(),
        ));

        $this->addTab('second', array(
            'label'   => Mage::helper('M2ePro')->__('second'),
            'title'   => Mage::helper('M2ePro')->__('second'),
            'content' => $this->getLayout()->createBlock('M2ePro/adminhtml_test_amazon_tabs_second')->toHtml(),
        ));

        $this->setActiveTab($this->getRequest()->getParam('tab', 'general'));

        return parent::_beforeToHtml();
    }

    public function getDestElementId()
    {
        return 'm2epro_amazon_content';
    }
}