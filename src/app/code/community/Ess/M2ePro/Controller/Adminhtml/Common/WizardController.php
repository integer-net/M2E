<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Common_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_WizardController
{
    //#############################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Common::NICK;
    }

    //#############################################

    public function indexAction()
    {
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isSkipped($this->getNick())) {
            return $this->_redirect('*/adminhtml_common_listing/index/');
        }

        parent::indexAction();
    }

    //#############################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK);
        $tempResult->_title(Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel());
        return $tempResult;
    }

    //#############################################
}