<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Cmd_ControlPanel extends Mage_Adminhtml_Block_Widget
{
   // ########################################

   public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('cmdControlPanel');
        //------------------------------

        $this->setTemplate('M2ePro/cmd/control_panel.phtml');
    }

    protected function _beforeToHtml()
    {
        $this->groups = $this->getData('groups');

        $this->success_message = $this->getData('success_message');
        $this->error_message = $this->getData('error_message');
        $this->warning_message = $this->getData('warning_message');

        $this->aboutPageUrl = $this->getUrl('*/adminhtml_about/index',array('show_cmd'=>1));

        $this->enabledComponents = Mage::helper('M2ePro/Component')->getEnabledComponents();
    }

    // ########################################
}