<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Configuration_Linker extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configurationLinker');
        //------------------------------

        $this->setTemplate('M2ePro/configuration/linker.phtml');
    }

    // ########################################
}