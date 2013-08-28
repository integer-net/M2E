<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Account extends Ess_M2ePro_Block_Adminhtml_Component_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('account');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_account';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Accounts');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        if (Mage::helper('M2ePro/Component_Ebay')->isActive()) {
            $this->_addButton('goto_general_templates', array(
                'label'     => Mage::helper('M2ePro')->__('General Templates'),
                'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_template_general/index").'\')',
                'class'     => 'button_link'
            ));
        }

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => $this->getAddButtonOnClickAction(),
            'class'     => 'add add-button-drop-down'
        ));
        //------------------------------
    }

    // ########################################

    protected function getEbayNewUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_account/new');
    }

    protected function getAmazonNewUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_account/new');
    }

    protected function getBuyNewUrl()
    {
        return $this->getUrl('*/adminhtml_buy_account/new');
    }

    protected function getPlayNewUrl()
    {
        return $this->getUrl('*/adminhtml_play_account/new');
    }

    // ########################################
}