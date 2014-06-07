<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    private $isEbayWizardFinished = false;

    // ########################################

    protected function _beforeToHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
        $wizardHelper = $this->helper('M2ePro/Module_Wizard');

        $this->isEbayWizardFinished = Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished();

        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'wizard_complete',
                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                'class' => 'end_button',
                'style' => 'display: none'
            ) );
        $this->setChild('end_button',$buttonBlock);
        //-------------------------------

        // Steps
        //-------------------------------
        if (!$this->isEbayWizardFinished) {
            $this->setChild(
                 'step_license',
                 $wizardHelper->createBlock('installation_license',$this->getNick())
            );
        }

        $this->setChild(
            'step_settings',
            $wizardHelper->createBlock('installation_settings',$this->getNick())
        );
        //-------------------------------

        if ($this->isEbayWizardFinished &&
            $wizardHelper->getStep($this->getNick()) == 'license') {
            $steps = $wizardHelper->getWizard($this->getNick())->getSteps();
            $wizardHelper->setStep($this->getNick(), $steps[array_search('license', $steps) + 1]);
        }

        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Configuration Wizard (Magento Multi-Channels Integration)');
        //------------------------------

        return $temp;
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . ($this->isEbayWizardFinished ? '' : $this->getChildHtml('step_license'))
            . $this->getChildHtml('step_settings')
            . $this->getChildHtml('end_button');
    }

    // ########################################
}