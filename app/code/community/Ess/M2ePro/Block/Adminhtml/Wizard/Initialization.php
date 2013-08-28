<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Initialization extends Mage_Adminhtml_Block_Template
{
    // ########################################

    protected function _beforeToHtml()
    {
        // Set data for form
        //----------------------------
        $this->addData(array(
            'step' => $this->helper('M2ePro/Wizard')->getStep($this->getNick()),
            'steps' => json_encode($this->helper('M2ePro/Wizard')->getWizard($this->getNick())->getSteps()),
            'status' => $this->helper('M2ePro/Wizard')->getStatus($this->getNick()),
            'edition' => $this->helper('M2ePro/Wizard')->getEdition()
        ));
        //----------------------------

        // Initialization block
        //------------------------------
        $this->setId('wizardInitialization');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/initialization.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}