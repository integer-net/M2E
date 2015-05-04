<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $this->appendWizardCompleteButton();
        //-------------------------------

        // Steps
        //-------------------------------
        $this->setChild(
            'step_marketplace',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_marketplace',$this->getNick())
        );
        $this->setChild(
            'step_synchronization',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_synchronization',$this->getNick())
        );
        $this->setChild(
            'step_account',
            $this->helper('M2ePro/Module_Wizard')->createBlock('installation_account',$this->getNick())
        );
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################

    protected function getHeaderTextHtml()
    {
        return 'Configuration Wizard (Magento Rakuten.com Integration)';
    }

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('step_marketplace')
            . $this->getChildHtml('step_synchronization')
            . $this->getChildHtml('step_account')
            . $this->getChildHtml('end_button');
    }

    // ########################################
}