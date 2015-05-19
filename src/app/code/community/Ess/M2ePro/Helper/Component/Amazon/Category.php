<?php

/*
 * @copyright Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Component_Amazon_Category extends Mage_Core_Helper_Abstract
{
    const RECENT_MAX_COUNT = 20;

    // ########################################

    public function getRecent($marketplaceId, array $excludedCategory = array())
    {
        $cacheValues = Mage::helper('M2ePro/Module')->getCacheConfig()->getAllGroupValues(
            $this->getConfigGroup($marketplaceId), Ess_M2ePro_Model_Config_Abstract::SORT_KEY_DESC
        );

        if (empty($cacheValues)) {
            return array();
        }

        $recentCategories = array();
        foreach ($cacheValues as $cacheKey => $cacheValue) {

            $categoryData = (array)json_decode($cacheValue, true);

            if (!isset($categoryData['product_data_nick'], $categoryData['browsenode_id'], $categoryData['path'])) {
                continue;
            }

            if (!empty($excludedCategory) &&
                $excludedCategory['product_data_nick'] == $categoryData['product_data_nick'] &&
                $excludedCategory['browsenode_id'] == $categoryData['browsenode_id'] &&
                $excludedCategory['path'] == $categoryData['path'])
            {
                continue;
            }

            $recentCategories[$cacheKey] = $categoryData;
        }

        // -- some categories can be not accessible in the current marketplaces build
        $this->removeNotAccessibleCategories($marketplaceId, $recentCategories);

        return $recentCategories;
    }

    public function addRecent($marketplaceId, $productDataNick, $browseNodeId, $categoryPath)
    {
        $cacheValues = Mage::helper('M2ePro/Module')->getCacheConfig()->getAllGroupValues(
            $this->getConfigGroup($marketplaceId), Ess_M2ePro_Model_Config_Abstract::SORT_KEY_DESC
        );

        foreach ($cacheValues as $cacheKey => &$cacheValue) {

            $cacheValue = (array)json_decode($cacheValue, true);

            if (!isset($cacheValue['product_data_nick'], $cacheValue['browsenode_id'], $cacheValue['path'])) {
                continue;
            }

            if ($cacheValue['product_data_nick'] == $productDataNick &&
                $cacheValue['browsenode_id'] == $browseNodeId &&
                $cacheValue['path'] == $categoryPath) {
                return;
            }
        }

        // -- remove oldest value
        if (count($cacheValues) >= self::RECENT_MAX_COUNT) {

            end($cacheValues);
            $oldestKey = key($cacheValues);

            Mage::helper('M2ePro/Module')->getCacheConfig()->deleteGroupValue(
                $this->getConfigGroup($marketplaceId), $oldestKey
            );
        }
        // --

        $categoryInfo = json_encode(array(
            'product_data_nick' => $productDataNick,
            'browsenode_id'     => $browseNodeId,
            'path'              => $categoryPath
        ));

        reset($cacheValues);
        $newestKey = key($cacheValues);

        Mage::helper('M2ePro/Module')->getCacheConfig()->setGroupValue(
            $this->getConfigGroup($marketplaceId), $newestKey + 1, $categoryInfo
        );
    }

    // ########################################

    private function getConfigGroup($marketplaceId)
    {
        return "/amazon/category/recent/marketplace/{$marketplaceId}";
    }

    private function removeNotAccessibleCategories($marketplaceId, array &$recentCategories)
    {
        if (empty($recentCategories)) {
            return;
        }

        $nodeIdsForCheck = array();
        foreach ($recentCategories as $categoryData) {
            $nodeIdsForCheck[] = $categoryData['browsenode_id'];
        }

        $select = Mage::getSingleton('core/resource')->getConnection('core_read')
            ->select()
            ->from(Mage::getSingleton('core/resource')->getTableName('m2epro_amazon_dictionary_category'))
            ->where('marketplace_id = ?', $marketplaceId)
            ->where('browsenode_id IN (?)', array_unique($nodeIdsForCheck));

        $queryStmt = $select->query();
        $tempCategories = array();

        while ($row = $queryStmt->fetch()) {
            $key = $row['product_data_nick'] .'##'. $row['browsenode_id'] .'##'. $row['path'].'>'.$row['title'];
            $tempCategories[$key] = $row;
        }

        foreach ($recentCategories as $categoryKey => &$categoryData) {

            $categoryPath = str_replace(' > ', '>', $categoryData['path']);
            $key = $categoryData['product_data_nick'] .'##'. $categoryData['browsenode_id'] .'##'. $categoryPath;

            if (!array_key_exists($key, $tempCategories)) {

                Mage::helper('M2ePro/Module')->getCacheConfig()->deleteGroupValue(
                    $this->getConfigGroup($marketplaceId), $categoryKey
                );
                unset($recentCategories[$categoryKey]);
            }
        }
    }

    // ########################################
}