<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Template_NewProduct_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyTemplateNewProductEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/buy/template/newProduct/tabs/general.phtml');
    }

    protected function _beforeToHtml()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->listingAttributeSetsIds = array();
        if (!$this->getRequest()->getParam('id') &&
            $listingProductIds = Mage::helper('M2ePro')->getSessionValue('buy_listing_product_ids')) {

            $this->listingAttributeSetsIds = (array)Mage::helper('M2ePro/Component_Buy')
                ->getObject('Listing_Product',reset($listingProductIds))
                ->getListing()
                ->getAttributeSetsIds();
        }

        $this->nodes = $connRead->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_buy_dictionary_category'))
            ->where('parent_id = ?', 0)
            ->query()
            ->fetchAll();
        !is_array($this->nodes) && $this->nodes = array();

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'category_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.confirmCategory();',
        ) );
        $this->setChild('category_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id' => 'category_change_button',
            'label'   => Mage::helper('M2ePro')->__('Change Category'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.changeCategory();',
        ) );
        $this->setChild('category_change_button',$buttonBlock);
        //------------------------------

        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);

        $temp = Mage::helper('M2ePro')->getGlobalValue('temp_data');

        $this->attribute_set_locked = false;

        if (!is_null($temp)) {
            $this->attribute_set_locked = (bool)Mage::getModel('M2ePro/Buy_Listing_Product')->getCollection()
                ->addFieldToFilter('template_new_product_id',$temp['category']['id'])
                ->getSize();
        }

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'attribute_sets_select_all_button',
            'label'   => Mage::helper('M2ePro')->__('Select All'),
            'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
            'class'   => 'attribute_sets_select_all_button'
        ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'attribute_sets_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => '',
            'class'   => 'attribute_sets_confirm_button',
            'style'   => 'display: none'
        ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'browse_category_button',
            'label'   => Mage::helper('M2ePro')->__('Browse'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.browse_category.showCenter(true)'
        ) );
        $this->setChild('browse_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'search_category_button',
            'label'   => Mage::helper('M2ePro')->__('Search By Keywords'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.search_category.showCenter(true)',
        ) );
        $this->setChild('search_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'search_category_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Search'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.searchClick()',
        ) );
        $this->setChild('search_category_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'reset_category_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Reset'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.resetSearchClick()',
            'style'   => 'display: none'
        ) );
        $this->setChild('reset_category_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'close_browse_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.browse_category.close()',
        ) );
        $this->setChild('close_browse_popup_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'close_search_popup_button',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'BuyTemplateNewProductHandlerObj.search_category.close()',
        ) );
        $this->setChild('close_search_popup_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}