<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Template_Description extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('templateDescription');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_template_description';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE).' ';
        } else {
            $componentName = '';
        }

        $this->_headerText = Mage::helper('M2ePro')->__('%sDescription Templates', $componentName);
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
            'onclick'   => 'setLocation(\'' .$this->getUrl('*/adminhtml_listing/index').'\')',
            'class'     => 'button_link'
        ));

        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Description Template'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/adminhtml_ebay_template_description/new').'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    // ########################################
}