<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Category
{
    //####################################

    public function catalogCategoryChangeProducts(Varien_Event_Observer $observer)
    {
        try {

            $changedProductsIds = $observer->getData('product_ids');

            if (!is_array($changedProductsIds) || count($changedProductsIds) <= 0) {
                return;
            }

            $addedProducts = array();
            $deletedProducts = array();

            $postedProductsIds = array_keys($observer->getData('category')->getData('posted_products'));

            foreach ($changedProductsIds as $productId) {

                if (in_array($productId,$postedProductsIds)) {
                    $addedProducts[] = $productId;
                } else {
                    $deletedProducts[] = $productId;
                }
            }

            if (count($addedProducts) <= 0 && count($deletedProducts) <= 0) {
                return;
            }

            self::synchChangesWithListings($observer->getData('category')->getId(),
                                           $addedProducts, $deletedProducts);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return;
        }
    }

    //####################################

    public static function synchChangesWithListings($categoryId,
                                                    $addedProducts,
                                                    $deletedProducts)
    {
        try {

            // Check listings categories
            //---------------------------
            $listingsCategories = Mage::getModel('M2ePro/Listing_Category')
                                            ->getCollection()
                                            ->addFieldToFilter('category_id', $categoryId)
                                            ->toArray();

            if ($listingsCategories['totalRecords'] <= 0) {
                return;
            }

            $listingsIds = array();
            foreach ($listingsCategories['items'] as $listingCategory) {
                $listingsIds[] = (int)$listingCategory['listing_id'];
            }
            $listingsIds = array_unique($listingsIds);

            if (count($listingsIds) <= 0) {
                return;
            }

            $listingsModels = array();
            foreach ($listingsIds as $listingId) {

                /** @var $tempModel Ess_M2ePro_Model_Listing */
                $tempModel = Mage::getModel('M2ePro/Listing')->loadInstance($listingId);

                if (!$tempModel->isSourceCategories()) {
                    continue;
                }

                /** @var $listingStoreObject Mage_Core_Model_Store */
                $listingStoreObject = Mage::getModel('core/store')->load($tempModel->getStoreId());
                $tempModel->setData('store_website_id',$listingStoreObject->getWebsite()->getId());

                $listingsModels[] = $tempModel;
            }

            if (count($listingsModels) <= 0) {
                return;
            }
            //---------------------------

            // Add new products
            //---------------------------
            foreach ($addedProducts as $product) {

                if (!($product instanceof Mage_Catalog_Model_Product)) {
                    $product = Mage::getModel('catalog/product')->load((int)$product);
                }

                $productId = (int)$product->getId();

                if ((bool)Mage::helper('M2ePro/Module')->getConfig()
                        ->getGroupValue('/listings/categories_add_actions/', 'ignore_not_visible') &&
                    (int)$product->getVisibility() == Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE) {
                    continue;
                }

                foreach ($listingsModels as $listingModel) {

                    /** @var $listingModel Ess_M2ePro_Model_Listing */

                    if ((int)$listingModel->getData('store_website_id') > 0 &&
                        !in_array($listingModel->getData('store_website_id'),$product->getWebsiteIds())) {
                        continue;
                    }

                    // Cancel when auto add none set
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_ADD_ACTION_NONE) {
                        continue;
                    }
                    //------------------------------

                    // Only add product
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_ADD_ACTION_ADD) {

                        if ($listingModel->hasProduct($productId)) {
                            continue;
                        }

                        $listingModel->addProduct($product);
                    }
                    //------------------------------

                    // Add product and list
                    //------------------------------
                    if ($listingModel->getData('categories_add_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_ADD_ACTION_ADD_LIST) {

                        if ($listingModel->hasProduct($productId)) {
                            continue;
                        }

                        /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                        $listingProduct = $listingModel->addProduct($product);

                        if ($listingProduct instanceof Ess_M2ePro_Model_Listing_Product) {
                            $paramsTemp = array();
                            $paramsTemp['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_OBSERVER;
                            $listingProduct->isListable() && $listingProduct->listAction($paramsTemp);
                        }
                    }
                    //------------------------------
                }
            }
            //---------------------------

            // Delete products
            //---------------------------
            foreach ($deletedProducts as $product) {

                if (!($product instanceof Mage_Catalog_Model_Product)) {
                    $product = Mage::getModel('catalog/product')->load((int)$product);
                }

                $productId = (int)$product->getId();

                foreach ($listingsModels as $listingModel) {

                    /** @var $listingModel Ess_M2ePro_Model_Listing */

                    if ((int)$listingModel->getData('store_website_id') > 0 &&
                        !in_array($listingModel->getData('store_website_id'),$product->getWebsiteIds())) {
                        continue;
                    }

                    // Cancel when auto delete none set
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_DELETE_ACTION_NONE) {
                        continue;
                    }
                    //------------------------------

                    // Find needed product
                    //------------------------------
                    $listingsProducts = $listingModel->getProducts(true,array('product_id'=>$productId));

                    if (count($listingsProducts) <= 0) {
                        continue;
                    }

                    $listingProduct = array_shift($listingsProducts);

                    if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                        continue;
                    }
                    //------------------------------

                    // Only stop product
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_DELETE_ACTION_STOP) {
                        $paramsTemp = array();
                        $paramsTemp['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_OBSERVER;
                        $listingProduct->isStoppable() && $listingProduct->stopAction($paramsTemp);
                    }
                    //------------------------------

                    // Stop product on marketplace and remove
                    //------------------------------
                    if ($listingModel->getData('categories_delete_action') ==
                        Ess_M2ePro_Model_Listing::CATEGORIES_DELETE_ACTION_STOP_REMOVE) {
                        $paramsTemp = array();
                        $paramsTemp['remove'] = true;
                        $paramsTemp['status_changer'] = Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_OBSERVER;
                        $listingProduct->stopAction($paramsTemp);
                    }
                    //------------------------------
                }
            }
            //---------------------------

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return;
        }
    }

    //####################################
}