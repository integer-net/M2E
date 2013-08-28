<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_NewProduct_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_buy_template_newProduct';
        $this->_mode = 'edit';
        //------------------------------

        // Set header text
        //------------------------------

        if ($this->getRequest()->getParam('id')) {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit New SKU Template For Rakuten.com (Beta)');
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add New SKU Template For Rakuten.com (Beta)');
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

        if (!is_null($this->getRequest()->getParam('back')) &&
            (Mage::helper('M2ePro')->getSessionValue('buy_listing_product_ids') ||
            $this->getRequest()->getParam('listing_product_id'))){
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\'' . Mage::helper('M2ePro')->getBackUrl().'\')',
                'class'     => 'back'
            ));
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $params = array();
        if ($listingProductId = $this->getRequest()->getParam('listing_product_id')) {
            $params['listing_product_id'] = $listingProductId;
        }
        $url = $this->getUrl('*/adminhtml_buy_template_newProduct/add',$params);

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));

        if ((int)$this->getRequest()->getParam('save_and_assign',1) &&
            Mage::helper('M2ePro')->getSessionValue('buy_listing_product_ids')) {

            $url = $this->getUrl('*/adminhtml_buy_template_newProduct/add',array(
                'do_map' => true
            ));

            $this->_addButton('save_and_map', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Assign'),
                'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
                'class'     => 'save'
            ));

        }

        //------------------------------
    }
}