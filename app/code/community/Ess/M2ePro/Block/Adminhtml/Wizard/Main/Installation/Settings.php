<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_Main_Installation_Settings extends Mage_Adminhtml_Block_Template
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('wizardMainInstallationSettings');
        //------------------------------

        $this->setTemplate('M2ePro/wizard/main/installation/settings.phtml');
    }

    // ########################################

    protected function _beforeToHtml()
    {
        //-------------------------------
        $params = array(
            '\''.$this->getUrl('*/adminhtml_settings/index',array('hide_upgrade_notification'=>'yes')).'\'',
            '\'settings\'',
            'function() {
                $(\'wizard_main_complete\').show();
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