<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_License
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationLicense');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/installation/license.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $url = $this->getUrl('*/*/license',array('wizard'=>true));
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