<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Congratulation_Content extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardMainCongratulationContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/main/congratulation/content.phtml');
    }

    // ########################################
}