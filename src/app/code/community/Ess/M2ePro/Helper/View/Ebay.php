<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_View_Ebay extends Mage_Core_Helper_Abstract
{
    // Parser hack -> Mage::helper('M2ePro')->__('Sell On eBay');

    const NICK  = 'ebay';
    const TITLE = 'Sell On eBay';

    const WIZARD_INSTALLATION_NICK = 'installationEbay';
    const MENU_ROOT_NODE_NICK = 'm2epro_ebay';

    const MODE_SIMPLE = 'simple';
    const MODE_ADVANCED = 'advanced';

    // ########################################

    public function getMenuRootNodeLabel()
    {
        return $this->__(self::TITLE);
    }

    public function getWizardInstallationNick()
    {
        return self::WIZARD_INSTALLATION_NICK;
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
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/ebay/support/', 'documentation_url');
    }

    public function getVideoTutorialsUrl()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/ebay/support/', 'video_tutorials_url');
    }

    // ########################################
}