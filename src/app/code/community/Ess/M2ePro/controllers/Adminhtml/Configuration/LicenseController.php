<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Configuration_LicenseController
    extends Ess_M2ePro_Controller_Adminhtml_Configuration_MainController
{
    //############################################

    public function confirmKeyAction()
    {
        if ($this->getRequest()->isPost()) {

            $post = $this->getRequest()->getPost();

            // Save settings
            //--------------------
            $key = strip_tags($post['key']);
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
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

    //--------------------------------------------

    public function checkLicenseAction()
    {
        $result = false;

        $enabledComponents = Mage::helper('M2ePro/Component')->getActiveComponents();

        if (count($enabledComponents) > 0) {
            $result = true;
            foreach ($enabledComponents as $enabledComponent) {
                if (Mage::helper('M2ePro/Module_License')->isNoneMode($enabledComponent)) {
                    $result = false;
                    break;
                }
            }
        }

        return $this->getResponse()->setBody(json_encode(array('ok' => $result)));
    }

    //#############################################
}