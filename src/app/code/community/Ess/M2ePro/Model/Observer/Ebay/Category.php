<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Ebay_Category
{
    private $cacheLoadedListings = array();
    private $cacheAutoCategoriesByCategoryId = array();

    //####################################

    public function synchProductWithAddedCategoryId($product, $categoryId, $websiteId)
    {
        /** @var Ess_M2ePro_Model_Observer_Ebay_Product $ebayObserver */
        $ebayObserver = Mage::getModel('M2ePro/Observer_Ebay_Product');

        $autoCategories = $this->getAutoCategoriesByCategory($categoryId);

        foreach ($autoCategories as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Ebay_Listing_Auto_Category */

            if ($autoCategory->isAddingModeNone()) {
                continue;
            }

            /** @var $listing Ess_M2ePro_Model_Listing */
            $listing = $this->getLoadedListing($autoCategory->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if (!$listing->getChildObject()->isAutoModeCategory()) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::helper('M2ePro/Magento_Product')->getCachedAndLoadedProduct($product);

            $ebayObserver->addProductToListing($listing,$product,
                                               $autoCategory->getAddingTemplateCategoryId(),
                                               $autoCategory->getAddingTemplateOtherCategoryId());
        }
    }

    public function synchProductWithDeletedCategoryId($product, $categoryId, $websiteId)
    {
        /** @var Ess_M2ePro_Model_Observer_Ebay_Product $ebayObserver */
        $ebayObserver = Mage::getModel('M2ePro/Observer_Ebay_Product');

        $autoCategories = $this->getAutoCategoriesByCategory($categoryId);

        foreach ($autoCategories as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Ebay_Listing_Auto_Category */

            if ($autoCategory->isDeletingModeNone()) {
                continue;
            }

            /** @var $listing Ess_M2ePro_Model_Listing */
            $listing = $this->getLoadedListing($autoCategory->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if (!$listing->getChildObject()->isAutoModeCategory()) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::helper('M2ePro/Magento_Product')->getCachedAndLoadedProduct($product);

            $ebayObserver->deleteProductFromListing($listing,$product,
                                                    $autoCategory->getDeletingMode());
        }
    }

    //####################################

    private function getLoadedListing($listing)
    {
        if ($listing instanceof Ess_M2ePro_Model_Listing) {
            return $listing;
        }

        $listingId = (int)$listing;

        if (isset($this->cacheLoadedListings[$listingId])) {
            return $this->cacheLoadedListings[$listingId];
        }

        /** @var $listing Ess_M2ePro_Model_Listing */
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing',$listingId);

        /** @var $listingStoreObject Mage_Core_Model_Store */
        $listingStoreObject = Mage::getModel('core/store')->load($listing->getStoreId());
        $listing->setData('store_website_id',$listingStoreObject->getWebsite()->getId());

        return $this->cacheLoadedListings[$listingId] = $listing;
    }

    private function getAutoCategoriesByCategory($categoryId)
    {
        if (isset($this->cacheAutoCategoriesByCategoryId[$categoryId])) {
            return $this->cacheAutoCategoriesByCategoryId[$categoryId];
        }

        return $this->cacheAutoCategoriesByCategoryId[$categoryId] =
                                Mage::getModel('M2ePro/Ebay_Listing_Auto_Category')
                                        ->getCollection()
                                        ->addFieldToFilter('category_id', $categoryId)
                                        ->getItems();
    }

    //####################################
}