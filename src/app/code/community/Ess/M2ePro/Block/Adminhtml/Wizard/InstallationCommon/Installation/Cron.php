<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_Cron
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationCron');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/installation/cron.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                'onclick' => 'WizardHandlerObj.skipStep(\'cron\');',
                'class'   => 'skip_cron_button'
            ) );
        $this->setChild('skip_cron_button',$buttonBlock);

        $this->basePath = Mage::helper('M2ePro/Client')->getBaseDirectory();
        $this->baseUrl = Mage::helper('M2ePro/Magento')->getBaseUrl();
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}