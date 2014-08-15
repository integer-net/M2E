<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Play_Installation_Marketplace extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationMarketplace');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/play/installation/marketplace.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl('*/adminhtml_common_marketplace/index',array(
            'wizard'=>true
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