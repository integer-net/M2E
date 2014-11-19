<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_Amazon_Listing_OtherController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/listings/listing_other');
    }

    //#############################################

    public function indexAction()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $this->_redirect('*/adminhtml_common_listing_other/index');
        }

        /** @var $block Ess_M2ePro_Block_Adminhtml_Common_Listing_Other */
        $block = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other');
        $block->enableAmazonTab();

        $this->getResponse()->setBody($block->getAmazonTabHtml());
    }

    public function gridAction()
    {
        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_common_amazon_listing_other_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################
}