<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_EbayOtherListing_Installation_Synchronization
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardEbayOtherListingInstallationSynchronization');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/ebayOtherListing/installation/synchronization.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl(
            '*/adminhtml_ebay_listingOtherSynchronization/edit',
            array('hide_upgrade_notification'=>'yes')
        );

        $callback = '\'\'';
        if (Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->getSize() < 1) {
            $callback = 'function() {
                WizardHandlerObj.skipStep(\'account\');
            }';
        }

        $params = array(
            '\''.$url.'\'',
            '\'synchronization\'',
            $callback
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep('.implode(',',$params).');',
                'class' => 'process_synchronization_button'
            ) );
        $this->setChild('process_synchronization_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}