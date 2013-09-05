<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Template_NewProduct_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProductEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_amazon_template_newProduct';
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
        //------------------------------

        $hasBack = $this->getRequest()->getParam('back');
        $listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('listing_product_ids');
        $listingProductId = $this->getRequest()->getParam('listing_product_id');

        if ($hasBack && ($listingProductIds || $listingProductId)) {
            //------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl();
            $this->_addButton('back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'CommonHandlerObj.back_click(\'' . $url .'\')',
                'class'     => 'back'
            ));
            //------------------------------
        }

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        //------------------------------
        $params = array('marketplace_id' => $marketplace_id);
        if ($listingProductId) {
            $params['listing_product_id'] = $listingProductId;
        }
        $url = $this->getUrl('*/adminhtml_common_amazon_template_newProduct/add', $params);
        $this->_addButton('save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
            'class'     => 'save'
        ));
        //------------------------------

        $saveAndAssign = (int)$this->getRequest()->getParam('save_and_assign', 1);

        if ($saveAndAssign && $listingProductIds) {
            //------------------------------
            $url = $this->getUrl(
                '*/adminhtml_common_amazon_template_newProduct/add',
                array(
                    'marketplace_id' => $marketplace_id,
                    'do_map'         => true
                )
            );
            $this->_addButton('save_and_map', array(
                'label'     => Mage::helper('M2ePro')->__('Save And Assign'),
                'onclick'   => 'CommonHandlerObj.save_click(\''.$url.'\')',
                'class'     => 'save'
            ));
            //------------------------------
        }
    }
}