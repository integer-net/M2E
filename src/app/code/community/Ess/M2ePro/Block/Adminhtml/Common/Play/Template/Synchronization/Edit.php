<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Play_Template_Synchronization_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('playTemplateSynchronizationEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_play_template_synchronization';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $componentName =  Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Play::TITLE);
            $headerTextEdit = Mage::helper('M2ePro')->__(
                "Edit %component_name% Synchronization Template",
                $componentName
            );
            $headerTextAdd = Mage::helper('M2ePro')->__(
                "Add %component_name% Synchronization Template",
                $componentName
            );
        } else {
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit Synchronization Template");
            $headerTextAdd = Mage::helper('M2ePro')->__("Add Synchronization Template");
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            $this->_headerText = $headerTextEdit;
            $this->_headerText .= ' "'.$this->escapeHtml(
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getTitle()).'"';
        } else {
            $this->_headerText = $headerTextAdd;
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
        //------------------------------

        //------------------------------
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'PlayTemplateSynchronizationHandlerObj.back_click(\''
                .Mage::helper('M2ePro')->getBackUrl('list').'\')',
            'class'     => 'back'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'PlayTemplateSynchronizationHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
            && Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            //------------------------------
            $this->_addButton('duplicate', array(
                'label'     => Mage::helper('M2ePro')->__('Duplicate'),
                'onclick'   => 'PlayTemplateSynchronizationHandlerObj.duplicate_click'
                    .'(\'common-play-template-synchronization\')',
                'class'     => 'add M2ePro_duplicate_button'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('delete', array(
                'label'     => Mage::helper('M2ePro')->__('Delete'),
                'onclick'   => 'PlayTemplateSynchronizationHandlerObj.delete_click()',
                'class'     => 'delete M2ePro_delete_button'
            ));
            //------------------------------
        }

        //------------------------------
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'PlayTemplateSynchronizationHandlerObj.save_click()',
            'class'     => 'save'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'PlayTemplateSynchronizationHandlerObj.save_and_edit_click'
                .'(\'\',\'playTemplateSynchronizationEditTabs\')',
            'class'     => 'save'
        ));
        //------------------------------
    }
}
