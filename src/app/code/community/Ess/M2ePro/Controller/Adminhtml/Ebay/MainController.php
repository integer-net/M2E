<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Ebay::NICK;
    }

    //#############################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK);
        $tempResult->_title(Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel());
        return $tempResult;
    }

    //---------------------------------------------

    protected function beforeAddContentEvent()
    {
        parent::beforeAddContentEvent();
        $this->showCronPopupConfirm();
    }

    //#############################################

    protected function showCronPopupConfirm()
    {
        $mode = Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/view/ebay/cron/popup/','confirm');

        if ((int)$mode > 0) {
            return;
        }

        if (!Mage::helper('M2ePro/Module_Wizard')->isFinished(
                $this->getCustomViewHelper()->getWizardInstallationNick()
            )) {
            return;
        }

        if (Mage::helper('M2ePro/Magento')->isCronWorking()) {
            Mage::helper('M2ePro/Module')->getConfig()
                        ->setGroupValue('/view/ebay/cron/popup/','confirm','1');
            return;
        }

        $this->_initPopUp();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_cronPopup');
        $this->getLayout()->getBlock('content')->append($block);
    }

    //#############################################
}