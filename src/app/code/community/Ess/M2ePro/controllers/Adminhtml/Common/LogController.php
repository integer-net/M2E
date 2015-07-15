<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_LogController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Activity Logs'));

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/DropDown.css')

            ->addJs('M2ePro/Plugin/DropDown.js')
            ->addJs('M2ePro/LogHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/logs');
    }

    public function preDispatch()
    {
        $channel = $this->getRequest()->getParam('channel', false);

        if (!$channel) {
            Mage::helper('M2ePro/View_Common_Component')->isAmazonDefault() &&
            $this->getRequest()->setParam('channel', Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_AMAZON);
            Mage::helper('M2ePro/View_Common_Component')->isBuyDefault()    &&
            $this->getRequest()->setParam('channel', Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::CHANNEL_ID_BUY);
        }

        return parent::preDispatch();
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    //#############################################

    public function listingAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_common/logs/listing')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $id);

            if (!$listing->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
                return $this->_redirect('*/*/index');
            }

            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log');
        }

        if (empty($block)) {
            $block = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::TAB_ID_LISTING)
            );
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Listings Log'))
             ->_addContent($block)
             ->renderLayout();
    }

    public function listingGridAction()
    {
        $id = $this->getRequest()->getParam('id', false);
        if ($id) {
            $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $id);

            if (!$listing->getId()) {
                return;
            }
        }

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_listing_log_grid', '', array(
                'channel' => $this->getRequest()->getParam('channel')
            ))->toHtml();
        $this->getResponse()->setBody($response);
    }

    // -------------------------------

    public function listingProductAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing Product does not exist.'));
                return $this->_redirect('*/*/index');
            }
        }

        $this->_initAction();

        $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log');

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingProductGridAction()
    {
        $listingProductId = $this->getRequest()->getParam('listing_product_id', false);
        if ($listingProductId) {
            $listingProduct = Mage::helper('M2ePro/Component')
                ->getUnknownObject('Listing_Product', $listingProductId);

            if (!$listingProduct->getId()) {
                return;
            }
        }

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_common_listing_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // -------------------------------

    public function listingOtherAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_common/logs/listing_other')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('3rd Party Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        if ($model->getId()) {
            $block = $this->getLayout()->createBlock('M2ePro/adminhtml_common_listing_other_log');
        } else {
            $block = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_common_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::TAB_ID_LISTING_OTHER)
            );
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings Log'))
             ->_addContent($block)
             ->renderLayout();
    }

    public function listingOtherGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_common_listing_other_log_grid', '', array(
                'channel' => $this->getRequest()->getParam('channel')
            ))->toHtml();
        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function synchronizationAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_common/logs/synchronization')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Synchronization Log'))
             ->_addContent($this->getLayout()->createBlock(
                 'M2ePro/adminhtml_common_log', '',
                 array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::TAB_ID_SYNCHRONIZATION)
             ))
             ->renderLayout();
    }

    public function synchronizationGridAction()
    {
        $response = $this->loadLayout()->getLayout()
             ->createBlock('M2ePro/adminhtml_common_synchronization_log_grid', '', array(
                 'channel' => $this->getRequest()->getParam('channel')
             ))->toHtml();
        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function orderAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_common/logs/order')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Orders Log'))
             ->_addContent($this->getLayout()->createBlock(
                 'M2ePro/adminhtml_common_log', '',
                 array('active_tab' => Ess_M2ePro_Block_Adminhtml_Common_Log_Tabs::TAB_ID_ORDER)
             ))
             ->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid', '', array(
            'channel' => $this->getRequest()->getParam('channel')
        ));
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################
}