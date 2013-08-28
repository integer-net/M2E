<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Ebay_Installation extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'wizard_ebay_complete',
                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                'class' => 'end_ebay_button',
                'style' => 'display: none'
            ) );
        $this->setChild('end_ebay_button',$buttonBlock);
        //-------------------------------

        // Steps
        //-------------------------------
        $this->setChild(
            'step_marketplace',
            $this->helper('M2ePro/Wizard')->createBlock('installation_marketplace',$this->getNick())
        );
        $this->setChild(
            'step_synchronization',
            $this->helper('M2ePro/Wizard')->createBlock('installation_synchronization',$this->getNick())
        );
        $this->setChild(
            'step_otherListing',
            $this->helper('M2ePro/Wizard')->createBlock('installation_otherListing',$this->getNick())
        );
        $this->setChild(
            'step_account',
            $this->helper('M2ePro/Wizard')->createBlock('installation_account',$this->getNick())
        );
        //-------------------------------

        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Configuration Wizard (Magento eBay Integration)');
        //------------------------------

        return $temp;
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('step_marketplace')
            . $this->getChildHtml('step_synchronization')
            . $this->getChildHtml('step_otherListing')
            . $this->getChildHtml('step_account')
            . $this->getChildHtml('end_ebay_button');
    }

    // ########################################
}