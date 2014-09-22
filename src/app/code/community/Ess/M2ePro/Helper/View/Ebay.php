<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Ebay extends Mage_Core_Helper_Abstract
{
    // M2ePro_TRANSLATIONS
    // Sell On eBay

    const NICK  = 'ebay';
    const TITLE = 'Sell On eBay';

    const WIZARD_INSTALLATION_NICK = 'installationEbay';
    const MENU_ROOT_NODE_NICK = 'm2epro_ebay';

    const MODE_SIMPLE = 'simple';
    const MODE_ADVANCED = 'advanced';

    // ########################################

    public function getMenuRootNodeLabel()
    {
        return Mage::helper('M2ePro')->__(self::TITLE);
    }

    // ########################################

    public function getWizardInstallationNick()
    {
        return self::WIZARD_INSTALLATION_NICK;
    }

    public function isInstallationWizardFinished()
    {
        return Mage::helper('M2ePro/Module_Wizard')->isFinished(
            $this->getWizardInstallationNick()
        );
    }

    // ########################################

    public function getMode()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/ebay/', 'mode');
    }

    public function setMode($mode)
    {
        $mode = strtolower($mode);
        if (!in_array($mode,array(self::MODE_SIMPLE,self::MODE_ADVANCED))) {
            return;
        }
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/view/ebay/', 'mode', $mode);
    }

    //-----------------------------------------

    public function isSimpleMode()
    {
        return $this->getMode() == self::MODE_SIMPLE;
    }

    public function isAdvancedMode()
    {
        return $this->getMode() == self::MODE_ADVANCED;
    }

    // ########################################

    public function getDocumentationUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/view/ebay/support/', 'documentation_url');
    }

    public function getVideoTutorialsUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()
                    ->getGroupValue('/view/ebay/support/', 'video_tutorials_url');
    }

    // ########################################

    public function prepareMenu(array $menuArray)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed(self::MENU_ROOT_NODE_NICK)) {
            return $menuArray;
        }

        if (count(Mage::helper('M2ePro/View_Ebay_Component')->getActiveComponents()) <= 0) {
            unset($menuArray[self::MENU_ROOT_NODE_NICK]);
            return $menuArray;
        }

        $tempTitle = $this->getMenuRootNodeLabel();
        !empty($tempTitle) && $menuArray[self::MENU_ROOT_NODE_NICK]['label'] = $tempTitle;

        // Add wizard menu item
        //---------------------------------
        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        $activeBlocker = $wizardHelper->getActiveBlockerWizard(Ess_M2ePro_Helper_View_Ebay::NICK);

        if (!$activeBlocker) {
            return $menuArray;
        }

        unset($menuArray[self::MENU_ROOT_NODE_NICK]['children']);
        unset($menuArray[self::MENU_ROOT_NODE_NICK]['click']);

        $menuArray[self::MENU_ROOT_NODE_NICK]['url'] = Mage::helper('adminhtml')->getUrl(
            'M2ePro/adminhtml_wizard_'.$wizardHelper->getNick($activeBlocker).'/index'
        );
        $menuArray[self::MENU_ROOT_NODE_NICK]['last'] = true;

        return $menuArray;
    }

    // ########################################
}