<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_EbayOtherListing_Installation_Account extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardEbayOtherListingInstallationAccount');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/ebayOtherListing/installation/account.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $first = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
                                                      ->setOrder('id','ASC')
                                                      ->getFirstItem();

        if ($id = $first->getId()) {
            Mage::helper('M2ePro')->setSessionValue('current_account_id',$id);
        }

        $url = $this->getUrl(
            '*/adminhtml_ebay_account/edit',
            array(
                'id' => Mage::helper('M2ePro')->getSessionValue('current_account_id'),
                'hide_upgrade_notification'=>'yes',
                'tab' => 'listingOther'
            )
        );
        $step = 'account';
        //-------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep(\''.$url.'\',\''.$step.'\');',
                'class'   => 'process_account_button'
            ) );
        $this->setChild('process_account_button',$buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Skip'),
                'onclick' => 'WizardHandlerObj.skipStep(\''.$step.'\');',
                'class'   => 'skip_account_button'
            ) );
        $this->setChild('skip_account_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}