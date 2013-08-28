<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_NewProduct extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonTemplateNewProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_amazon_template_newProduct';
        //------------------------------

        $marketplace_id      = $this->getRequest()->getParam('marketplace_id');

        // Set header text
        //------------------------------
        $marketplaceInstance = Mage::helper('M2ePro/Component_Amazon')->getCachedObject('Marketplace',$marketplace_id);
        $this->_headerText = Mage::helper('M2ePro')->__('New ASIN Templates For "%s" Marketplace (Beta)',
                                                        $marketplaceInstance->getCode());
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $listingProductIds = Mage::helper('M2ePro')->getSessionValue('listing_product_ids');

        $listingId = Mage::helper('M2ePro/Component_Amazon')
            ->getObject('Listing_Product',reset($listingProductIds))
            ->getListingId();

        $url = $this->getUrl('*/adminhtml_amazon_listing/view',array(
            'id' => $listingId
        ));

        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
            'class'     => 'back'
        ));

        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));

        $tempUrl = $this->getUrl('*/adminhtml_amazon_template_newProduct/add',array(
            'marketplace_id' => $marketplace_id,
            'back' => Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_amazon_template_newProduct',array(
                'marketplace_id'      => $marketplace_id
            ))
        ));

        $this->_addButton('new', array(
            'label'     => Mage::helper('M2ePro')->__('Add New ASIN Template'),
            'onclick'   => 'setLocation(\''.$tempUrl.'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_template_newProduct_help');

        return $helpBlock->toHtml() . parent::getGridHtml();
    }
}