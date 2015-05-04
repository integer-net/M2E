<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        //------------------------------
        $this->setId('wizard'.$this->getNick().'Installation');

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__($this->getHeaderTextHtml());

        $this->removeButtons();
        $this->appendButtons();

        $this->setTemplate('widget/form/container.phtml');

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function appendButtons()
    {
        $this->appendWizardSkipButton();
        parent::appendButtons();
    }

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard!';
    }

    // ########################################
}