<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayListingEditForm');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/listing/form.phtml');
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getUrl('*/adminhtml_ebay_listing/save'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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

        //----------------------------
        $this->sellingFormatTemplatesDropDown = Mage::helper('M2ePro/Component_Ebay')
                                                        ->getCollection('Template_SellingFormat')
                                                        ->getSize() < $maxRecordsQuantity;
        $this->descriptionsTemplatesDropDown = Mage::helper('M2ePro/Component_Ebay')
                                                        ->getCollection('Template_Description')
                                                        ->getSize() < $maxRecordsQuantity;
        $this->generalTemplatesDropDown = Mage::helper('M2ePro/Component_Ebay')
                                                        ->getCollection('Template_General')
                                                        ->getSize() < $maxRecordsQuantity;
        //----------------------------

        //----------------------------
        $synchronizationTemplatesCollection = Mage::helper('M2ePro/Component_Ebay')
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
            $listingCategories = Mage::helper('M2ePro/Component_Ebay')
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
        $buttonUrl =  $this->getUrl('*/adminhtml_ebay_template_sellingFormat/new');
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'EbayListingEditHandlerObj.openWindow(\'' . $buttonUrl . '\');',
                                'class'   => 'add add_new_selling_format_template_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('add_new_selling_format_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'EbayListingEditHandlerObj.reloadSellingFormatTemplates();',
                                'class'   => 'reload_selling_format_templates_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('reload_selling_format_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonUrl = $this->getUrl('*/adminhtml_ebay_template_general/new');
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'EbayListingEditHandlerObj.openWindow(\'' . $buttonUrl . '\');',
                                'class'   => 'add add_new_general_template_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('add_new_general_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'EbayListingEditHandlerObj.reloadGeneralTemplates();',
                                'class'   => 'reload_general_templates_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('reload_general_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonUrl = $this->getUrl('*/adminhtml_ebay_template_description/new');
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'EbayListingEditHandlerObj.openWindow(\'' . $buttonUrl . '\');',
                                'class'   => 'add add_new_description_template_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('add_new_description_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'EbayListingEditHandlerObj.reloadDescriptionTemplates();',
                                'class'   => 'reload_description_templates_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('reload_description_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonUrl = $this->getUrl('*/adminhtml_ebay_template_synchronization/new');
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Add New'),
                                'onclick' => 'EbayListingEditHandlerObj.openWindow(\'' . $buttonUrl . '\');',
                                'class'   => 'add add_new_synchronization_template_button',
                                'style'   => 'vertical-align: bottom'
                            ) );
        $this->setChild('add_new_synchronization_template_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh'),
                                'onclick' => 'EbayListingEditHandlerObj.reloadSynchronizationTemplates();',
                                'class'   => 'reload_synchronization_templates_button'
                            ) );
        $this->setChild('reload_synchronization_templates_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $treeSettings = array(
            'show_products_amount' => false,
            'hide_products_this_listing' => false
        );

        $treeBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_listing_category_tree','',array(
            'component'=>Ess_M2ePro_Helper_Component_Ebay::NICK,
            'tree_settings' => $treeSettings
        ));
        $this->setChild('categoriesTree', $treeBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }
}