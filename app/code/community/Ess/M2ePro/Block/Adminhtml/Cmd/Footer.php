<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Cmd_Footer extends Mage_Adminhtml_Block_Widget
{
   // ########################################

   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('cmdFooter');
        //------------------------------

        $this->setTemplate('M2ePro/cmd/footer.phtml');
    }

    // ########################################
}