<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Development_MainController
    extends Ess_M2ePro_Controller_Adminhtml_BaseController
{
    //#############################################

    public function indexAction()
    {
        $this->_redirect(Mage::helper('M2ePro/View_Development')->getPageRoute());
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        if ($this->getRequest()->isGet() &&
            !$this->getRequest()->isPost() &&
            !$this->getRequest()->isXmlHttpRequest()) {

            $this->addDevelopmentNotification();
            $this->addMaintenanceNotification();
        }

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_title(Ess_M2ePro_Helper_View_Development::TITLE);
        return $tempResult;
    }

    //#############################################

    private function addDevelopmentNotification()
    {
        if (!Mage::helper('M2ePro/Magento')->isDeveloper()) {
            return false;
        }

        $this->_getSession()->addWarning('Magento development mode is Active now.');

        return true;
    }

    private function addMaintenanceNotification()
    {
        if (!Mage::helper('M2ePro/Module_Maintenance')->isEnabled()) {
            return false;
        }

        $this->_getSession()->addWarning('Maintenance is Active now.');

        return true;
    }

    //#############################################
}