<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Ebay_Product
{
    //####################################

    public function tryToPerformGlobalProductActions(Mage_Catalog_Model_Product $productNew)
    {
        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
             Ess_M2ePro_Helper_Component_Ebay::NICK,
            'Listing'
        );
        $collection->addFieldToFilter('auto_mode',Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_GLOBAL);
        $collection->addFieldToFilter('auto_global_adding_mode',array('neq'=>Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE));
        $listings = $collection->getItems();

        foreach ($listings as $listing) {
            /** @var Ess_M2ePro_Model_Listing $listing */
            $this->addProductToListing($listing, $productNew,
                                       $listing->getChildObject()->getAutoGlobalAddingTemplateCategoryId(),
                                       $listing->getChildObject()->getAutoGlobalAddingTemplateOtherCategoryId());
        }
    }

    public function tryToPerformWebsiteProductActions(Mage_Catalog_Model_Product $productNew,
                                                      $isJustAddedProduct, $websiteIdsOld)
    {
        $websiteIdsNew = $productNew->getWebsiteIds();

        if ($isJustAddedProduct) {
            $websiteIdsNew[] = 0;
        }

        $addedWebsiteIds = array_diff($websiteIdsNew,$websiteIdsOld);
        foreach ($addedWebsiteIds as $websiteId) {
            $this->synchProductWithAddedWebsiteId($productNew,$websiteId);
        }

        $deletedWebsiteIds = array_diff($websiteIdsOld,$websiteIdsNew);
        foreach ($deletedWebsiteIds as $websiteId) {
            $this->synchProductWithDeletedWebsiteId($productNew,$websiteId);
        }
    }

    //####################################

    private function synchProductWithAddedWebsiteId(Mage_Catalog_Model_Product $productNew, $websiteId)
    {
        if ($websiteId == 0) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        } else {
            /** @var $websiteObject Mage_Core_Model_Website */
            $websiteObject = Mage::getModel('core/website')->load((string)$websiteId);
            $storeIds = (array)$websiteObject->getStoreIds();
        }

        if (count($storeIds) <= 0) {
            return;
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
             Ess_M2ePro_Helper_Component_Ebay::NICK,
            'Listing'
        );

        $collection->addFieldToFilter('auto_mode',Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE);
        $collection->addFieldToFilter('auto_website_adding_mode',array('neq'=>Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_NONE));
        $collection->addFieldToFilter('store_id',array('in'=>$storeIds));

        $listings = $collection->getItems();

        foreach ($listings as $listing) {
            /** @var Ess_M2ePro_Model_Listing $listing */
            $this->addProductToListing($listing, $productNew,
                                       $listing->getChildObject()->getAutoWebsiteAddingTemplateCategoryId(),
                                       $listing->getChildObject()->getAutoWebsiteAddingTemplateOtherCategoryId());
        }
    }

    private function synchProductWithDeletedWebsiteId(Mage_Catalog_Model_Product $productNew, $websiteId)
    {
        /** @var $websiteObject Mage_Core_Model_Website */
        $websiteObject = Mage::getModel('core/website')->load((string)$websiteId);
        $storeIds = (array)$websiteObject->getStoreIds();

        if (count($storeIds) <= 0) {
            return;
        }

        /** @var Mage_Core_Model_Mysql4_Collection_Abstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
             Ess_M2ePro_Helper_Component_Ebay::NICK,
            'Listing'
        );

        $collection->addFieldToFilter('auto_mode',Ess_M2ePro_Model_Ebay_Listing::AUTO_MODE_WEBSITE);
        $collection->addFieldToFilter('auto_website_deleting_mode',
                                      array('neq'=>Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_NONE));

        $collection->addFieldToFilter('store_id',array('in'=>$storeIds));

        $listings = $collection->getItems();

        foreach ($listings as $listing) {
            /** @var Ess_M2ePro_Model_Listing $listing */
            $this->deleteProductFromListing($listing, $productNew,
                                            $listing->getChildObject()->getAutoWebsiteDeletingMode());
        }
    }

    //####################################

    public function addProductToListing(Ess_M2ePro_Model_Listing $listing,
                                        Mage_Catalog_Model_Product $productNew,
                                        $templateCategoryId, $templateOtherCategoryId)
    {
        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
        $listingProduct = $listing->addProduct($productNew);

        if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
            return;
        }

        if (is_null($templateCategoryId)) {
            return;
        }

        $listingProduct->setData('template_category_id',$templateCategoryId)
                       ->setData('template_other_category_id',$templateOtherCategoryId)
                       ->save();
    }

    public function deleteProductFromListing(Ess_M2ePro_Model_Listing $listing,
                                             Mage_Catalog_Model_Product $productNew,
                                             $deletingMode)
    {
        if ($deletingMode == Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_NONE) {
            return;
        }

        $listingsProducts = $listing->getProducts(true,array('product_id'=>(int)$productNew->getId()));

        if (count($listingsProducts) <= 0) {
            return;
        }

        foreach ($listingsProducts as $listingProduct) {

            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                return;
            }

            try {

                if ($deletingMode == Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_STOP) {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                }

                if ($deletingMode == Ess_M2ePro_Model_Ebay_Listing::DELETING_MODE_STOP_REMOVE) {
                    $listingProduct->isStoppable() && Mage::getModel('M2ePro/StopQueue')->add($listingProduct);
                    $listingProduct->addData(array('status'=>Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED))->save();
                    $listingProduct->deleteInstance();
                }

            } catch (Exception $exception) {}
        }
    }

    //####################################
}