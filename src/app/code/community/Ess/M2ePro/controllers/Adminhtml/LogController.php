<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LogController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/logs')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Activity Logs'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/logs');
    }

    //#############################################

    public function indexAction()
    {
        $this->_redirect('*/*/listing');
    }

    //#############################################

    public function listingAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro/logs/listing')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Listings Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing_log'))
             ->renderLayout();
    }

    public function listingGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_listing_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function listingOtherAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro/logs/listing_other')) {
            return $this->_forward('denied');
        }

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('3rd Party Listing does not exist.'));
            return $this->_redirect('*/*/index');
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('3rd Party Listings Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_listing_other_log'))
             ->renderLayout();
    }

    public function listingOtherGridAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('M2ePro/Listing_Other')->load($id);

        if (!$model->getId() && $id) {
            exit();
        }

        Mage::helper('M2ePro')->setGlobalValue('temp_data', $model->getData());

        $response = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_listing_other_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function synchronizationAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro/logs/synchronization')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Synchronization Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_synchronization_log'))
             ->renderLayout();
    }

    public function synchronizationGridAction()
    {
        $response = $this->loadLayout()->getLayout()
                         ->createBlock('M2ePro/adminhtml_synchronization_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //---------------------------------------------

    public function orderAction()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('m2epro/logs/order')) {
            $this->_forward('denied');
            return;
        }

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Orders Log'))
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_order_log'))
             ->renderLayout();
    }

    public function orderGridAction()
    {
        $grid = $this->loadLayout()->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid');
        $this->getResponse()->setBody($grid->toHtml());
    }

    //#############################################
}