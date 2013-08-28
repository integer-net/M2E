<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Cmd_BackButton extends Mage_Adminhtml_Block_Widget
{
   // ########################################

   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('cmdBackButton');
        //------------------------------

        $this->setTemplate('M2ePro/cmd/back_button.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->backUrl = $this->getUrl('*/*/index');
        $this->aboutPageUrl = $this->getUrl('*/adminhtml_about/index',array('show_cmd'=>1));
    }

    // ########################################
}