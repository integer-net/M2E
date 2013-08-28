<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_NewProduct_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProductEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template_newProduct';
        $this->_mode = 'edit';
        //------------------------------

        $marketplace_id      = $this->getRequest()->getParam('marketplace_id');

        // Set header text
        //------------------------------
        $marketplaceInstance = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Marketplace',$marketplace_id);

        if ($this->getRequest()->getParam('id')) {
            $this->_headerText = Mage::helper('M2ePro')->__('Edit "New ASIN" Template For "%s" Marketplace (Beta)',
                                                            $marketplaceInstance->getCode());
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Add "New ASIN" Template For "%s" Marketplace (Beta)',
                                                            $marketplaceInstance->getCode());
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
            (Mage::helper('M2ePro')->getSessionValue('listing_product_ids') ||
            $this->getRequest()->getParam('listing_product_id'))) {
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

        $params = array('marketplace_id' => $marketplace_id);
        if ($listingProductId = $this->getRequest()->getParam('listing_product_id')) {
            $params['listing_product_id'] = $listingProductId;
        }

        $url = $this->getUrl('*/adminhtml_amazon_template_newProduct/add',$params);

        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));

        if ((int)$this->getRequest()->getParam('save_and_assign',1) &&
            Mage::helper('M2ePro')->getSessionValue('listing_product_ids')) {

            $url = $this->getUrl('*/adminhtml_amazon_template_newProduct/add',array(
                'marketplace_id' => $marketplace_id,
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