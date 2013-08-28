<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_LicenseController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/configuration')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('License'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Configuration/LicenseHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/license');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_license'))
             ->renderLayout();
    }

    //#############################################

    public function confirmKeyAction()
    {
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            // Save settings
            //--------------------
            $key = strip_tags($post['key']);
            Mage::helper('M2ePro/Ess')->getConfig()->setGroupValue(
                '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',(string)$key
            );
            //--------------------

            Mage::getModel('M2ePro/Servicing_Dispatcher')->processTasks(array(
                Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
            ));

            $this->_getSession()->addSuccess(
                Mage::helper('M2ePro')->__('The license key has been successfully updated.')
            );
        }

        $this->_redirectUrl($this->_getRefererUrl());
    }

    public function refreshStatusAction()
    {
        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTasks(array(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        ));

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The license status has been successfully refreshed.')
        );

        $this->_redirectUrl($this->_getRefererUrl());
    }

    public function checkLicenseAction()
    {
        $result = false;

        $enabledComponents = Mage::helper('M2ePro/Component')->getActiveComponents();

        if (count($enabledComponents) > 0) {
            $result = true;
            foreach ($enabledComponents as $enabledComponent) {
                if (Mage::helper('M2ePro/License')->isNoneMode($enabledComponent)) {
                    $result = false;
                    break;
                }
            }
        }

        exit(json_encode(array('ok' => $result)));
    }

    //#############################################
}