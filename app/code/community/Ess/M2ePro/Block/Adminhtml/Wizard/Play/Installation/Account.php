<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Play_Installation_Account extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardPlayInstallationAccount');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/play/installation/account.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl('*/adminhtml_play_account/new',array('hide_upgrade_notification'=>'yes'));
        $step = 'account';
        $callback = 'function() {
            $(\'wizard_play_complete\').show()
        }';
        //-------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep(\''.$url.'\',\''.$step.'\','.$callback.');',
                'class'   => 'process_account_button'
            ) );
        $this->setChild('process_account_button',$buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Skip'),
                'onclick' => 'WizardHandlerObj.skipStep(\''.$step.'\','.$callback.');',
                'class'   => 'skip_account_button'
            ) );
        $this->setChild('skip_account_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }
}