<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationCommon_Installation_Settings_Container
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('settings');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml';
        $this->_mode = 'configuration_components';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Settings');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ComponentsHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('close', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Complete This Step'),
            'onclick'   => 'ComponentsHandlerObj.completeStep();',
            'class'     => 'close'
        ));
        //------------------------------
    }
}