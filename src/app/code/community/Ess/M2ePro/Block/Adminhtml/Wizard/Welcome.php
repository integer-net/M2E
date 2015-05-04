<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Welcome extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        //------------------------------
        $this->setId('wizard'.$this->getNick().'Welcome');

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__($this->getHeaderTextHtml());

        // Buttons
        //------------------------------
        $this->removeButtons();
        $this->appendButtons();

        $this->setTemplate('widget/form/container.phtml');

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
        $wizardHelper = $this->helper('M2ePro/Module_Wizard');

        return parent::_toHtml() .
        $wizardHelper->createBlock('welcome_content', $this->getNick())->toHtml();
    }

    // ########################################

    protected function getHeaderTextHtml()
    {
        return 'Welcome';
    }

    protected function appendButtons()
    {
        $this->appendWizardSkipButton();
        parent::appendButtons();
    }

    // ########################################
}