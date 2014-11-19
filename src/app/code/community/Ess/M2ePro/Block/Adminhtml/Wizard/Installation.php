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
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Configuration Wizard');
        //------------------------------

        //------------------------------
        $this->prepareButtons();

        $url = $this->getUrl('*/*/skip');
        $this->_addButton('skip', array(
            'label'     => Mage::helper('M2ePro')->__('Skip Wizard'),
            'onclick'   => 'WizardHandlerObj.skip(\''.$url.'\')',
            'class'     => 'skip'
        ));
        //------------------------------

        $this->setTemplate('widget/form/container.phtml');

        //------------------------------
        return parent::_beforeToHtml();
    }

    // ########################################
}