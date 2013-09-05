<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View extends Mage_Core_Helper_Abstract
{
    const LAYOUT_NICK = 'm2epro';
    const GENERAL_BLOCK_PATH = 'M2ePro/adminhtml_general';

    const ANALYTIC_VIEW_BOTH = 'both';
    const ANALYTIC_VIEW_EBAY = 'ebay';
    const ANALYTIC_VIEW_COMMON = 'common';

    const ANALYTIC_NAVIGATION_MODE_DISABLED = 'disabled';
    const ANALYTIC_NAVIGATION_MODE_STANDARD = 'standard';
    const ANALYTIC_NAVIGATION_MODE_ADVANCED = 'advanced';

    const ANALYTIC_ACTION_MODE_DISABLED = 'disabled';
    const ANALYTIC_ACTION_MODE_ALL = 'all';
    const ANALYTIC_ACTION_MODE_SPECIAL = 'special';

    // ########################################

    public function isBaseControllerLoaded()
    {
        return (bool)Mage::helper('M2ePro/Data_Global')->getValue('is_base_controller_loaded');
    }

    // ########################################

    /**
     * @param string $viewNick
     * @return Ess_M2ePro_Helper_View_Common|Ess_M2ePro_Helper_View_Ebay
     */
    public function getHelper($viewNick = null)
    {
        if (is_null($viewNick)) {
            $viewNick = $this->getCurrentView();
        }

        switch ($viewNick) {
            case Ess_M2ePro_Helper_View_Ebay::NICK:
                $helper = Mage::helper('M2ePro/View_Ebay');
                break;

            case Ess_M2ePro_Helper_View_Common::NICK:
            default:
                $helper = Mage::helper('M2ePro/View_Common');
                break;
        }

        return $helper;
    }

    /**
     * @param string $viewNick
     * @return Ess_M2ePro_Helper_View_Common_Component|Ess_M2ePro_Helper_View_Ebay_Component
     */
    public function getComponentHelper($viewNick = null)
    {
        if (is_null($viewNick)) {
            $viewNick = $this->getCurrentView();
        }

        switch ($viewNick) {
            case Ess_M2ePro_Helper_View_Ebay::NICK:
                $helper = Mage::helper('M2ePro/View_Ebay_Component');
                break;

            case Ess_M2ePro_Helper_View_Common::NICK:
            default:
                $helper = Mage::helper('M2ePro/View_Common_Component');
                break;
        }

        return $helper;
    }

    /**
     * @param string $viewNick
     * @return Ess_M2ePro_Helper_View_Ebay_Controller|Ess_M2ePro_Helper_View_Common_Controller
     */
    public function getControllerHelper($viewNick = null)
    {
        if (is_null($viewNick)) {
            $viewNick = $this->getCurrentView();
        }

        switch ($viewNick) {
            case Ess_M2ePro_Helper_View_Ebay::NICK:
                $helper = Mage::helper('M2ePro/View_Ebay_Controller');
                break;

            case Ess_M2ePro_Helper_View_Common::NICK:
            default:
                $helper = Mage::helper('M2ePro/View_Common_Controller');
                break;
        }

        return $helper;
    }

    // ########################################

    public function getCurrentView()
    {
        $request = Mage::app()->getRequest();
        $controller = $request->getControllerName();

        if (is_null($controller)) {
            return NULL;
        }

        if (stripos($controller, 'adminhtml_ebay') !== false) {
            return Ess_M2ePro_Helper_View_Ebay::NICK;
        }

        if (stripos($controller, 'adminhtml_common') !== false) {
            return Ess_M2ePro_Helper_View_Common::NICK;
        }

        if (stripos($controller, 'system_config') !== false) {
            return Ess_M2ePro_Helper_View_Configuration::NICK;
        }

        return NULL;
    }

    //-----------------------------------------

    public function isCurrentViewEbay()
    {
        return $this->getCurrentView() == Ess_M2ePro_Helper_View_Ebay::NICK;
    }

    public function isCurrentViewCommon()
    {
        return $this->getCurrentView() == Ess_M2ePro_Helper_View_Common::NICK;
    }

    public function isCurrentViewConfiguration()
    {
        return $this->getCurrentView() == Ess_M2ePro_Helper_View_Configuration::NICK;
    }

    // ########################################

    public function getAnalyticView()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'view');
    }

    //-----------------------------------------

    public function isAnalyticViewBoth()
    {
        return $this->getAnalyticView() == self::ANALYTIC_VIEW_BOTH;
    }

    public function isAnalyticViewEbay()
    {
        return $this->getAnalyticView() == self::ANALYTIC_VIEW_EBAY;
    }

    public function isAnalyticViewCommon()
    {
        return $this->getAnalyticView() == self::ANALYTIC_VIEW_COMMON;
    }

    // ########################################

    public function getAnalyticNavigationMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'navigation_mode');
    }

    //-----------------------------------------

    public function isAnalyticNavigationModeDisabled()
    {
        return $this->getAnalyticNavigationMode() == self::ANALYTIC_NAVIGATION_MODE_DISABLED;
    }

    public function isAnalyticNavigationModeStandard()
    {
        return $this->getAnalyticNavigationMode() == self::ANALYTIC_NAVIGATION_MODE_STANDARD;
    }

    public function isAnalyticNavigationModeAdvanced()
    {
        return $this->getAnalyticNavigationMode() == self::ANALYTIC_NAVIGATION_MODE_ADVANCED;
    }

    // ########################################

    public function getAnalyticActionMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'action_mode');
    }

    //-----------------------------------------

    public function isAnalyticActionModeDisabled()
    {
        return $this->getAnalyticActionMode() == self::ANALYTIC_ACTION_MODE_DISABLED;
    }

    public function isAnalyticActionModeAll()
    {
        return $this->getAnalyticActionMode() == self::ANALYTIC_ACTION_MODE_ALL;
    }

    public function isAnalyticActionModeSpecial()
    {
        return $this->getAnalyticActionMode() == self::ANALYTIC_ACTION_MODE_SPECIAL;
    }

    // ########################################

    public function getUrl($row, $controller, $action, array $params = array())
    {
        $component = strtolower($row->getData('component_mode'));

        if ($component != Ess_M2ePro_Helper_Component_Ebay::NICK) {
            $component = Ess_M2ePro_Helper_View_Common::NICK . '_' . $component;
        }

        return Mage::helper('adminhtml')->getUrl("*/adminhtml_{$component}_{$controller}/{$action}", $params);
    }

    // ########################################
}