<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Menu extends Mage_Adminhtml_Block_Page_Menu
{
    //#############################################

    public function getModuleName()
    {
        if (Mage::getStoreConfig('advanced/modules_disable_output/Ess_M2ePro')) {
            return 'Mage_Adminhtml';
        }
        return parent::getModuleName();
    }

    public function getMenuArray()
    {
        $menuArray = parent::getMenuArray();

        try {
            $menuArray = $this->prepareEbayViewMenu($menuArray);
            $menuArray = $this->prepareCommonViewMenu($menuArray);
        } catch (Exception $exception) {}

        return $menuArray;
    }

    //#############################################

    private function prepareEbayViewMenu($menuArray)
    {
        $menuRootNick = Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK;

        if (!Mage::getSingleton('admin/session')->isAllowed($menuRootNick)) {
            return $menuArray;
        }

        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) <= 0) {
            unset($menuArray[$menuRootNick]);
            return $menuArray;
        }

        $tempTitle = Mage::helper('M2ePro/View_Ebay')->getMenuRootNodeLabel();
        !empty($tempTitle) && $menuArray[$menuRootNick]['label'] = $tempTitle;

        // Add wizard menu item
        //---------------------------------
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeBlocker = $wizardHelper->getActiveBlockerWizard(Ess_M2ePro_Helper_View_Ebay::NICK);

        if (!$activeBlocker) {
            return $menuArray;
        }

        unset($menuArray[$menuRootNick]['children']);
        unset($menuArray[$menuRootNick]['click']);

        $menuArray[$menuRootNick]['url'] = $this->getUrl(
            'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
        );
        $menuArray[$menuRootNick]['last'] = true;

        return $menuArray;

    }

    private function prepareCommonViewMenu($menuArray)
    {
        $menuRootNick = Ess_M2ePro_Helper_View_Common::MENU_ROOT_NODE_NICK;

        if (!Mage::getSingleton('admin/session')->isAllowed($menuRootNick)) {
            return $menuArray;
        }

        if (count(Mage::helper('M2ePro/View_Common_Component')->getActiveComponents()) <= 0) {
            unset($menuArray[$menuRootNick]);
            return $menuArray;
        }

        $tempTitle = Mage::helper('M2ePro/View_Common')->getMenuRootNodeLabel();
        !empty($tempTitle) && $menuArray[$menuRootNick]['label'] = $tempTitle;

        // Add wizard menu item
        //---------------------------------
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeBlocker = $wizardHelper->getActiveBlockerWizard(Ess_M2ePro_Helper_View_Common::NICK);

        if ($activeBlocker) {

            unset($menuArray[$menuRootNick]['children']);
            unset($menuArray[$menuRootNick]['click']);

            $menuArray[$menuRootNick]['url'] = $this->getUrl(
                'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
            );
            $menuArray[$menuRootNick]['last'] = true;

            return $menuArray;
        }
        //---------------------------------

        // Set documentation redirect url
        //---------------------------------
        if (isset($menuArray[$menuRootNick]['children']['help']['children']['doc'])) {
            $menuArray[$menuRootNick]['children']['help']['children']['doc']['click'] =
                "window.open(this.href, '_blank'); return false;";
            $menuArray[$menuRootNick]['children']['help']['children']['doc']['url'] =
                Mage::helper('M2ePro/View_Common')->getDocumentationUrl();
        }
        //---------------------------------

        // Set video tutorials redirect url
        //---------------------------------
        if (isset($menuArray[$menuRootNick]['children']['help']['children']['tutorial'])) {
            $menuArray[$menuRootNick]['children']['help']['children']['tutorial']['click'] =
                "window.open(this.href, '_blank'); return false;";
            $menuArray[$menuRootNick]['children']['help']['children']['tutorial']['url'] =
                Mage::helper('M2ePro/View_Common')->getVideoTutorialsUrl();
        }
        //---------------------------------

        return $menuArray;
    }

    //#############################################
}