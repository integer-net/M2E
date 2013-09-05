<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Category extends Mage_Core_Helper_Abstract
{
    // ########################################

    const CATEGORY_TYPE_EBAY_MAIN = 0;
    const CATEGORY_TYPE_EBAY_SECONDARY = 1;
    const CATEGORY_TYPE_STORE_MAIN = 2;
    const CATEGORY_TYPE_STORE_SECONDARY = 3;

    const CATEGORY_TYPE_TAX = 4;

    // ########################################

    public function getEbayCategoryTypes()
    {
        return array(
            self::CATEGORY_TYPE_EBAY_MAIN,
            self::CATEGORY_TYPE_EBAY_SECONDARY
        );
    }

    public function getStoreCategoryTypes()
    {
        return array(
            self::CATEGORY_TYPE_STORE_MAIN,
            self::CATEGORY_TYPE_STORE_SECONDARY
        );
    }

    // ########################################

    public function getPathById($categoryId, $marketplaceId, $delimiter = ' -> ')
    {
        $titles = array();

        for ($i = 1; $i < 8; $i++) {

            $category = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',(int)$marketplaceId)
                ->getChildObject()
                ->getCategory((int)$categoryId);

            if (!$category || ($i == 1 && !$category['is_leaf'])) {
                return '';
            }

            $titles[] = $category['title'];

            if (!$category['parent_id']) {
                break;
            }

            $categoryId = (int)$category['parent_id'];
        }

        return implode($delimiter, array_reverse($titles));
    }

    public function getPathIdsById($categoryId, $marketplaceId)
    {
        $ids = array();

        for ($i = 1; $i < 8; $i++) {

            $category = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',(int)$marketplaceId)
                ->getChildObject()
                ->getCategory((int)$categoryId);

            if (!$category || ($i == 1 && !$category['is_leaf'])) {
                return array();
            }

            $ids[] = $category['category_id'];

            if (!$category['parent_id']) {
                break;
            }

            $categoryId = (int)$category['parent_id'];
        }

        return array_reverse($ids);
    }

    // ----------------------------------------

    public function getStorePathById($categoryId, $accountId, $delimiter = ' -> ')
    {
        $account = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Account', $accountId);
        $categories = $account->getChildObject()->getEbayStoreCategories();

        $path = array();

        while (true) {
            $currentCategory = NULL;
            foreach ($categories as $category) {
                if ($category['category_id'] == $categoryId) {
                    $currentCategory = $category;
                    break;
                }
            }

            if (is_null($currentCategory)) {
                break;
            }

            $path[] = $currentCategory['title'];

            if ($currentCategory['parent_id'] == 0) {
                break;
            }

            $categoryId = $currentCategory['parent_id'];
        }

        return implode($delimiter, array_reverse($path));
    }

    public function getTaxPathById($categoryId, $marketplaceId, $delimiter = ' -> ')
    {
        $marketplace = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Marketplace', $marketplaceId);
        $categories = $marketplace->getChildObject()->getTaxCategoryInfo();

        $path = array();

        while (true) {
            $currentCategory = NULL;
            foreach ($categories as $category) {
                if ($category['ebay_id'] == $categoryId) {
                    $currentCategory = $category;
                    break;
                }
            }

            if (is_null($currentCategory)) {
                break;
            }

            $path[] = $currentCategory['title'];

            if (is_null($currentCategory['parent'])) {
                break;
            }

            $categoryId = $currentCategory['parent'];
        }

        return implode($delimiter, array_reverse($path));
    }

    // ########################################

    public function getRecent($marketplaceOrAccountId, $categoryType)
    {
        $configPath = $this->getRecentConfigPath($categoryType);
        $cacheValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            $configPath, $marketplaceOrAccountId
        );

        if (empty($cacheValue)) {
            return array();
        }

        return json_decode($cacheValue, true);
    }

    public function addRecent($categoryId, $marketplaceOrAccountId, $categoryType, $path)
    {
        $configPath = $this->getRecentConfigPath($categoryType);
        $cacheValue = Mage::helper('M2ePro/Module')->getCacheConfig()->getGroupValue(
            $configPath, $marketplaceOrAccountId
        );

        $categories = array();
        if (!empty($cacheValue)) {
            $categories = json_decode($cacheValue, true);
        }

        if (count($categories) >= 100) {
            array_shift($categories);
        }

        $categories[$categoryId] = $path;
        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            $configPath, $marketplaceOrAccountId, json_encode($categories)
        );
    }

    // ########################################

    public function getMarketplaceDefaultsCategoryFeatures($marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplaces = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
            ->from($tableDictMarketplaces,'categories_features_defaults')
            ->where('`marketplace_id` = ?',(int)$marketplaceId);
        $featuresDefaults = $connRead->fetchRow($dbSelect);

        return json_decode($featuresDefaults['categories_features_defaults'], true);
    }

    public function getCategoryFeatures($categoryId, $marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $pathCategoriesIds = $this->getPathIdsById($categoryId, $marketplaceId);

        $dbSelect = $connRead->select()
            ->from($tableDictCategories, 'features')
            ->where('`marketplace_id` = ?',(int)$marketplaceId);

        $sqlClauseCategories = '';
        foreach ($pathCategoriesIds as $categoryId) {
            if ($sqlClauseCategories != '') {
                $sqlClauseCategories .= ' OR ';
            }
            $sqlClauseCategories .= ' `category_id` = '.(int)$categoryId;
        }

        $dbSelect->where('('.$sqlClauseCategories.')')->order(array('level ASC'));

        $features = array();
        foreach ($connRead->fetchAll($dbSelect) as $rowFeatures) {
            if (is_null($rowFeatures['features'])) {
                continue;
            }

            $features = array_merge($features, (array)json_decode($rowFeatures['features'], true));
        }

        return $features;
    }

    public function getCategorySpecifics($categoryId, $marketplaceId, $categoryFeatures = null)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
            ->from($tableDictCategories,'*')
            ->where('`marketplace_id` = ?', (int)$marketplaceId)
            ->where('`category_id` = ?', (int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        if (is_null($categoryRow) || $categoryRow['is_leaf'] != 1) {
            return false;
        }

        $specifics = array(
            'mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ITEM_SPECIFICS,
            'mode_relation_id' => (int)$categoryId
        );

        if (!is_null($categoryRow['item_specifics'])) {
            $specifics['specifics'] = json_decode($categoryRow['item_specifics'],true);
        } else {
            $specifics['specifics'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                ->processVirtualAbstract(
                    'marketplace','get','categorySpecifics',
                    array('category_id'=>$categoryRow['category_id']),'specifics',
                    $categoryRow['marketplace_id'],NULL,NULL
                );

            if (!is_null($specifics)) {

                $tempData = array(
                    'marketplace_id' => (int)$categoryRow['marketplace_id'],
                    'category_id' => (int)$categoryRow['category_id'],
                    'item_specifics' => json_encode($specifics['specifics'])
                );

                /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                $connWrite->insertOnDuplicate($tableDictCategories, $tempData);

            } else {
                $specifics['specifics'] = array();
            }
        }

        if (empty($specifics['specifics'])) {

            if (!is_array($categoryFeatures)) {
                $defaultFeatures = $this->getMarketplaceDefaultsCategoryFeatures($marketplaceId);
                $features = $this->getCategoryFeatures($categoryId, $marketplaceId);

                $categoryFeatures = array_merge($defaultFeatures, $features);
            }

            if (!isset($categoryFeatures['attribute_conversion_enabled'])
                || !(bool)$categoryFeatures['attribute_conversion_enabled']) {

                return array();
            }

            $specifics = array(
                'mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET,
                'mode_relation_id' => (int)$categoryRow['attribute_set_id']
            );

            if (!is_null($categoryRow['attribute_set'])) {
                $specifics['specifics'] = json_decode($categoryRow['attribute_set'],true);
            } else {
                $specifics['specifics'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
                    ->processVirtualAbstract(
                        'marketplace','get','attributesCS',
                        array('attribute_set_id'=>(int)$categoryRow['attribute_set_id']),'specifics',
                        $categoryRow['marketplace_id'],NULL,NULL
                    );

                if (!is_null($specifics['specifics'])) {

                    $tempData = array(
                        'marketplace_id' => (int)$categoryRow['marketplace_id'],
                        'category_id' => (int)$categoryRow['category_id'],
                        'attribute_set' => json_encode($specifics['specifics'])
                    );

                    /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                    $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connWrite->insertOnDuplicate($tableDictCategories, $tempData);

                } else {
                    $specifics['specifics'] = array();
                }
            }
        }

        return $specifics;
    }

    public function getFullCategoryData($categoryId, $marketplaceId)
    {
        $defaultFeatures = $this->getMarketplaceDefaultsCategoryFeatures($marketplaceId);

        if (!$categoryId) {
            return $defaultFeatures;
        }

        $categoryFeatures = $this->getCategoryFeatures($categoryId, $marketplaceId);
        $resultData = array_merge($defaultFeatures, $categoryFeatures);

        $resultData['item_specifics'] = $this->getCategorySpecifics($categoryId, $marketplaceId, $resultData);

        return $resultData;
    }

    // ########################################

    public function isVariationEnabledByCategoryId($categoryId, $marketplaceId)
    {
        $features = $this->getCategoryFeatures($categoryId, $marketplaceId);
        if (isset($features['variation_enabled'])) {
            return (bool)$features['variation_enabled'];
        }

        $featuresDefaults = $this->getMarketplaceDefaultsCategoryFeatures($marketplaceId);
        if (isset($featuresDefaults['variation_enabled'])) {
            return (bool)$featuresDefaults['variation_enabled'];
        }

        return false;
    }

    public function isCategoryHasRequiredSpecifics($categoryId, $marketplaceId)
    {
        $features = $this->getCategoryFeatures($categoryId, $marketplaceId);
        if (isset($features['item_specifics_enabled']) && $features['item_specifics_enabled'] <= 0) {
            return false;
        }

        $featuresDefaults = $this->getMarketplaceDefaultsCategoryFeatures($marketplaceId);
        if (isset($featuresDefaults['item_specifics_enabled']) && $featuresDefaults['item_specifics_enabled'] <= 0) {
            return false;
        }

        $fullFeatures = array_merge($featuresDefaults, $features);
        $specifics = $this->getCategorySpecifics($categoryId, $marketplaceId, $fullFeatures);
        if (empty($specifics)) {
            return false;
        }

        foreach ($specifics['specifics'] as $specific) {
            if ($specific['required']) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    public function exists($categoryId, $marketplaceId)
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
            ->from($tableDictCategories, 'COUNT(*)')
            ->where('`marketplace_id` = ?', (int)$marketplaceId)
            ->where('`category_id` = ?', (int)$categoryId);

        return $dbSelect->query()->fetchColumn() == 1;
    }

    // ########################################

    protected function getRecentConfigPath($categoryType)
    {
        $configPaths = array(
            self::CATEGORY_TYPE_EBAY_MAIN => '/ebay/category/recent/ebay/main/',
            self::CATEGORY_TYPE_EBAY_SECONDARY => '/ebay/category/recent/ebay/secondary/',
            self::CATEGORY_TYPE_STORE_MAIN => '/ebay/category/recent/store/main/',
            self::CATEGORY_TYPE_STORE_SECONDARY => 'ebay/category/recent/store/secondary/',
        );

        return $configPaths[$categoryType];
    }

    // ########################################
}