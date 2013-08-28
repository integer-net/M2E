<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Add_StepTwoProduct extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingAddStepTwoProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing_product';
        //------------------------------

        // Set header text
        //------------------------------
        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = ' ' . Mage::helper('M2ePro')->__(Ess_M2ePro_Helper_Component_Ebay::TITLE);
        } else {
            $componentName = '';
        }

        $this->_headerText = Mage::helper('M2ePro')->__("Add%s Listing [Select Products]", $componentName);
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl('*/adminhtml_ebay_listing/add',array('step'=>'1'));
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'ProductGridHandlerObj.back_click(\'' . $url . '\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'ProductGridHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $url = $this->getUrl('*/adminhtml_ebay_listing/add',array('step'=>'2','save'=>'yes','back'=>'list'));
        $this->_addButton('save_and_go_to_listings_list', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'ProductGridHandlerObj.save_click(\'' . $url .'\')',
            'class'     => 'save'
        ));

        $url = $this->getUrl('*/adminhtml_ebay_listing/add',array('step'=>'2','save'=>'yes','back'=>'view'));
        $this->_addButton('save_and_go_to_listing_view', array(
            'label'     => Mage::helper('M2ePro')->__('Save And View Listing'),
            'onclick'   => 'ProductGridHandlerObj.save_click(\'' . $url .'\')',
            'class'     => 'save'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_product_help');
        return $helpBlock->toHtml() . parent::getGridHtml();
    }

    protected function _toHtml()
    {
        return '<div id="add_products_progress_bar"></div>'.
               '<div id="add_products_container">'.
                parent::_toHtml().
                '</div>';
    }
}