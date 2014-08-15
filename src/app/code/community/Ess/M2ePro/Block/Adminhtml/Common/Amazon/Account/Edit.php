<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_account';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (!Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit %component_name% Account", $componentName);
            $headerTextAdd = Mage::helper('M2ePro')->__("Add %component_name% Account", $componentName);
        } else {
            $headerTextEdit = Mage::helper('M2ePro')->__("Edit Account");
            $headerTextAdd = Mage::helper('M2ePro')->__("Add Account");
        }

        if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
            Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
        ) {
            $this->_headerText = $headerTextEdit;
            $this->_headerText .= ' "'.$this->escapeHtml(Mage::helper('M2ePro/Data_Global')->getValue('temp_data')
                                                                               ->getTitle()).'"';
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

        /* @var $wizardHelper Ess_M2ePro_Helper_Module_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if ($wizardHelper->isActive('amazon') &&
            $wizardHelper->getStep('amazon') == 'account') {

            //------------------------------
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AmazonAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AmazonAccountHandlerObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                'class'     => 'save'
            ));
            //------------------------------

            if ($this->getRequest()->getParam('id')) {
                //------------------------------
                $url = $this->getUrl('*/adminhtml_common_amazon_account/new', array('wizard' => true));
                $this->_addButton('add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''. $url .'\')',
                    'class'     => 'add_new_account'
                ));
                //------------------------------

                //------------------------------
                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'AmazonAccountHandlerObj.completeStep();',
                    'class'     => 'close'
                ));
                //------------------------------
            }
        } else {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl('list');
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'AmazonAccountHandlerObj.back_click(\''. $url .'\')',
                'class'     => 'back'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AmazonAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            if (Mage::helper('M2ePro/Data_Global')->getValue('temp_data') &&
                Mage::helper('M2ePro/Data_Global')->getValue('temp_data')->getId()
            ) {
                //------------------------------
                $this->_addButton('delete', array(
                    'label'     => Mage::helper('M2ePro')->__('Delete'),
                    'onclick'   => 'AmazonAccountHandlerObj.delete_click()',
                    'class'     => 'delete M2ePro_delete_button'
                ));
                //------------------------------
            }

            //------------------------------
            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'AmazonAccountHandlerObj.save_click()',
                'class'     => 'save'
            ));
            //------------------------------

            //------------------------------
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AmazonAccountHandlerObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                'class'     => 'save'
            ));
            //------------------------------
        }
    }
}