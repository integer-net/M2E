<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Welcome extends Ess_M2ePro_Block_Adminhtml_Wizard_Welcome
{
    // ########################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $descriptionBlock = $this->helper('M2ePro/Wizard')->createBlock('welcome_description',$this->getNick());
        $requirementsBlock = $this->helper('M2ePro/Wizard')->createBlock('welcome_requirements',$this->getNick());
        //------------------------------

        //------------------------------
        $step = $this->helper('M2ePro/Wizard')->getWizard($this->getNick())->getFirstStep();
        $status = Ess_M2ePro_Helper_Wizard::STATUS_ACTIVE;
        $callback = 'function() { setLocation(\''.$this->getUrl('*/adminhtml_wizard_'.$this->getNick()).'\'); }';
        $callback = 'function() { WizardHandlerObj.setStep(\''.$step.'\', '.$callback.'); }';

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Start Configuration'),
                'onclick' => 'WizardHandlerObj.setStatus(\''.$status.'\', '.$callback.')',
                'class' => 'start_installation_button'
            ) );
        //------------------------------

        //------------------------------
        $this->setChild('description', $descriptionBlock);
        $this->setChild('requirements', $requirementsBlock);
        $this->setChild('start_wizard_button',$buttonBlock);
        //------------------------------

        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Welcome to M2E Pro - Magento Multi-Channels Integration!');
        //------------------------------

        return $temp;
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('description')
            . $this->getChildHtml('requirements')
            . $this->getChildHtml('start_wizard_button');
    }

    // ########################################
}