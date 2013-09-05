<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_LogController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Logs'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/logs');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    // -------------------------------

    public function listingAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $this->_initAction();

        if (!empty($id)) {
            $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log');
        } else {
            $logBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING)
            );
        }

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // -------------------------------

    public function listingOtherAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('3rd Party Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $this->_initAction();

        if (!empty($id)) {
            $logBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_other_log');
        } else {
            $logBlock = $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_log', '',
                array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_LISTING_OTHER)
            );
        }

        $this->_addContent($logBlock)->renderLayout();
    }

    public function listingOtherGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_listing_other_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // -------------------------------

    public function synchronizationAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_addContent(
                 $this->getLayout()->createBlock(
                     'M2ePro/adminhtml_ebay_log', '',
                     array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_SYNCHRONIZATION)
                 )
             )->renderLayout();
    }

    public function synchronizationGridAction()
    {
        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_ebay_synchronization_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    // -------------------------------

    public function orderAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro_ebay/configuration')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_addContent(
                 $this->getLayout()->createBlock(
                     'M2ePro/adminhtml_ebay_log', '',
                     array('active_tab' => Ess_M2ePro_Block_Adminhtml_Ebay_Log_Tabs::TAB_ID_ORDER)
                 )
             )->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################
}