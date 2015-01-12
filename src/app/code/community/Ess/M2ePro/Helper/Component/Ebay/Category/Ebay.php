<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Ebay_Category_Ebay extends Mage_Core_Helper_Abstract
{
    const CACHE_TAG = '_ebay_dictionary_data_';

    // ########################################

    public function getData($categoryId, $marketplaceId)
    {
        $defaultFeatures = $this->getMarketplaceDefaultFeatures($marketplaceId);

        if (!$categoryId) {
            return $defaultFeatures;
        }

        $categoryFeatures = $this->getFeatures($categoryId, $marketplaceId);
        $resultData = array_merge($defaultFeatures, $categoryFeatures);

        $resultData['item_specifics'] = $this->getSpecifics($categoryId, $marketplaceId, $resultData);

        return $resultData;
    }

    public function getPath($categoryId, $marketplaceId, $delimiter = ' -> ')
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
        if (isset($features['variation_enabled'])) {
            return (bool)$features['variation_enabled'];
        }

        $featuresDefaults = $this->getMarketplaceDefaultFeatures($marketplaceId);
        if (isset($featuresDefaults['variation_enabled'])) {
            return (bool)$featuresDefaults['variation_enabled'];
        }

        return false;
    }

    public function hasRequiredSpecifics($categoryId, $marketplaceId)
    {
        $features = $this->getFeatures($categoryId, $marketplaceId);
        if (isset($features['item_specifics_enabled']) && $features['item_specifics_enabled'] <= 0) {
            return false;
        }

        $featuresDefaults = $this->getMarketplaceDefaultFeatures($marketplaceId);
        if (isset($featuresDefaults['item_specifics_enabled']) && $featuresDefaults['item_specifics_enabled'] <= 0) {
            return false;
        }

        $fullFeatures = array_merge($featuresDefaults, $features);
        $specifics = $this->getSpecifics($categoryId, $marketplaceId, $fullFeatures);
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

    protected function getFeatures($categoryId, $marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache');
        $cacheKey = '_ebay_categories_features_'.$categoryId.'_'.$marketplaceId;

        if (($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictCategories = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_category');

        $pathCategoriesIds = $this->getPathData($categoryId, $marketplaceId, 'category_id');

        if (empty($pathCategoriesIds)) {
            $cacheHelper->setValue($cacheKey,array(),array(self::CACHE_TAG));
            return array();
        }

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

        $cacheHelper->setValue($cacheKey,$features,array(self::CACHE_TAG));

        return $features;
    }

    protected function getMarketplaceDefaultFeatures($marketplaceId)
    {
        $cacheHelper = Mage::helper('M2ePro/Data_Cache');
        $cacheKey = '_ebay_marketplaces_features_'.$marketplaceId;

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
        $featuresDefaults = $connRead->fetchRow($dbSelect);

        $features = json_decode($featuresDefaults['categories_features_defaults'], true);
        $cacheHelper->setValue($cacheKey,$features,array(self::CACHE_TAG));

        return $features;
    }

    // ----------------------------------------

    protected function getPathData($categoryId, $marketplaceId, $dataField)
    {
        $pathData = array();

        for ($i = 1; $i < 8; $i++) {

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

    public function getSpecifics($categoryId, $marketplaceId, $categoryFeatures = null)
    {
        $categoryFeaturesArg = $categoryFeatures;

        $cacheHelper = Mage::helper('M2ePro/Data_Cache');
        $cacheKey = '_ebay_category_marketplace_specifics_'.$categoryId.'_'.$marketplaceId;

        if ( $categoryFeaturesArg === null && ($cacheValue = $cacheHelper->getValue($cacheKey)) !== false) {
            return $cacheValue;
        }

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
            $specifics['specifics'] = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                ->processVirtual(
                    'marketplace','get','categorySpecifics',
                    array('category_id'=>$categoryRow['category_id']),'specifics',
                    $categoryRow['marketplace_id'],NULL,NULL
                );

            if (!is_null($specifics)) {

                /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                $connWrite->update($tableDictCategories,
                                   array('item_specifics' => json_encode($specifics['specifics'])),
                                   array('marketplace_id = ?' => (int)$categoryRow['marketplace_id'],
                                         'category_id = ?' => (int)$categoryRow['category_id'])
                                  );

            } else {
                $specifics['specifics'] = array();
            }
        }

        if (empty($specifics['specifics'])) {

            if (!is_array($categoryFeatures)) {
                $defaultFeatures = $this->getMarketplaceDefaultFeatures($marketplaceId);
                $features = $this->getFeatures($categoryId, $marketplaceId);
                $categoryFeatures = array_merge($defaultFeatures, $features);
            }

            if (!isset($categoryFeatures['attribute_conversion_enabled'])
                || !(bool)$categoryFeatures['attribute_conversion_enabled']) {

                $specifics = array();
                if($categoryFeaturesArg === null) {
                    $cacheHelper->setValue($cacheKey,$specifics,array(self::CACHE_TAG));
                }
                return $specifics;
            }

            $specifics = array(
                'mode' => Ess_M2ePro_Model_Ebay_Template_Category_Specific::MODE_ATTRIBUTE_SET,
                'mode_relation_id' => (int)$categoryRow['attribute_set_id']
            );

            if (!is_null($categoryRow['attribute_set'])) {
                $specifics['specifics'] = json_decode($categoryRow['attribute_set'],true);
            } else {
                $specifics['specifics'] = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
                    ->processVirtual(
                        'marketplace','get','attributesCS',
                        array('attribute_set_id'=>(int)$categoryRow['attribute_set_id']),'specifics',
                        $categoryRow['marketplace_id'],NULL,NULL
                    );

                if (!is_null($specifics['specifics'])) {

                    /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
                    $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connWrite->update($tableDictCategories,
                                       array('attribute_set' => json_encode($specifics['specifics'])),
                                       array('marketplace_id = ?' => (int)$categoryRow['marketplace_id'],
                                             'category_id = ?' => (int)$categoryRow['category_id'])
                                       );

                } else {
                    $specifics['specifics'] = array();
                }
            }
        }
        if($categoryFeaturesArg === null) {
            $cacheHelper->setValue($cacheKey,$specifics,array(self::CACHE_TAG));
        }
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