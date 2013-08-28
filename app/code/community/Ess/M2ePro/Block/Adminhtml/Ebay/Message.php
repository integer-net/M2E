<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Message extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayMessage');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_message';
        $this->_mode = '';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('My Messages');
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
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'CommonHandlerObj.reset_click()',
            'class'   => 'reset'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_message_help');

        $filtersBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => 'adminhtml_ebay_message'
        ));
        $filtersBlock->setUseConfirm(false);

        $formBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_message_form');

        return $helpBlock->toHtml() . $filtersBlock->toHtml() . $formBlock->toHtml() . parent::getGridHtml();
    }
}