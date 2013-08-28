<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Template_Synchronization extends Ess_M2ePro_Block_Adminhtml_Component_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateSynchronization');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_template_synchronization';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('Synchronization Templates');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_listing/index").'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('goto_synchronization', array(
            'label'     => Mage::helper('M2ePro')->__('Synchronization Settings'),
            'onclick'   => 'setLocation(\'' .$this->getUrl("*/adminhtml_synchronization/index").'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Synchronization Template'),
            'onclick'   => $this->getAddButtonOnClickAction(),
            'class'     => 'add add-button-drop-down'
        ));
        //------------------------------
    }

    // ########################################

    protected function getEbayNewUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_template_synchronization/new');
    }

    protected function getAmazonNewUrl()
    {
        return $this->getUrl('*/adminhtml_amazon_template_synchronization/new');
    }

    protected function getBuyNewUrl()
    {
        return $this->getUrl('*/adminhtml_buy_template_synchronization/new');
    }

    protected function getPlayNewUrl()
    {
        return $this->getUrl('*/adminhtml_play_template_synchronization/new');
    }

    // ########################################
}