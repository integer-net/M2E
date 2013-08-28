<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Buy_Installation_Synchronization extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardBuyInstallationSynchronization');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/buy/installation/synchronization.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $params = array(
            '\''.$this->getUrl('*/adminhtml_synchronization/index',array('hide_upgrade_notification'=>'yes')).'\'',
            '\'synchronization\''
        );
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep('.implode(',',$params).');',
                'class' => 'process_synchronization_button'
            ) );
        $this->setChild('process_synchronization_button',$buttonBlock);

        $params = array(
            '\'synchronization\''
        );

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Skip'),
                'onclick' => 'WizardHandlerObj.skipStep('.implode(',',$params).');',
                'class' => 'skip_synchronization_button'
            ) );
        $this->setChild('skip_synchronization_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}