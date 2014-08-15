<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Template_NewProduct extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProduct');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_common_buy_template_newProduct';
        //------------------------------

        // Set header text
        //------------------------------
        $this->_headerText = Mage::helper('M2ePro')->__('New SKU Templates For Rakuten.com (Beta)');
        //------------------------------

        // Set buttons actions
        //------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $listingProductIds = Mage::helper('M2ePro/Data_Session')->getValue('buy_listing_product_ids');

        $listingId = Mage::helper('M2ePro/Component_Buy')
            ->getObject('Listing_Product',reset($listingProductIds))
            ->getListingId();

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_buy_listing/view',
            array(
                'id' => $listingId
            )
        );
        $this->_addButton('back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'onclick'   => 'CommonHandlerObj.back_click(\''.$url.'\')',
            'class'     => 'back'
        ));
        //------------------------------

        //------------------------------
        $this->_addButton('reset', array(
            'label'     => Mage::helper('M2ePro')->__('Refresh'),
            'onclick'   => 'CommonHandlerObj.reset_click()',
            'class'     => 'reset'
        ));
        //------------------------------

        $backUrl = Mage::helper('M2ePro')->makeBackUrlParam('*/adminhtml_common_buy_template_newProduct');

        //------------------------------
        $url = $this->getUrl(
            '*/adminhtml_common_buy_template_newProduct/add',
            array(
                'back' => $backUrl
            )
        );
        $this->_addButton('new', array(
            'label'     => Mage::helper('M2ePro')->__('Add New SKU Template'),
            'onclick'   => 'setLocation(\''.$url.'\')',
            'class'     => 'add'
        ));
        //------------------------------
    }

    public function getGridHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_template_newProduct_help');

        return $helpBlock->toHtml() .  parent::getGridHtml();
    }
}