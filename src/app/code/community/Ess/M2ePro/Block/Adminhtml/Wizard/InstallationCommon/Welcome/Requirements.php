<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Welcome_Requirements
    extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardWelcomeRequirements');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/welcome/requirements.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->setChild('requirements', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_development_inspection_requirements'
        ));

        return parent::_beforeToHtml();
    }

    // ########################################
}