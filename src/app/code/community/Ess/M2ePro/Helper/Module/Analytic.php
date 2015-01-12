<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Module_Analytic extends Mage_Core_Helper_Abstract
{
    const VIEW_BOTH = 'both';
    const VIEW_EBAY = 'ebay';
    const VIEW_COMMON = 'common';

    const NAVIGATION_MODE_DISABLED = 'disabled';
    const NAVIGATION_MODE_STANDARD = 'standard';
    const NAVIGATION_MODE_ADVANCED = 'advanced';

    const ACTION_MODE_DISABLED = 'disabled';
    const ACTION_MODE_ALL = 'all';
    const ACTION_MODE_SPECIAL = 'special';

    // ########################################

    public function getView()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'view');
    }

    //-----------------------------------------

    public function isViewBoth()
    {
        return $this->getView() == self::VIEW_BOTH;
    }

    public function isViewEbay()
    {
        return $this->getView() == self::VIEW_EBAY;
    }

    public function isViewCommon()
    {
        return $this->getView() == self::VIEW_COMMON;
    }

    // ########################################

    public function getNavigationMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'navigation_mode');
    }

    //-----------------------------------------

    public function isNavigationModeDisabled()
    {
        return $this->getNavigationMode() == self::NAVIGATION_MODE_DISABLED;
    }

    public function isNavigationModeStandard()
    {
        return $this->getNavigationMode() == self::NAVIGATION_MODE_STANDARD;
    }

    public function isNavigationModeAdvanced()
    {
        return $this->getNavigationMode() == self::NAVIGATION_MODE_ADVANCED;
    }

    // ########################################

    public function getActionMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/analytic/', 'action_mode');
    }

    //-----------------------------------------

    public function isActionModeDisabled()
    {
        return $this->getActionMode() == self::ACTION_MODE_DISABLED;
    }

    public function isActionModeAll()
    {
        return $this->getActionMode() == self::ACTION_MODE_ALL;
    }

    public function isActionModeSpecial()
    {
        return $this->getActionMode() == self::ACTION_MODE_SPECIAL;
    }

    // ########################################
}