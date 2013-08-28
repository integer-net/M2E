<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Amazon_LogController extends Ess_M2ePro_Controller_Adminhtml_MainController
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
        $this->_redirect('*/adminhtml_log/index');
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
                         ->createBlock('M2ePro/adminhtml_amazon_listing_other_log_grid')->toHtml();
        $this->getResponse()->setBody($response);
    }

    //#############################################
}