<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Info_License_Component extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/development/info/license/component.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $component = $this->getComponent();

        if (Mage::helper('M2ePro/Module_License')->isLiveMode($component)) {
            $licenseModeText = 'Live';
        } elseif (Mage::helper('M2ePro/Module_License')->isTrialMode($component)) {
            $licenseModeText = 'Trial';
        } else {
            $licenseModeText = 'None';
        }

        $this->licenseMode = $licenseModeText . Mage::helper('M2ePro')->__(' License');
        $this->licenseExpirationDate = Mage::helper('M2ePro/Module_License')->getTextExpirationDate($component);

        return parent::_beforeToHtml();
    }

    // ########################################
}