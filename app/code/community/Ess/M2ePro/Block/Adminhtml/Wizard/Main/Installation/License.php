<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Installation_License extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardMainInstallationLicense');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/main/installation/license.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl('*/adminhtml_license/index',array('hide_upgrade_notification'=>'yes'));
        $step = 'license';
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlerObj.processStep(\''.$url.'\',\''.$step.'\');',
                                'class' => 'process_license_button'
                            ) );
        $this->setChild('process_license_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}