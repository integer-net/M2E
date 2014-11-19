<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Congratulation extends Ess_M2ePro_Block_Adminhtml_Wizard_Abstract
{
    // ########################################

    protected function _beforeToHtml()
    {
        // Initialization block
        //------------------------------
        $this->setId('wizard'.$this->getNick().'Congratulation');
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Congratulations!');
        //------------------------------

        // Buttons
        //------------------------------
        $this->prepareButtons();
        //------------------------------

        $this->setTemplate('widget/form/container.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}