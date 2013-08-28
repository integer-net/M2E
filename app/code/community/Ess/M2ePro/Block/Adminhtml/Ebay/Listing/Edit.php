<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
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
            $url = Mage::helper('M2ePro')->getBackUrl('*/adminhtml_listing/index',array(
                'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
            ));
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'EbayListingEditHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'EbayListingEditHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $url = $this->getUrl('*/adminhtml_ebay_listing/save',array(
            'id' => $listingData['id'],
            'back' => Mage::helper('M2ePro')->getBackUrlParam('list')
        ));
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'EbayListingEditHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));

        $this->_addButton('save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'EbayListingEditHandlerObj.save_and_edit_click()',
            'class'     => 'save'
        ));
        //------------------------------
    }
}