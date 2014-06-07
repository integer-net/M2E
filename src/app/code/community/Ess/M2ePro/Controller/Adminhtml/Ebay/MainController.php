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

    //#############################################
}