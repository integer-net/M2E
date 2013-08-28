<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_account';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Amazon::TITLE);
        } else {
            $componentName = '';
        }

        if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
            Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
        ) {
            $this->_headerText = Mage::helper('M2ePro')->__("Edit%s Account", $componentName);
            $this->_headerText .= ' "'.$this->escapeHtml(Mage::helper('M2ePro')->getGlobalValue('temp_data')
                                                                               ->getTitle()).'"';
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__("Add%s Account", $componentName);
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

        /* @var $wizardHelper Ess_M2ePro_Helper_Wizard */
        $wizardHelper = Mage::helper('M2ePro/Wizard');

        if ($wizardHelper->isActive('amazon') &&
            $wizardHelper->getStep('amazon') == 'account') {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AmazonAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AmazonAccountHandlerObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                'class'     => 'save'
            ));

            if ($this->getRequest()->getParam('id')) {

                $this->_addButton('add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_amazon_account/new',
                        array('hide_upgrade_notification' => 'yes')).'\')',
                    'class'     => 'add_new_account'
                ));

                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'AmazonAccountHandlerObj.completeStep();',
                    'class'     => 'close'
                ));
            }
        } else {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'AmazonAccountHandlerObj.back_click(\''.Mage::helper('M2ePro')->getBackUrl('list').'\')',
                'class'     => 'back'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'AmazonAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
                Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()
            ) {

                /** @var $accountObj Ess_M2ePro_Model_Account */
                $accountObj = Mage::helper('M2ePro')->getGlobalValue('temp_data');

                if (!$accountObj->isLockedObject(NULL)) {

                    $this->_addButton('delete', array(
                        'label'     => Mage::helper('M2ePro')->__('Delete'),
                        'onclick'   => 'AmazonAccountHandlerObj.delete_click()',
                        'class'     => 'delete M2ePro_delete_button'
                    ));
                }
            }

            $this->_addButton('save', array(
                'label'     => Mage::helper('M2ePro')->__('Save'),
                'onclick'   => 'AmazonAccountHandlerObj.save_click()',
                'class'     => 'save'
            ));

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'AmazonAccountHandlerObj.save_and_edit_click(\'\',\'amazonAccountEditTabs\')',
                'class'     => 'save'
            ));

        }

        //------------------------------
    }
}