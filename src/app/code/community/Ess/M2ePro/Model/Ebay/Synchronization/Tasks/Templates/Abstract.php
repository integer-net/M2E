<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Templates_Abstract
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    //####################################

    static private $listingProductsCache = array();
    static private $listingProductsByParamsCache = array();

    //####################################

    protected function getChangedInstances(array $attributes,
                                           $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItems');
    }

    protected function getChangedInstancesByListingProduct(array $attributes,
                                                           $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItemsByListingProduct');
    }

    protected function getChangedInstancesByVariationOption(array $attributes,
                                                            $withStoreFilter = false)
    {
        return $this->getListingProducts($attributes,
                                         $withStoreFilter,
                                         'getChangedItemsByVariationOption');
    }

    //####################################

    private function getListingProducts(array $attributes,
                                        $withStoreFilter = false,
                                        $fetchFunction)
    {

        $cacheKey = md5(json_encode(func_get_args()));

        if (isset(self::$listingProductsByParamsCache[$cacheKey])) {
            return self::$listingProductsByParamsCache[$cacheKey];
        }

        $changedListingsProducts = Mage::getModel('M2ePro/Listing_Product')->$fetchFunction(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );

        self::$listingProductsByParamsCache[$cacheKey] = array();

        $listingProductsIds = array();
        $resultListingProducts = array();

        foreach ($changedListingsProducts as $key => $listingProductData) {

            $lpId = $listingProductData['id'];

            if (!isset(self::$listingProductsCache[$lpId])) {
                $listingProductsIds[$key] = $lpId;
                continue;
            }

            $resultListingProducts[$lpId] = self::$listingProductsCache[$lpId];
            $resultListingProducts[$lpId]->addData($listingProductData);
            $resultListingProducts[$lpId]->enableCache();

            self::$listingProductsByParamsCache[$cacheKey][$lpId] = $resultListingProducts[$lpId];

            unset($changedListingsProducts[$key]);
        }

        if (empty($changedListingsProducts)) {
            return self::$listingProductsByParamsCache[$cacheKey] = $resultListingProducts;
        }

        $listingProducts = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('listing_product_id',array('in' => $listingProductsIds))
            ->getItems();

        foreach ($listingProductsIds as $key => $lpId) {
            $listingProducts[$lpId]->addData($changedListingsProducts[$key]);
            $listingProducts[$lpId]->enableCache();

            self::$listingProductsCache[$lpId] = $listingProducts[$lpId];
            self::$listingProductsByParamsCache[$cacheKey][$lpId] = $listingProducts[$lpId];
        }

        return self::$listingProductsByParamsCache[$cacheKey];
    }

    //####################################

    public static function clearCache()
    {
        return self::$listingProductsCache = self::$listingProductsByParamsCache = array();
    }

    //####################################
}