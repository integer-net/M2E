<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Config_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('configEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_config';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::helper('M2ePro')->getGlobalValue('config_mode') == 'ess') {
            $this->_headerText = Mage::helper('M2ePro')->__('Manage ESS Config Data');
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Manage M2ePro Config Data');
        }
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (Mage::helper('M2ePro')->getGlobalValue('config_mode') == 'm2epro') {
            $this->_addButton('goto_ess_config', array(
                'label'     => 'ESS Config',
                'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_config/ess').'\')',
                'class'     => 'button_link ess_config'
            ));
        }

        if (Mage::helper('M2ePro')->getGlobalValue('config_mode') == 'ess') {
            $this->_addButton('goto_m2epro_config', array(
                'label'     => 'M2ePro Config',
                'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_config/m2epro').'\')',
                'class'     => 'button_link m2epro_config'
            ));
        }

        $this->_addButton('goto_cmd', array(
            'label'     => 'CMD',
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_cmd/index').'\')',
            'class'     => 'button_link cmd'
        ));

        $this->_addButton('add_new_config', array(
            'label'     => 'Add Config',
            'onclick'   => 'ConfigHandlerObj.setForAdd();',
            'class'     => 'add_new_config'
        ));
        //------------------------------
    }
}