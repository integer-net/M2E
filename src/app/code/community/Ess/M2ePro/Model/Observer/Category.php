<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Category
{
    private $cacheLoadedListings = array();
    private $cacheAutoCategoriesByCategoryId = array();

    //####################################

    public function catalogCategoryChangeProducts(Varien_Event_Observer $observer)
    {
        try {

            /** @var Mage_Catalog_Model_Category $category */
            $category = $observer->getData('category');

            $categoryId = (int)$category->getId();
            $websiteId = (int)$category->getStore()->getWebsiteId();

            $changedProductsIds = $observer->getData('product_ids');
            $postedProductsIds = array_keys($observer->getData('category')->getData('posted_products'));

            if (!is_array($changedProductsIds) || count($changedProductsIds) <= 0) {
                return;
            }

            $websitesProductsIds = array(
                0 => $changedProductsIds // website for default store view
            );

            if ($websiteId == 0) {

                foreach ($changedProductsIds as $productId) {
                    $productModel = Mage::getModel('M2ePro/Magento_Product')->setProductId($productId);
                    foreach ($productModel->getWebsiteIds() as $websiteId) {
                        $websitesProductsIds[$websiteId][] = $productId;
                    }
                }

            } else {
                $websitesProductsIds[$websiteId] = $changedProductsIds;
            }

            /** @var Ess_M2ePro_Model_Observer_Ebay_Category $ebayCategoryObserver */
            $ebayCategoryObserver = Mage::getModel('M2ePro/Observer_Ebay_Category');

            foreach ($websitesProductsIds as $websiteId => $productIds) {
                foreach ($productIds as $productId) {

                    if (in_array($productId,$postedProductsIds)) {
                        $this->synchProductWithAddedCategoryId($productId,$categoryId,$websiteId);
                        $ebayCategoryObserver->synchProductWithAddedCategoryId($productId,$categoryId,$websiteId);
                    } else {
                        $this->synchProductWithDeletedCategoryId($productId,$categoryId,$websiteId);
                        $ebayCategoryObserver->synchProductWithDeletedCategoryId($productId,$categoryId,$websiteId);
                    }
                }
            }

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################

    public function synchProductWithAddedCategoryId($product, $categoryId, $websiteId)
    {
        $autoCategories = $this->getAutoCategoriesByCategory($categoryId);

        foreach ($autoCategories as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Listing_Category */

            /** @var $listing Ess_M2ePro_Model_Listing */
            $listing = $this->getLoadedListing($autoCategory->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if ($listing->isCategoriesAddActionNone()) {
                continue;
            }

            if (!$listing->isSourceCategories() || $listing->isComponentModeEbay()) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::helper('M2ePro/Magento_Product')->getCachedAndLoadedProduct($product);

            $listing->addProduct($product);
        }
    }

    public function synchProductWithDeletedCategoryId($product, $categoryId, $websiteId)
    {
        $autoCategories = $this->getAutoCategoriesByCategory($categoryId);

        foreach ($autoCategories as $autoCategory) {

            /** @var $autoCategory Ess_M2ePro_Model_Listing_Category */

            /** @var $listing Ess_M2ePro_Model_Listing */
            $listing = $this->getLoadedListing($autoCategory->getListingId());

            if ((int)$listing->getData('store_website_id') != $websiteId) {
                continue;
            }

            if ($listing->isCategoriesDeleteActionNone()) {
                continue;
            }

            if (!$listing->isSourceCategories() || $listing->isComponentModeEbay()) {
                continue;
            }

            /** @var Mage_Catalog_Model_Product $product */
            $product = Mage::helper('M2ePro/Magento_Product')->getCachedAndLoadedProduct($product);

            $listingsProducts = $listing->getProducts(true,array('product_id'=>(int)$product->getId()));

            if (count($listingsProducts) <= 0) {
                continue;
            }

            foreach ($listingsProducts as $listingProduct) {

                if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                    continue;
                }

                try {

                    if ($listing->isCategoriesDeleteActionStop()) {
                        $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                    }

                    if ($listing->isCategoriesDeleteActionStopRemove()) {
                        $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                        $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                        $listingProduct->deleteInstance();
                    }

                } catch (Exception $exception) {}
            }
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
        $listing = Mage::helper('M2ePro/Component')->getUnknownObject('Listing',$listingId);

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
                                Mage::getModel('M2ePro/Listing_Category')
                                        ->getCollection()
                                        ->addFieldToFilter('category_id', $categoryId)
                                        ->getItems();
    }

    //####################################
}