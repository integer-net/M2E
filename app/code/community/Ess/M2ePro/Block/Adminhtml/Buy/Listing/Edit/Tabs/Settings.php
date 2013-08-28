<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Buy_Listing_Edit_Tabs_Settings extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('buyListingEditTabsSettings');
        //------------------------------

        $this->setTemplate('M2ePro/buy/listing/tabs/settings.phtml');
    }

    protected function _beforeToHtml()
    {
        //-------------------------------
        $maxRecordsQuantity = Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/autocomplete/', 'max_records_quantity');
        $maxRecordsQuantity <= 0 && $maxRecordsQuantity = 100;
        //-------------------------------

        // Get attribute sets
        //------------------------------
        $this->attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();

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

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'id'      => 'attribute_sets_confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => 'BuyListingSettingsHandlerObj.attribute_sets_confirm();',
            'class'   => 'attribute_sets_confirm_button',
            'style'   => 'display: none'
        ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //----------------------------
        $this->sellingFormatTemplatesDropDown = Mage::helper('M2ePro/Component_Buy')
            ->getCollection('Template_SellingFormat')
            ->getSize() < $maxRecordsQuantity;
        //----------------------------

        //----------------------------
        $synchronizationTemplatesCollection = Mage::helper('M2ePro/Component_Buy')
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
            $listingCategories = Mage::helper('M2ePro/Component_Buy')
                                        ->getCachedObject('Listing',$listingId)
                                        ->getCategories();

            $categoriesIds = array();
            foreach ($listingCategories as $listingCategory) {
                $categoriesIds[] = $listingCategory['category_id'];
            }
            //----------------------------

            Mage::helper('M2ePro')->setGlobalValue('temp_listing_categories', $categoriesIds);
        }
        //----------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Add New'),
            'onclick' => 'BuyListingSettingsHandlerObj.openWindow(\''
                .$this->getUrl('*/adminhtml_buy_template_sellingFormat/new')
                .'\');',
            'class'   => 'add add_new_selling_format_template_button',
            'style'   => 'vertical-align: bottom'
        ) );
        $this->setChild('add_new_selling_format_template_button',$buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'BuyListingSettingsHandlerObj.reloadSellingFormatTemplates();',
            'class'   => 'reload_selling_format_templates_button',
            'style'   => 'vertical-align: bottom'
        ) );
        $this->setChild('reload_selling_format_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Add New'),
            'onclick' => 'BuyListingSettingsHandlerObj.openWindow(\''
                .$this->getUrl('*/adminhtml_buy_template_synchronization/new')
                .'\');',
            'class'   => 'add add_new_synchronization_template_button',
            'style'   => 'vertical-align: bottom'
        ) );
        $this->setChild('add_new_synchronization_template_button',$buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData( array(
            'label'   => Mage::helper('M2ePro')->__('Refresh'),
            'onclick' => 'BuyListingSettingsHandlerObj.reloadSynchronizationTemplates();',
            'class'   => 'reload_synchronization_templates_button',
            'style'   => 'vertical-align: bottom'
        ) );
        $this->setChild('reload_synchronization_templates_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}
