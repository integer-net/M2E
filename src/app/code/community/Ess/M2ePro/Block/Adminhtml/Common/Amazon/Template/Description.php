<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_Description extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateDescription');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_template_description';
        //------------------------------

        // Set header text
        //------------------------------
        $componentName = !Mage::helper('M2ePro/View_Common_Component')->isSingleActiveComponent()
            ? Mage::helper('M2ePro/Component_Amazon')->getTitle().' ' : '';

        $this->_headerText = Mage::helper('M2ePro')->__("%sDescription Policies", $componentName);
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
        $url = $this->getUrl(
            '*/adminhtml_common_listing/index', array('tab' => Ess_M2ePro_Helper_Component_Amazon::NICK)
        );
        $this->_addButton('goto_listings', array(
            'label'     => Mage::helper('M2ePro')->__('Listings'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'button_link'
        ));
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_amazon_template_description/new');
        $this->_addButton('add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Description Policy'),
            'onclick'   => 'setLocation(\'' . $url .'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    // ####################################

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_amazon_template_description_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    // ########################################
}