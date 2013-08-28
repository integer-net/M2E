<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Add_StepOne extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAddStepOne');
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

        $this->_headerText = Mage::helper('M2ePro')->__("Add%s Listing [Settings]", $componentName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl('*/adminhtml_listing/index',array(
            'tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_EBAY
        ));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'EbayListingEditHandlerObj.back_click(\'' .$url.'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'EbayListingEditHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $url = $this->getUrl('*/adminhtml_ebay_listing/add',array('step'=>'1'));
        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'EbayListingEditHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'next'
        ));
        //------------------------------
    }
}