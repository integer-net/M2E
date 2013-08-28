<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Ebay_Installation_OtherListing
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardEbayInstallationOtherListing');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/ebay/installation/otherListing.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_listingOtherSynchronization/edit',
            array('hide_upgrade_notification'=>'yes')
        );

        $params = array(
            '\''.$url.'\'',
            '\'otherListing\''
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep('.implode(',',$params).');',
                'class' => 'process_otherListing_button'
            ) );
        $this->setChild('process_otherListing_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}