<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Welcome_Description extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardMainWelcomeDescription');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/main/welcome/description.phtml');
    }

    // ########################################
}