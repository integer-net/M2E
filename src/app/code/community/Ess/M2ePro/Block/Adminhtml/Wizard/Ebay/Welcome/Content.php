<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Ebay_Welcome_Content extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardEbayWelcomeContent');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/ebay/welcome/content.phtml');
    }

    // ########################################
}