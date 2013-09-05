<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Abstract
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    //####################################

    static private $listingOtherProductsCache = array();
    static private $listingOtherProductsByParamsCache = array();

    //####################################

    protected function getChangedInstances(array $attributes,
                                           $withStoreFilter = false)
    {
        return $this->getListingOtherProducts($attributes,
                                              $withStoreFilter,
                                              'getChangedItems');
    }

    //####################################

    private function getListingOtherProducts(array $attributes,
                                             $withStoreFilter = false,
                                             $fetchFunction)
    {

        $cacheKey = md5(json_encode(func_get_args()));

        if (isset(self::$listingOtherProductsByParamsCache[$cacheKey])) {
            return self::$listingOtherProductsByParamsCache[$cacheKey];
        }

        $changedListingOtherProducts = Mage::getModel('M2ePro/Listing_Other')->$fetchFunction(
            $attributes,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            $withStoreFilter
        );

        self::$listingOtherProductsByParamsCache[$cacheKey] = array();

        $resultListingOtherProducts = array();
        $listingOtherProductsIds = array();

        foreach ($changedListingOtherProducts as $key => $listingOtherProductData) {

            $loId = $listingOtherProductData['id'];

            if (!isset(self::$listingOtherProductsCache[$loId])) {
                $listingOtherProductsIds[$key] = $loId;
                continue;
            }

            $resultListingOtherProducts[$loId] = self::$listingOtherProductsCache[$loId];
            $resultListingOtherProducts[$loId]->addData($listingOtherProductData);
            $resultListingOtherProducts[$loId]->enableCache();

            self::$listingOtherProductsByParamsCache[$cacheKey][$loId] = $resultListingOtherProducts[$loId];

            unset($changedListingOtherProducts[$key]);
        }

        if (empty($changedListingOtherProducts)) {
            return self::$listingOtherProductsByParamsCache[$cacheKey] = $resultListingOtherProducts;
        }

        $listingOtherProducts = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Other')
            ->addFieldToFilter('id',array('in' => $listingOtherProductsIds))
            ->getItems();

        foreach ($listingOtherProductsIds as $key => $loId) {
            $listingOtherProducts[$loId]->addData($changedListingOtherProducts[$key]);
            $listingOtherProducts[$loId]->enableCache();

            self::$listingOtherProductsCache[$loId] = $listingOtherProducts[$loId];
            self::$listingOtherProductsByParamsCache[$cacheKey][$loId] = $listingOtherProducts[$loId];
        }

        return self::$listingOtherProductsByParamsCache[$cacheKey];
    }

    //####################################

    public static function clearCache()
    {
        return self::$listingOtherProductsCache= self::$listingOtherProductsByParamsCache = array();
    }

    //####################################
}