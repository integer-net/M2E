<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Add_StepOne extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingAddStepOne');
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

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'BuyListingSettingsHandlerObj.back_click(\''
                .$this->getUrl('*/adminhtml_listing/index',
                    array('tab' => Ess_M2ePro_Block_Adminhtml_Component_Abstract::TAB_ID_BUY))
                .'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'BuyListingSettingsHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $this->_addButton('save_and_next', array(
            'label'     => Mage::helper('M2ePro')->__('Next'),
            'onclick'   => 'BuyListingSettingsHandlerObj.save_click(\''
                .$this->getUrl('*/adminhtml_buy_listing/add',
                    array('step'=>'1'))
                .'\')',
            'class'     => 'next'
        ));
        //------------------------------
    }
}
