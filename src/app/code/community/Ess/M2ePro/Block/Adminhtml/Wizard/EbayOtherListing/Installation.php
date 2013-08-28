<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_EbayOtherListing_Installation
    extends Ess_M2ePro_Block_Adminhtml_Wizard_Installation
{
    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'id' => 'wizard_ebayOtherListing_complete',
                'label'   => Mage::helper('M2ePro')->__('Complete Configuration'),
                'onclick' => 'setLocation(\''.$this->getUrl('*/*/complete').'\');',
                'class' => 'end_ebayOtherListing_button',
                'style' => 'display: none'
            ) );
        $this->setChild('end_ebayOtherListing_button',$buttonBlock);
        //-------------------------------

        // Steps
        //-------------------------------
        $this->setChild(
            'step_synchronization',
            $this->helper('M2ePro/Wizard')->createBlock('installation_synchronization',$this->getNick())
        );
        $this->setChild(
            'step_account',
            $this->helper('M2ePro/Wizard')->createBlock('installation_account',$this->getNick())
        );
        $this->setChild(
            'step_reset',
            $this->helper('M2ePro/Wizard')->createBlock('installation_reset',$this->getNick())
        );
        //-------------------------------

        $temp = parent::_beforeToHtml();

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Configuration Wizard (New 3rd Party Listings functionality)');
        //------------------------------

        return $temp;
    }

    // ########################################

    protected function _toHtml()
    {
        return parent::_toHtml()
            . $this->getChildHtml('step_synchronization')
            . $this->getChildHtml('step_account')
            . $this->getChildHtml('step_reset')
            . $this->getChildHtml('end_ebayOtherListing_button');
    }

    // ########################################
}