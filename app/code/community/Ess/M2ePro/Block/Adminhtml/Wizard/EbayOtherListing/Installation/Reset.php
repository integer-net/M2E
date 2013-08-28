<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_EbayOtherListing_Installation_Reset extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardEbayOtherListingInstallationReset');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/ebayOtherListing/installation/reset.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'    => 'reset_yes_button',
                                'label' => Mage::helper('M2ePro')->__('Re-import')
                            ) );
        $this->setChild('yes',$buttonBlock);
        //-------------------------------

        $step = 'reset';
        $callback = 'function() {
            $(\'wizard_ebayOtherListing_complete\').show();
        }';
        //-------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('No, thanks'),
                                'onclick' => 'WizardHandlerObj.skipStep(\''.$step.'\','.$callback.');',
                            ) );
        $this->setChild('no',$buttonBlock);
        //-------------------------------

        //-------------------------------
        $this->resetUrl = $this->getUrl('*/*/reset');
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}