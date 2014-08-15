<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_ConfigurationController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Configuration'))
            ->_title(Mage::helper('M2ePro')->__('Global'));

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration/global');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_configuration_linker'))
            ->renderLayout();
    }

    //#############################################
}