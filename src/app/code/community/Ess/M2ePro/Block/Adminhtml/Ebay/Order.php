<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayOrder');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_order';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Orders');
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
        $url = $this->getUrl('*/adminhtml_ebay_account/index');
        $this->_addButton('accounts', array(
            'label'     => Mage::helper('M2ePro')->__('Accounts'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_ebay_log/order');
        $this->_addButton('logs', array(
            'label'     => Mage::helper('M2ePro')->__('View Logs'),
            'onclick'   => 'window.open(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------
    }

    // ####################################

    public function getGridHtml()
    {
        //------------------------------
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_help');
        $editItemBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit');
        //------------------------------

        //------------------------------
        $marketplaceFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_marketplace_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => 'adminhtml_ebay_order'
        ));
        $marketplaceFilterBlock->setUseConfirm(false);
        //------------------------------

        //------------------------------
        $accountFilterBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_account_switcher', '', array(
            'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'controller_name' => 'adminhtml_ebay_order'
        ));
        $accountFilterBlock->setUseConfirm(false);
        //------------------------------

        //------------------------------
        $orderStateSwitcherBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_order_notCreatedFilter',
            '',
            array(
                'component_mode' => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'controller' => 'adminhtml_ebay_order'
            )
        );
        //------------------------------

        return $helpBlock->toHtml()
            . '<div class="filter_block">'
            . $marketplaceFilterBlock->toHtml()
            . $accountFilterBlock->toHtml()
            . $orderStateSwitcherBlock->toHtml()
            . '</div>'
            . $editItemBlock->toHtml()
            . parent::getGridHtml();
    }

    // ####################################
}