<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Development_Tabs_Command_Group extends Mage_Adminhtml_Block_Widget
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/development/tabs/command/group.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        $this->enabledComponents = Mage::helper('M2ePro/Component')->getEnabledComponents();

        $this->commands = Mage::helper('M2ePro/View_Development_Command')
                            ->parseGeneralCommandsData($this->getControllerName());

        return parent::_beforeToHtml();
    }

    // ########################################
}