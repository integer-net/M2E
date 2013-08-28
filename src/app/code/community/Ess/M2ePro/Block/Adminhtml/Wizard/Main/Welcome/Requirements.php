<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Welcome_Requirements extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardMainWelcomeRequirements');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/main/welcome/requirements.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->setChild('requirements', $this->getLayout()->createBlock('M2ePro/adminhtml_about_requirements'));

        return parent::_beforeToHtml();
    }

    // ########################################
}