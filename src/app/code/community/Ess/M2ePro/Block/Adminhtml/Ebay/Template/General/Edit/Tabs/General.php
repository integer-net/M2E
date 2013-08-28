<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_General_Edit_Tabs_General extends Mage_Adminhtml_Block_Widget
{
    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateGeneralEditTabsGeneral');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/general/general.phtml');
    }

    // ####################################

    protected function _beforeToHtml()
    {
        //------------------------------
        $attributesSets = Mage::helper('M2ePro/Magento')->getAttributeSets();
        $this->setData('attributes_sets', $attributesSets);
        //------------------------------

        //------------------------------
        $marketplaces = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Marketplace')
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('title', 'ASC')
            ->toArray();
        $this->setData('marketplaces', $marketplaces['items']);
        //------------------------------

        //-------------------------------
        $accounts = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Account')
            ->setOrder('title', 'ASC')
            ->toArray();
        $this->setData('accounts', $accounts['items']);
        //-------------------------------

        //------------------------------
        $this->attribute_set_locked = false;
        if (Mage::helper('M2ePro')->getGlobalValue('temp_data')->getId()) {
            $this->attribute_set_locked = Mage::helper('M2ePro')->getGlobalValue('temp_data')->isLocked();
        }
        //------------------------------

        //------------------------------
        $formData = Mage::helper('M2ePro')->getGlobalValue('temp_data');
        $formData = $formData ? $formData->toArray() : array();

        empty($formData['categories_main_id']) || $this->generateCategories('main', $formData);
        empty($formData['categories_secondary_id']) || $this->generateCategories('secondary', $formData);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_select_all_button',
                                'label'   => Mage::helper('M2ePro')->__('Select All'),
                                'onclick' => 'AttributeSetHandlerObj.selectAllAttributeSets();',
                                'class' => 'attribute_sets_select_all_button'
                            ) );
        $this->setChild('attribute_sets_select_all_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'attribute_sets_confirm_button',
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateGeneralHandlerObj.attribute_sets_confirm();',
                                'class' => 'attribute_sets_confirm_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('attribute_sets_confirm_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.confirmCategory(\'main\');',
                                'class' => 'confirm_main_category_button'
                            ) );
        $this->setChild('confirm_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'main_ebay_category_change_button',
                                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.initCategoryEdit(\'main\');',
                                'class' => 'change_main_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('change_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'main_ebay_category_cancel_button',
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.cancelCategoryEdit(\'main\');',
                                'class' => 'cancel_main_category_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('cancel_main_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.selectCategoryById(\'main\');',
                                'class' => 'select_main_category_by_id_button'
                            ) );
        $this->setChild('select_main_category_by_id_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.confirmCategory(\'secondary\');',
                                'class' => 'confirm_secondary_category_button'
                            ) );
        $this->setChild('confirm_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_change_button',
                                'label'   => Mage::helper('M2ePro')->__('Change Category'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.initCategoryEdit(\'secondary\');',
                                'class' => 'change_secondary_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('change_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_empty_button',
                                'label'   => Mage::helper('M2ePro')->__('Reset Category'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.emptyCategory(\'secondary\');',
                                'class' => 'reset_secondary_category_button',
                                'style' => 'display: none; margin-right: 5px;'
                            ) );
        $this->setChild('reset_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'id' => 'secondary_ebay_category_cancel_button',
                                'label'   => Mage::helper('M2ePro')->__('Cancel'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.cancelCategoryEdit(\'secondary\');',
                                'class' => 'cancel_secondary_category_button',
                                'style' => 'display: none'
                            ) );
        $this->setChild('cancel_secondary_category_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Confirm'),
                                'onclick' => 'EbayTemplateGeneralCategoryHandlerObj.selectCategoryById(\'secondary\');',
                                'class' => 'select_secondary_category_by_id_button'
                            ) );
        $this->setChild('select_secondary_category_by_id_button',$buttonBlock);
        //------------------------------

        //------------------------------
        $buttonBlock = $this->getLayout()
                            ->createBlock('adminhtml/widget_button')
                            ->setData( array(
                                'label'   => Mage::helper('M2ePro')->__('Refresh Store Categories'),
                                'onclick' => 'EbayTemplateGeneralHandlerObj.updateEbayStoreByAccount_click();',
                                'class' => 'update_ebay_store_button'
                            ) );
        $this->setChild('update_ebay_store_button',$buttonBlock);
        //------------------------------

        return parent::_beforeToHtml();
    }

    protected function generateCategories($type, $formData)
    {
        $key = $type == 'main' ? 'categories_main_id' : 'categories_secondary_id';

        $breadcrumbs = array();
        $selectedIds = array();

        $id = $formData[$key];
        $selectedId = $id;

        /** @var $marketplace Ess_M2ePro_Model_Ebay_Marketplace */
        $marketplace = Mage::helper('M2ePro/Component_Ebay')
            ->getCachedObject('Marketplace',$formData['marketplace_id'])
            ->getChildObject();

        for ($i = 1; $i < 8; $i++) {

            $category = $marketplace->getCategory($id);

            if (!isset($category['title'])) {
                break;
            }

            $breadcrumbs[] = $category['title'];
            $selectedIds[] = $category['category_id'];

            if (!$category['parent_id']) {
                break; // root node
            }

            $id = $category['parent_id'];
        }

        $categoryData = array('breadcrumbs' => implode(' > ', array_reverse($breadcrumbs)) . " ($selectedId)" );
        $parentIds = $selectedIds;
        $parentIds[] = 0;
        $selectedIds = array_reverse($selectedIds);
        $parentIds = array_reverse($parentIds);
        array_pop($parentIds);

        $i = 1;
        foreach ($parentIds as $id) {
            $categories = $marketplace->getChildCategories($id);

            $categoryData["select-$i"] = '<select name="'
                                         .$type
                                         .'_ebay_category-' . $i . '" id="' . $type . '_ebay_category-' . $i
                                         .'" class="ebay-cat ' . $type . '-ebay-category-hidden">';
            foreach ($categories as $category) {
                $categoryData["select-$i"] .= '<option is_leaf="' . $category['is_leaf'] . '" value="'
                                              .$category['category_id'] . '"'
                                              .($selectedIds[$i - 1] == $category['category_id']
                                                    ? ' selected="selected"' : '') . '>' . $category['title']
                                              .'</option>' . PHP_EOL;
            }
            $categoryData["select-$i"] .= '</select>';
            $i++;
        }

        $this->setData($type . '_category', $categoryData);
    }

    // ####################################
}