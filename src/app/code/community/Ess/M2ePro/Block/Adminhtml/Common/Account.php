<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Account extends Ess_M2ePro_Block_Adminhtml_Common_Component_Grid_Container
{
    // ########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('account');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_account';
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

        //------------------------------
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Account'),
            'onclick'   => $this->getAddButtonOnClickAction(),
            'class'     => 'add add-button-drop-down'
        ));
        //------------------------------
    }

    // ########################################

    protected function getAmazonNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_amazon_account/new');
    }

    protected function getBuyNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_buy_account/new');
    }

    protected function getPlayNewUrl()
    {
        return $this->getUrl('*/adminhtml_common_play_account/new');
    }

    // ########################################
}