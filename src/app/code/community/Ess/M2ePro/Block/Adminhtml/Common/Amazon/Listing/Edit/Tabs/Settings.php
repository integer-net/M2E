<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Common_Amazon_Listing_Edit_Tabs_Settings extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('amazonListingEditTabsSettings');
        //------------------------------

        $this->setTemplate('M2ePro/common/amazon/listing/tabs/settings.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $maxRecordsQuantity = Mage::helper('M2ePro/View_Common')->getAutocompleteMaxItems();
        //-------------------------------

        // Get attribute sets
        //------------------------------
        $this->attributesSets = Mage::helper('M2ePro/Magento_AttributeSet')->getAll();

        //------------------------------
        $data = array(
            'id'      => 'attribute_sets_select_all_button',
            'label'   => Mage::helper('M2ePro')->__('Select All'),
            'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
            'class'   => 'attribute_sets_select_all_button'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'id'      => 'attribute_sets_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'AmazonListingSettingsHandlerObj.attribute_sets_confirm();',
            'class'   => 'attribute_sets_confirm_button',
            'style'   => 'display: none'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //----------------------------
        $this->sellingFormatTemplatesDropDown = Mage::helper('M2ePro/Component_Amazon')
                                                    ->getCollection('Template_SellingFormat')
                                                    ->getSize() < $maxRecordsQuantity;
        //----------------------------

        //----------------------------
        $synchronizationTemplatesCollection = Mage::helper('M2ePro/Component_Amazon')
                                                    ->getCollection('Template_Synchronization')
                                                    ->setOrder('title', 'ASC');

        if ($synchronizationTemplatesCollection->getSize() < $maxRecordsQuantity) {
            $this->synchronizationsTemplatesDropDown = true;
            $templates = $synchronizationTemplatesCollection->toArray();

            foreach ($templates['items'] as $key => $value) {
                $templates['items'][$key]['title'] = Mage::helper('M2ePro')
                                                                ->escapeHtml($templates['items'][$key]['title']);
            }

            $this->synchronizationsTemplates = $templates['items'];
        } else {
            $this->synchronizationsTemplatesDropDown = false;
            $this->synchronizationsTemplates = array();
        }
        //----------------------------

        // Get selected categories
        //----------------------------
        if ($listingId = $this->getRequest()->getParam('id')) {
            $listingCategories = Mage::helper('M2ePro/Component_Amazon')
                                        ->getCachedObject('Listing',$listingId)
                                        ->getCategories();

            $categoriesIds = array();
            foreach ($listingCategories as $listingCategory) {
                $categoriesIds[] = $listingCategory['category_id'];
            }
            //----------------------------

            Mage::helper('M2ePro/Data_Global')->setValue('temp_listing_categories', $categoriesIds);
        }
        //----------------------------

        //------------------------------
        $url  = $this->getUrl('*/adminhtml_common_amazon_template_sellingFormat/new');
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Add New'),
            'onclick' => 'AmazonListingSettingsHandlerObj.openWindow(\'' . $url . '\');',
            'class'   => 'add add_new_selling_format_template_button',
            'style'   => 'vertical-align: bottom'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_new_selling_format_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'AmazonListingSettingsHandlerObj.reloadSellingFormatTemplates();',
            'class'   => 'reload_selling_format_templates_button',
            'style'   => 'vertical-align: bottom'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('reload_selling_format_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $url = $this->getUrl('*/adminhtml_common_amazon_template_synchronization/new');
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Add New'),
            'onclick' => 'AmazonListingSettingsHandlerObj.openWindow(\'' . $url . '\');',
            'class'   => 'add add_new_synchronization_template_button',
            'style'   => 'vertical-align: bottom'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('add_new_synchronization_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $data = array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'AmazonListingSettingsHandlerObj.reloadSynchronizationTemplates();',
            'class'   => 'reload_synchronization_templates_button',
            'style'   => 'vertical-align: bottom'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('reload_synchronization_templates_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}