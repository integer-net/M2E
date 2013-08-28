<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'wizard_main_complete',
                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                'class' => 'end_main_button',
                'style' => 'display: none'
            ) );
        $this->setChild('end_main_button',$buttonBlock);
        //-------------------------------

        // Steps
        //-------------------------------
        $this->setChild(
            'step_cron',
            $this->helper('M2ePro/Wizard')->createBlock('installation_cron',$this->getNick())
        );
        $this->setChild(
            'step_license',
            $this->helper('M2ePro/Wizard')->createBlock('installation_license',$this->getNick())
        );
        $this->setChild(
            'step_settings',
            $this->helper('M2ePro/Wizard')->createBlock('installation_settings',$this->getNick())
        );
        //-------------------------------

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
            . $this->getChildHtml('step_cron')
            . $this->getChildHtml('step_license')
            . $this->getChildHtml('step_settings')
            . $this->getChildHtml('end_main_button');
    }

    // ########################################
}