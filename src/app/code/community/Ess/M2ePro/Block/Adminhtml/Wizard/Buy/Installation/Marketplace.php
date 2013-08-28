<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Installation_Marketplace extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardBuyInstallationMarketplace');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/buy/installation/marketplace.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl('*/adminhtml_marketplace/index',array(
            'hide_upgrade_notification'=>'yes'
        ));
        $step = 'marketplace';
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlerObj.processStep(\''.$url.'\',\''.$step.'\');',
                                'class' => 'process_marketplace_button'
                            ) );
        $this->setChild('process_marketplace_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}