<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_CategoryController extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    //#############################################

    public function getChooserHtmlAction()
    {
        //------------------------------
        $selectedCategoriesJson = $this->getRequest()->getParam('selected_categories');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $divId = $this->getRequest()->getParam('div_id');
        $attributesString = $this->getRequest()->getParam('attributes');
        $interfaceMode = $this->getRequest()->getParam('interface_mode');
        $isShowEditLinks = $this->getRequest()->getParam('is_show_edit_links');
        $selectCallback = $this->getRequest()->getParam('select_callback');
        $unSelectCallback = $this->getRequest()->getParam('unselect_callback');

        $selectedCategories = array();
        if (!is_null($selectedCategoriesJson)) {
            $selectedCategories = json_decode($selectedCategoriesJson, true);
        }

        $attributes = array();
        if (!is_null($attributesString)) {
            $attributes = explode(',', $attributesString);
        }
        //------------------------------

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        foreach ($selectedCategories as $type => &$selectedCategory) {
            if (!empty($selectedCategory['path'])) {
                continue;
            }

            switch ($selectedCategory['mode']) {
                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY:
                case Ess_M2ePro_Model_Ebay_Template_Category::TAX_CATEGORY_MODE_VALUE:
                    if (in_array($type, $ebayCategoryTypes)) {
                        $selectedCategory['path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                            ->getPathById(
                                $selectedCategory['value'],
                                $marketplaceId
                            );

                        $selectedCategory['path'] .= ' (' . $selectedCategory['value'] . ')';

                        Mage::helper('M2ePro/Component_Ebay_Category')
                            ->addRecent(
                                $selectedCategory['value'],
                                $marketplaceId,
                                $type,
                                $selectedCategory['path']
                            );
                    } elseif (in_array($type, $storeCategoryTypes)) {
                        $selectedCategory['path'] = Mage::helper('M2ePro/Component_Ebay_Category')
                            ->getStorePathById(
                                $selectedCategory['value'],
                                $accountId
                            );

                        $selectedCategory['path'] .= ' (' . $selectedCategory['value'] . ')';

                        Mage::helper('M2ePro/Component_Ebay_Category')
                            ->addRecent(
                                $selectedCategory['value'],
                                $accountId,
                                $type,
                                $selectedCategory['path']
                            );
                    } else {
                        // tax category
                        break;
                    }

                    break;

                case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE:
                    $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')
                        ->getAttributeLabel($selectedCategory['value']);

                    $selectedCategory['path'] = Mage::helper('M2ePro')->__('Magento Attribute');
                    $selectedCategory['path'] .= ' -> ' . $attributeLabel;
                    break;
            }
        }

        //------------------------------
        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser $chooserBlock */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setAccountId($accountId);
        $chooserBlock->setDivId($divId);

        if (!empty($selectedCategories)) {
            $chooserBlock->setConvertedInternalData($selectedCategories);
        }
        if (!empty($attributes)) {
            $chooserBlock->setAttributes($attributes);
        }
        if (!empty($interfaceMode)) {
            $chooserBlock->setInterfaceMode($interfaceMode);
        }
        if (!empty($isShowEditLinks)) {
            $chooserBlock->setShowEditLinks($isShowEditLinks);
        }
        if (!empty($selectCallback)) {
            $chooserBlock->setSelectCallback($selectCallback);
        }
        if (!empty($unselectCallback)) {
            $chooserBlock->setUnselectCallback($unSelectCallback);
        }
        //------------------------------

        $this->getResponse()->setBody($chooserBlock->toHtml());
    }

    public function getChooserEditHtmlAction()
    {
        //------------------------------
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedMode = $this->getRequest()->getParam(
            'selected_mode', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE
        );
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');
        $attributesString = $this->getRequest()->getParam('attributes');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');

        $attributes = array();
        if (!is_null($attributesString)) {
            $attributes = explode(',', $attributesString);
        }
        //------------------------------

        //------------------------------
        $editBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_edit');
        $editBlock->setCategoryType($categoryType);

        if (!empty($attributes)) {
            $editBlock->setAttributes(explode(',', $attributes));
        }
        //------------------------------

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();

        if (in_array($categoryType, $ebayCategoryTypes)) {
            $recentCategories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $marketplaceId, $categoryType
            );
        } else {
            $recentCategories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent(
                $accountId, $categoryType
            );
        }
        if (empty($recentCategories)) {
            Mage::helper('M2ePro/Data_Global')->setValue('category_chooser_hide_recent', true);
        }

        if ($selectedMode != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
            if (empty($selectedPath)) {
                switch ($selectedMode) {
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY:
                        if (in_array($categoryType, $ebayCategoryTypes)) {
                            $selectedPath = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById(
                                $selectedValue, $marketplaceId
                            );

                            $selectedPath .= ' (' . $selectedValue . ')';
                        } else {
                            $selectedPath = Mage::helper('M2ePro/Component_Ebay_Category')->getStorePathById(
                                $selectedValue, $accountId
                            );
                        }

                        break;
                    case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
                    case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE:
                        $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($selectedValue);
                        $selectedPath = Mage::helper('M2ePro')->__('Magento Attribute') . ' -> ' . $attributeLabel;

                        break;
                }
            }

            $editBlock->setSelectedCategory(array(
                'mode' => $selectedMode,
                'value' => $selectedValue,
                'path' => $selectedPath
            ));
        }

        $this->getResponse()->setBody($editBlock->toHtml());
    }

    public function getChildCategoriesAction()
    {
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $parentCategoryId  = $this->getRequest()->getParam('parent_id');
        $categoryType = $this->getRequest()->getParam('category_type');

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        $data = array();

        if (is_null($parentCategoryId)
            || (in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId))
            || (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody(json_encode($data));
            return;
        }

        if (in_array($categoryType, $ebayCategoryTypes)) {
            $data = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',$marketplaceId)
                ->getChildObject()
                ->getChildCategories($parentCategoryId);
        } elseif (in_array($categoryType, $storeCategoryTypes))  {
            $tableAccountStoreCategories = Mage::getSingleton('core/resource')
                ->getTableName('m2epro_ebay_account_store_category');

            /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
            $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

            $dbSelect = $connRead->select()
                ->from($tableAccountStoreCategories,'*')
                ->where('`account_id` = ?',(int)$accountId)
                ->where('`parent_id` = ?', (int)$parentCategoryId)
                ->order(array('sorder ASC'));

            $data = $connRead->fetchAll($dbSelect);
        }

        $this->getResponse()->setBody(json_encode($data));
    }

    public function getPathAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $accountId = $this->getRequest()->getParam('account_id');
        $value = $this->getRequest()->getParam('value');
        $mode = $this->getRequest()->getParam('mode');
        $categoryType = $this->getRequest()->getParam('category_type');

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        if (is_null($value) || is_null($mode)
            || (in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId))
            || (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody('');
            return;
        }

        $path = '';

        switch ($mode) {
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY:
            case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_EBAY:
                if (in_array($categoryType, $ebayCategoryTypes)) {
                    $path = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById($value, $marketplaceId);
                } else {
                    $path = Mage::helper('M2ePro/Component_Ebay_Category')->getStorePathById($value, $accountId);
                }

                $path .= ' (' . $value . ')';

                break;
            case Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE:
            case Ess_M2ePro_Model_Ebay_Template_Category::STORE_CATEGORY_MODE_ATTRIBUTE:
                $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel($value);
                $path = Mage::helper('M2ePro')->__('Magento Attribute') . ' -> ' . $attributeLabel;

                break;
        }

        $this->getResponse()->setBody($path);
    }

    public function searchAction()
    {
        $query = $this->getRequest()->getParam('query');
        $categoryType = $this->getRequest()->getParam('category_type');
        $marketplaceId  = $this->getRequest()->getParam('marketplace_id');
        $accountId  = $this->getRequest()->getParam('account_id');
        $result = array();

        $ebayCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes();
        $storeCategoryTypes = Mage::helper('M2ePro/Component_Ebay_Category')->getStoreCategoryTypes();

        if (is_null($query)
            || (in_array($categoryType, $ebayCategoryTypes) && is_null($marketplaceId))
            || (in_array($categoryType, $storeCategoryTypes) && is_null($accountId))
        ) {
            $this->getResponse()->setBody(json_encode($result));
            return;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        if (in_array($categoryType, $ebayCategoryTypes)) {
            $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');
        } else {
            $tableName = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_account_store_category');
        }

        $dbSelect = $connRead->select();
        $dbSelect->from($tableName, 'category_id')
                 ->where('is_leaf = ?', 1);
        if (in_array($categoryType, $ebayCategoryTypes)) {
            $dbSelect->where('marketplace_id = ?', (int)$marketplaceId);
        } else {
            $dbSelect->where('account_id = ?', (int)$accountId);
        }

        $tempDbSelect = clone $dbSelect;
        $isSearchById = false;

        if (is_numeric($query)) {
            $dbSelect->where('category_id = ?', $query);
            $isSearchById = true;
        } else {
            $dbSelect->where('title like ?', '%' . $query . '%');
        }

        $ids = $connRead->fetchAll($dbSelect);
        if (empty($ids) && $isSearchById) {
            $tempDbSelect->where('title like ?', '%' . $query . '%');
            $ids = $connRead->fetchAll($tempDbSelect);
        }

        foreach ($ids as $categoryId) {
            if (in_array($categoryType, $ebayCategoryTypes)) {
                $treePath = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById(
                    $categoryId['category_id'], $marketplaceId
                );
            } else {
                $treePath = Mage::helper('M2ePro/Component_Ebay_Category')->getStorePathById(
                    $categoryId['category_id'], $accountId
                );
            }

            $result[] = array(
                'titles' => $treePath,
                'id' => $categoryId['category_id']
            );
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    public function getAttributeLabelsAction()
    {
        $attributesParam = $this->getRequest()->getParam('attributes');
        if (is_null($attributesParam)) {
            $this->getResponse()->setBody('');
            return;
        }

        $attributes = explode(',', $attributesParam);
        $labels = Mage::helper('M2ePro/Magento_Attribute')->getAttributesLabels($attributes);

        $this->getResponse()->setBody(json_encode($labels));
    }

    public function getRecentAction()
    {
        $marketplaceId = $this->getRequest()->getParam('marketplace');
        $accountId = $this->getRequest()->getParam('account');
        $categoryType = $this->getRequest()->getParam('category_type');
        $selectedCategory = $this->getRequest()->getParam('selected_category');

        if (in_array($categoryType, Mage::helper('M2ePro/Component_Ebay_Category')->getEbayCategoryTypes())) {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent($marketplaceId, $categoryType);
        } else {
            $categories = Mage::helper('M2ePro/Component_Ebay_Category')->getRecent($accountId, $categoryType);
        }

        if (!is_null($selectedCategory) && isset($categories[(int)$selectedCategory])) {
            unset($categories[(int)$selectedCategory]);
        }

        $this->getResponse()->setBody(json_encode($categories));
    }

    //#############################################

    public function getAttributeTypeAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attribute = Mage::getResourceModel('catalog/product')->getAttribute($attributeCode);

        if ($attribute === false) {
            $this->getResponse()->setBody(json_encode(array('type' => null)));
            return;
        }

        $this->getResponse()->setBody(json_encode(array('type' => $attribute->getBackendType())));
    }

    public function getJsonSpecificsFromPostAction()
    {
        $itemSpecifics = $this->_getSpecificsFromPost($this->getRequest()->getPost());
        $this->getResponse()->setBody(json_encode($itemSpecifics));
    }

    public function getSpecificHtmlAction()
    {
        $post = $this->getRequest()->getPost();
        $specifics = $this->_getSpecificsFromPost($post);

        $categoryMode = $this->getRequest()->getParam('category_mode');
        $categoryValue = $this->getRequest()->getParam('category_value');
        $marketplaceId = $this->getRequest()->getParam('marketplace_id');
        $divId = $this->getRequest()->getParam('div_id');

        //--------------------
        $internalData = array();
        $internalData['motors_specifics_attribute'] = isset($post['motors_specifics_attribute'])
            ? $post['motors_specifics_attribute'] : '';
        //--------------------

        $categoryBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');

        $categoryBlock->setMarketplaceId($marketplaceId);
        $categoryBlock->setCategoryMode($categoryMode);
        $categoryBlock->setCategoryValue($categoryValue);
        $categoryBlock->setDivId($divId);
        $categoryBlock->setSelectedSpecifics($specifics);

        $this->getResponse()->setBody($categoryBlock->toHtml());
    }

    //#############################################

    protected function _getSpecificsFromPost($post)
    {
        $itemSpecifics = array();
        for ($i=0; true; $i++) {
            if (!isset($post['item_specifics_mode_'.$i])) {
                break;
            }
            if (!isset($post['custom_item_specifics_value_mode_'.$i])) {
                continue;
            }
            $ebayRecommendedTemp = array();
            if (isset($post['item_specifics_value_ebay_recommended_'.$i])) {
                $ebayRecommendedTemp = (array)$post['item_specifics_value_ebay_recommended_'.$i];
            }
            foreach ($ebayRecommendedTemp as $key=>$temp) {
                $tempParsed = explode('-|-||-|-',$temp);
                $ebayRecommendedTemp[$key] = array(
                    'id' => base64_decode($tempParsed[0]),
                    'value' => base64_decode($tempParsed[1])
                );
            }

            $attributeValue = '';
            $customAttribute = '';

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS;
            if ($post['item_specifics_mode_'.$i] == $temp) {
                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['item_specifics_value_mode_' . $i] == $temp) {
                    $attributeValue = $post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                    $attributeValue = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_EBAY_RECOMMENDED;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_NONE;
                if ((int)$post['item_specifics_value_mode_'.$i] == $temp) {
                    $customAttribute = '';
                    $attributeValue = '';
                }

                $itemSpecifics[] = array(
                    'mode'                   => (int)$post['item_specifics_mode_'.$i],
                    'mode_relation_id'       => (int)$post['item_specifics_mode_relation_id_'.$i],
                    'attribute_id'           => $post['item_specifics_attribute_id_'.$i],
                    'attribute_title'        => $post['item_specifics_attribute_title_'.$i],
                    'value_mode'             => (int)$post['item_specifics_value_mode_'.$i],
                    'value_ebay_recommended' => json_encode($ebayRecommendedTemp),
                    'value_custom_value'     => $attributeValue,
                    'value_custom_attribute' => $customAttribute
                );
            }

            $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_CUSTOM_ITEM_SPECIFICS;
            if ($post['item_specifics_mode_'.$i] == $temp) {
                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_VALUE;
                if ((int)$post['custom_item_specifics_value_mode_' . $i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_value_'.$i];
                    $attributeValue = $post['item_specifics_value_custom_value_'.$i];
                    $customAttribute = '';
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = $post['item_specifics_value_custom_attribute_'.$i];;
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $temp = Ess_M2ePro_Model_Ebay_Template_Category_Specific::VALUE_MODE_CUSTOM_LABEL_ATTRIBUTE;
                if ((int)$post['custom_item_specifics_value_mode_'.$i] == $temp) {
                    $attributeTitle = $post['custom_item_specifics_label_custom_label_attribute_'.$i];
                    $attributeValue = '';
                    $customAttribute = $post['item_specifics_value_custom_attribute_'.$i];
                }

                $itemSpecifics[] = array(
                    'mode'                      => (int)$post['item_specifics_mode_' . $i],
                    'mode_relation_id'          => 0,
                    'attribute_id'              => 0,
                    'attribute_title'           => $attributeTitle,
                    'value_mode'                => (int)$post['custom_item_specifics_value_mode_' . $i],
                    'value_ebay_recommended'    => json_encode(array()),
                    'value_custom_value'        => $attributeValue,
                    'value_custom_attribute'    => $customAttribute
                );
            }
        }

        return $itemSpecifics;
    }

    //#############################################
}