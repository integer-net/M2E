<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_License_Information extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('developmentAboutLicenseInformation');
        //------------------------------

        $this->setTemplate('M2ePro/development/info/license/information.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->licenseKey = Mage::helper('M2ePro')->escapeHtml(Mage::helper('M2ePro/Module_License')->getKey());

        $this->licenseDomain = Mage::helper('M2ePro/Module_License')->getDomain();
        $this->licenseIp = Mage::helper('M2ePro/Module_License')->getIp();
        $this->licenseDirectory = Mage::helper('M2ePro/Module_License')->getDirectory();

        return parent::_beforeToHtml();
    }

    // ########################################
}