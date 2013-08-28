<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Config extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('config');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_config';
        //------------------------------

        // Set header text
        //------------------------------
        if (Mage::helper('M2ePro')->getGlobalValue('config_mode') == 'ess') {
            $this->_headerText = Mage::helper('M2ePro')->__('ESS Config Data');
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('M2ePro Config Data');
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
        //------------------------------
    }

    protected function _beforeToHtml()
    {
        $this->setChild('edit', $this->getLayout()->createBlock('M2ePro/adminhtml_config_edit'));
        $this->setChild('grid', $this->getLayout()->createBlock('M2ePro/adminhtml_config_view_grid'));
        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        return $this->getChildHtml('edit').$this->getChildHtml('grid');
    }
}