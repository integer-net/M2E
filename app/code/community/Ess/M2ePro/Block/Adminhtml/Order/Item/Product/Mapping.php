<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Order_Item_Product_Mapping extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/order/item/product/mapping.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'    => 'product_mapping_submit_button',
                                'label' => Mage::helper('M2ePro')->__('Confirm'),
                                'class' => 'product_mapping_submit_button submit',
                                'onclick' => 'OrderEditItemHandlerObj.assignProduct();'
                            ) );
        $this->setChild('product_mapping_submit_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id'    => 'product_mapping_advanced_search_button',
                                'label' => Mage::helper('M2ePro')->__('Advanced Search'),
                                'class' => 'product_mapping_advanced_search_button submit',
                                'onclick' => '$(\'help_grid\').toggle()'
                        ) );
        $this->setChild('product_mapping_advanced_search_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $this->setChild(
            'product_mapping_grid', $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_product_mapping_grid')
        );
        //------------------------------

        parent::_beforeToHtml();
    }
}
