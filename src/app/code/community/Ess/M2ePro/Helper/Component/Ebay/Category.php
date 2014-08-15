<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Category extends Mage_Core_Helper_Abstract
{
    const TYPE_EBAY_MAIN = 0;
    const TYPE_EBAY_SECONDARY = 1;
    const TYPE_STORE_MAIN = 2;
    const TYPE_STORE_SECONDARY = 3;

    const RECENT_MAX_COUNT = 20;

    // ########################################

    public function getEbayCategoryTypes()
    {
        return array(
            self::TYPE_EBAY_MAIN,
            self::TYPE_EBAY_SECONDARY
        );
    }

    public function getStoreCategoryTypes()
    {
        return array(
            self::TYPE_STORE_MAIN,
            self::TYPE_STORE_SECONDARY
        );
    }

    // ########################################

    public function getRecent($marketplaceOrAccountId, $categoryType, $excludeCategory = null)
    {
        $configPath = $this->getRecentConfigPath($categoryType);
        $cacheValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            $configPath, $marketplaceOrAccountId
        );

        if (empty($cacheValue)) {
            return array();
        }

        if (in_array($categoryType, $this->getEbayCategoryTypes())) {
            $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Ebay');
        } else {
            $categoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Store');
        }

        $categoryIds = (array)explode(',', $cacheValue);
        $result = array();
        foreach ($categoryIds as $categoryId) {
            if ($categoryId === $excludeCategory) {
                continue;
            }

            $path = $categoryHelper->getPath($categoryId, $marketplaceOrAccountId);
            if (empty($path)) {
                continue;
            }

            $result[] = array(
                'id' => $categoryId,
                'path' => $path . ' (' . $categoryId . ')',
            );
        }

        return $result;
    }

    public function addRecent($categoryId, $marketplaceOrAccountId, $categoryType)
    {
        $configPath = $this->getRecentConfigPath($categoryType);
        $cacheValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            $configPath, $marketplaceOrAccountId
        );

        $categories = array();
        if (!empty($cacheValue)) {
            $categories = (array)explode(',', $cacheValue);
        }

        if (count($categories) >= self::RECENT_MAX_COUNT) {
            array_pop($categories);
        }

        array_unshift($categories, $categoryId);
        $categories = array_unique($categories);

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            $configPath, $marketplaceOrAccountId, implode(',', $categories)
        );
    }

    //-----------------------------------------

    protected function getRecentConfigPath($categoryType)
    {
        $configPaths = array(
            self::TYPE_EBAY_MAIN => '/ebay/category/recent/ebay/main/',
            self::TYPE_EBAY_SECONDARY => '/ebay/category/recent/ebay/secondary/',
            self::TYPE_STORE_MAIN => '/ebay/category/recent/store/main/',
            self::TYPE_STORE_SECONDARY => 'ebay/category/recent/store/secondary/',
        );

        return $configPaths[$categoryType];
    }

    // ########################################

    public function getSameTemplatesData($ids, $table, $modes)
    {
        $fields = array();

        foreach ($modes as $mode) {
            $fields[] = $mode.'_id';
            $fields[] = $mode.'_path';
            $fields[] = $mode.'_mode';
            $fields[] = $mode.'_attribute';
        }

        $select = Mage::getSingleton('core/resource')->getConnection('core_read')->select();
        $select->from($table, $fields);
        $select->where('id IN (?)', $ids);

        $templatesData = $select->query()->fetchAll(PDO::FETCH_ASSOC);

        $resultData = reset($templatesData);

        if (!$resultData) {
            return array();
        }

        foreach ($modes as $i => $mode) {

            if (!Mage::helper('M2ePro')->theSameItemsInData($templatesData, array_slice($fields,$i*4,4))) {
                $resultData[$mode.'_id'] = 0;
                $resultData[$mode.'_path'] = NULL;
                $resultData[$mode.'_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
                $resultData[$mode.'_attribute'] = NULL;
                $resultData[$mode.'_message'] = Mage::helper('M2ePro')->__(
                    'Please, specify a value suitable for all chosen products.'
                );
            }
        }

        return $resultData;
    }

    public function fillCategoriesPaths(array &$data, Ess_M2ePro_Model_Listing $listing)
    {
        $ebayCategoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Ebay');
        $ebayStoreCategoryHelper = Mage::helper('M2ePro/Component_Ebay_Category_Store');

        $temp = array(
            'category_main'            => array('call' => array($ebayCategoryHelper,'getPath'),
                                                'arg'  => $listing->getMarketplaceId()),
            'category_secondary'       => array('call' => array($ebayCategoryHelper,'getPath'),
                                                'arg'  => $listing->getMarketplaceId()),
            'store_category_main'      => array('call' => array($ebayStoreCategoryHelper,'getPath'),
                                                'arg'  => $listing->getAccountId()),
            'store_category_secondary' => array('call' => array($ebayStoreCategoryHelper,'getPath'),
                                                'arg'  => $listing->getAccountId()),
        );

        foreach ($temp as $key => $value) {

            if (!isset($data[$key.'_mode']) || !empty($data[$key.'_path'])) {
                continue;
            }

            if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data[$key.'_path'] = call_user_func($value['call'], $data[$key.'_id'], $value['arg']);
            }

            if ($data[$key.'_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $attributeLabel = Mage::helper('M2ePro/Magento_Attribute')->getAttributeLabel(
                    $data[$key.'_attribute'], $listing->getStoreId()
                );
                $data[$key.'_path'] = 'Magento Attribute' . ' -> ' . $attributeLabel;
            }
        }
    }

    // ########################################
}