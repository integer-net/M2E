<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Category_Ebay extends Mage_Core_Helper_Abstract
{
    const CACHE_TAG = '_ebay_dictionary_data_';

    // ########################################

    public function getPath($categoryId, $marketplaceId, $delimiter = ' > ')
    {
        $pathData = $this->getPathData($categoryId, $marketplaceId, 'title');
        return implode($delimiter, $pathData);
    }

    public function getTopLevel($categoryId, $marketplaceId, $dataField = 'category_id')
    {
        $pathCategoriesIds = $this->getPathData($categoryId, $marketplaceId, $dataField);
        return array_shift($pathCategoriesIds);
    }

    // ----------------------------------------

    public function isVariationEnabled($categoryId, $marketplaceId)
    {
        $features = $this->getFeatures($categoryId, $marketplaceId);
        return !empty($features['variation_enabled']);
    }

    public function hasRequiredSpecifics($categoryId, $marketplaceId)
    {
        $specifics = $this->getSpecifics($categoryId, $marketplaceId);

        if (empty($specifics)) {
            return false;
        }

        foreach ($specifics as $specific) {
            if ($specific['required']) {
                return true;
            }
        }

        return false;
    }

    // ########################################

    public function getFeatures($categoryId, $marketplaceId)
    {
        return array_merge($this->getMarketplaceFeatures($marketplaceId),
                           $this->getCategoryFeatures($categoryId, $marketplaceId));
    }

    // ########################################

    protected function getCategoryFeatures($categoryId, $marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache_Permanent');
        $cacheKey = '_ebay_category_features_'.$marketplaceId.'_'.$categoryId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        $pathCategoriesIds = $this->getPathData($categoryId, $marketplaceId, 'category_id');

        if (empty($pathCategoriesIds)) {
            $cacheHelper->setValue($cacheKey,array(),array(self::CACHE_TAG));
            return array();
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategory = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory, 'features')
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
        foreach ($connRead->fetchAll($dbSelect) as $rowCategory) {
            if (is_null($rowCategory['features'])) {
                continue;
            }
            $features = array_merge($features, (array)json_decode($rowCategory['features'], true));
        }

        $cacheHelper->setValue($cacheKey,$features,array(self::CACHE_TAG));

        return $features;
    }

    protected function getMarketplaceFeatures($marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache_Permanent');
        $cacheKey = '_ebay_marketplace_features_'.$marketplaceId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplaces = Mage::getSingleton('core/resource')->getTableName(
            'm2epro_ebay_dictionary_marketplace'
        );

        $dbSelect = $connRead->select()
                              ->from($tableDictMarketplaces,'categories_features_defaults')
                              ->where('`marketplace_id` = ?',(int)$marketplaceId);
        $features = $connRead->fetchRow($dbSelect);

        $features = (array)json_decode($features['categories_features_defaults'], true);
        $cacheHelper->setValue($cacheKey,$features,array(self::CACHE_TAG));

        return $features;
    }

    // ----------------------------------------

    protected function getPathData($categoryId, $marketplaceId, $dataField)
    {
        $pathData = array();

        for ($i = 1; $i < 10; $i++) {

            $category = Mage::helper('M2ePro/Component_Ebay')
                ->getCachedObject('Marketplace',(int)$marketplaceId)
                ->getChildObject()
                ->getCategory((int)$categoryId);

            if (!$category || ($i == 1 && !$category['is_leaf'])) {
                return array();
            }

            $pathData[] = $category[$dataField];

            if (!$category['parent_category_id']) {
                break;
            }

            $categoryId = (int)$category['parent_category_id'];
        }

        return array_reverse($pathData);
    }

    public function getSpecifics($categoryId, $marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache_Permanent');
        $cacheKey = '_ebay_category_item_specifics_'.$categoryId.'_'.$marketplaceId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategory = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $dbSelect = $connRead->select()
                             ->from($tableDictCategory,'*')
                             ->where('`marketplace_id` = ?', (int)$marketplaceId)
                             ->where('`category_id` = ?', (int)$categoryId);

        $categoryRow = $connRead->fetchAssoc($dbSelect);
        $categoryRow = array_shift($categoryRow);

        if (!$categoryRow || !$categoryRow['is_leaf']) {
            return false;
        }

        if (!is_null($categoryRow['item_specifics'])) {

            $specifics = (array)json_decode($categoryRow['item_specifics'],true);

        } else {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('marketplace','get','categorySpecifics',
                                                                   array('category_id' => $categoryId), 'specifics',
                                                                   $marketplaceId, NULL, NULL);

            $specifics = (array)$dispatcherObject->process($connectorObj);

            /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
            $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
            $connWrite->update($tableDictCategory,
                               array('item_specifics' => json_encode($specifics)),
                               array('marketplace_id = ?' => (int)$marketplaceId,
                                     'category_id = ?' => (int)$categoryId));
        }

        $cacheHelper->setValue($cacheKey,$specifics,array(self::CACHE_TAG));
        return $specifics;
    }

    // ########################################

    public function getSameTemplatesData($ids)
    {
        return Mage::helper('M2ePro/Component_Ebay_Category')->getSameTemplatesData(
            $ids, Mage::getResourceModel('M2ePro/Ebay_Template_Category')->getMainTable(),
            array('category_main')
        );
    }

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

    public function isExistDeletedCategories()
    {
        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $etcTable = Mage::getModel('M2ePro/Ebay_Template_Category')->getResource()->getMainTable();
        $etocTable = Mage::getModel('M2ePro/Ebay_Template_OtherCategory')->getResource()->getMainTable();
        $edcTable = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        // prepare category main select
        // -------------------------------------------
        $etcSelect = $connRead->select();
        $etcSelect->from(
                array('etc' => $etcTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_main_id as category_id',
                'marketplace_id',
            ))
            ->where('category_main_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'marketplace_id'));
        // -------------------------------------------

        // prepare category secondary select
        // -------------------------------------------
        $etocSelect = $connRead->select();
        $etocSelect->from(
                array('etc' => $etocTable)
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'category_secondary_id as category_id',
                'marketplace_id',
            ))
            ->where('category_secondary_mode = ?', Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY)
            ->group(array('category_id', 'marketplace_id'));
        // -------------------------------------------

        $unionSelect = $connRead->select();
        $unionSelect->union(array(
            $etcSelect,
            $etocSelect,
        ));

        $mainSelect = $connRead->select();
        $mainSelect->reset()
            ->from(array('main_table' => $unionSelect))
            ->joinLeft(
                array('edc' => $edcTable),
                'edc.marketplace_id = main_table.marketplace_id
                 AND edc.category_id = main_table.category_id'
            )
            ->where('edc.category_id IS NULL');

        return $connRead->query($mainSelect)->fetchColumn() !== false;
    }

    // ########################################
}