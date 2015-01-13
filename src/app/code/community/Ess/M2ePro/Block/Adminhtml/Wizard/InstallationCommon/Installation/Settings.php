<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_Settings
    extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardInstallationSettings');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/installationCommon/installation/settings.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $params = array(
            '\''.$this->getUrl('*/*/settings',array('wizard'=>true)).'\'',
            '\'settings\'',
            'function() {
                $(\'wizard_complete\').show();
            }'
        );
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                                'onclick' => 'WizardHandlerObj.processStep('.implode(',',$params).');',
                                'class' => 'process_settings_button'
                            ) );
        $this->setChild('process_settings_button',$buttonBlock);
        //-------------------------------

        return parent::_beforeToHtml();
    }

    // ########################################
}