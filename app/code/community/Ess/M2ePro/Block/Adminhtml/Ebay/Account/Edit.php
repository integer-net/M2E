<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayAccountEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_account';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        } else {
            $componentName = '';
        }

        if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
            Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()) {
            $this->_headerText = Mage::helper('M2ePro')->__("Edit%s Account", $componentName);
            $this->_headerText .= ' "'.$this->escapeHtml(
                Mage::helper('M2ePro')->getGlobalValue('temp_data')->getTitle()).'"';
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

        if ($wizardHelper->isActive('ebay') &&
            $wizardHelper->getStep('ebay') == 'account') {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'EbayAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'EbayAccountHandlerObj.save_and_edit_click(\'\',\'ebayAccountEditTabs\')',
                'class'     => 'save'
            ));

            if ($this->getRequest()->getParam('id')) {

                $this->_addButton('add_new_account', array(
                    'label'     => Mage::helper('M2ePro')->__('Add New Account'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_account/new').'\')',
                    'class'     => 'add_new_account'
                ));

                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'EbayAccountHandlerObj.completeStep();',
                    'class'     => 'close'
                ));
            }

        } elseif ($wizardHelper->isActive('ebayOtherListing') &&
                  $wizardHelper->getStep('ebayOtherListing') == 'account') {

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'EbayAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            $currentId = Mage::helper('M2ePro')->getSessionValue('current_account_id');
            $nextId = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')
                ->addFieldToFilter('id',array('gt' => $currentId))
                ->setOrder('id','ASC')
                ->getFirstItem()
                ->getId();

            if ($nextId) {

                $this->_addButton('save_and_proceed', array(
                    'label'     => Mage::helper('M2ePro')->__('Save And Proceed Next'),
                    'onclick'   => 'EbayAccountHandlerObj.save_click()',
                    'class'     => 'save'
                ));

            } else {

                $this->_addButton('close', array(
                    'label'     => Mage::helper('M2ePro')->__('Complete This Step'),
                    'onclick'   => 'EbayAccountHandlerObj.completeStep();',
                    'class'     => 'close'
                ));
            }

        } else {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'EbayAccountHandlerObj.back_click(\'' .Mage::helper('M2ePro')->getBackUrl('list').'\')',
                'class'     => 'back'
            ));

            $this->_addButton('reset', array(
                'label'     => Mage::helper('M2ePro')->__('Refresh'),
                'onclick'   => 'EbayAccountHandlerObj.reset_click()',
                'class'     => 'reset'
            ));

            if (Mage::helper('M2ePro')->getGlobalValue('temp_data') &&
                Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()) {

                $this->_addButton('delete', array(
                    'label'     => Mage::helper('M2ePro')->__('Delete'),
                    'onclick'   => 'EbayAccountHandlerObj.delete_click()',
                    'class'     => 'delete M2ePro_delete_button'
                 ));

                $this->_addButton('save', array(
                    'label'     => Mage::helper('M2ePro')->__('Save'),
                    'onclick'   => 'EbayAccountHandlerObj.save_click()',
                    'class'     => 'save'
                ));
            }

            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
                'onclick'   => 'EbayAccountHandlerObj.save_and_edit_click(\'\',\'ebayAccountEditTabs\')',
                'class'     => 'save'
            ));
        }
        //------------------------------
    }
}