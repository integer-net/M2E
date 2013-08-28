<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_buy_listing';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Buy::TITLE);
        } else {
            $componentName = '';
        }

        $listingData = Mage::helper('M2ePro')->getGlobalValue('temp_data');
        $this->_headerText = Mage::helper('M2ePro')->__('Edit "%s"%s Listing [Settings]',
                                                        $this->escapeHtml($listingData['title']),
                                                        $componentName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (!is_null($this->getRequest()->getParam('back'))) {

            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'BuyListingSettingsHandlerObj.back_click(\''.Mage::helper('M2ePro')
                    ->getBackUrl('*/adminhtml_listing/index',
                    array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY)).'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'BuyListingSettingsHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'BuyListingSettingsHandlerObj.save_click(\''
                .$this->getUrl('*/adminhtml_buy_listing/save',
                    array('id' => $listingData['id'],
                        'back' => Mage::helper('M2ePro')->getBackUrlParam('list'))).'\')',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'BuyListingSettingsHandlerObj.save_and_edit_click(\'\',\'buyListingEditTabs\')',
            'class'     => 'save'
        ));
        //------------------------------
    }
}
