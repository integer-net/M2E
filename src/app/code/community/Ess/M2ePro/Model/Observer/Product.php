<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product
{
    private $_isFailedDuringUpdate = false;
    private $affectedStoreIdAttributeCache = array();

    private $_productId = 0;
    private $_storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
    private $_isJustAddedProduct = false;

    private $_productNameOld = '';
    private $_productWebsiteIdsOld = array();
    private $_productCategoryIdsOld = array();

    private $_productStatusOld = NULL;
    private $_productPriceOld = 0;
    private $_productSpecialPriceOld = 0;
    private $_productSpecialPriceFromDate = NULL;
    private $_productSpecialPriceToDate = NULL;
    private $_productCustomAttributes = array();

    private $_listingsProductsArray = array();
    private $_otherListingsArray = array();

    //####################################

    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        try {

            $productOld = $observer->getEvent()->getProduct();

            if (!($productOld instanceof Mage_Catalog_Model_Product)) {
                $this->_isFailedDuringUpdate = true;
                return;
            }

            $this->_productId = (int)$productOld->getId();
            $this->_storeId = (int)$productOld->getData('store_id');

            if ($this->_productId <= 0) {
                return;
            }

            $productOld = Mage::getModel('catalog/product')
                                    ->setStoreId($this->_storeId)
                                    ->load($this->_productId);

            $this->_productNameOld = $productOld->getName();
            $this->_productWebsiteIdsOld = $productOld->getWebsiteIds();
            $this->_productCategoryIdsOld = $productOld->getCategoryIds();

            $this->_listingsProductsArray = Mage::getResourceModel('M2ePro/Listing_Product')
                                              ->getItemsByProductId($this->_productId);
            $this->_otherListingsArray = Mage::getResourceModel('M2ePro/Listing_Other')
                                            ->getItemsByProductId($this->_productId);

            if (count($this->_listingsProductsArray) > 0 || count($this->_otherListingsArray) > 0) {

                $this->_productStatusOld = (int)$productOld->getStatus();
                $this->_productPriceOld = (float)$productOld->getPrice();
                $this->_productSpecialPriceOld = (float)$productOld->getSpecialPrice();
                $this->_productSpecialPriceFromDate = $productOld->getSpecialFromDate();
                $this->_productSpecialPriceToDate = $productOld->getSpecialToDate();

                //--------------------
                $this->_productCustomAttributes = $this->getCustomAttributes($this->_listingsProductsArray,
                                                                             $this->_otherListingsArray);

                /** @var $magentoProductModel Ess_M2ePro_Model_Magento_Product */
                $magentoProductModel = Mage::getModel('M2ePro/Magento_Product')->setProduct($productOld);

                foreach ($this->_productCustomAttributes as &$attribute) {
                    $attribute['value_old'] = $magentoProductModel->getAttributeValue($attribute['attribute']);
                }
                //--------------------
            }

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        if ($this->_isFailedDuringUpdate) {
            return;
        }

        try {

            $productNew = $observer->getEvent()->getProduct();

            if (!($productNew instanceof Mage_Catalog_Model_Product) ||
                ($this->_productId > 0 && (int)$productNew->getId() != $this->_productId) ||
                (int)$productNew->getData('store_id') != $this->_storeId) {
                return;
            }

            $this->_isJustAddedProduct = ($this->_productId <= 0);
            $this->_productId = (int)$productNew->getId();

            $productNew = Mage::helper('M2ePro/Magento_Product')
                                ->getCachedAndLoadedProduct($this->_productId,$this->_storeId);

            if (count($this->_listingsProductsArray) > 0 || count($this->_otherListingsArray) > 0) {

                Mage::getModel('M2ePro/ProductChange')
                                ->addUpdateAction( $this->_productId,
                                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );

                $this->tryToUpdateTheStatus($productNew);
                $this->tryToUpdateThePrice($productNew);
                $this->tryToUpdateTheSpecialPrice($productNew);
                $this->tryToUpdateTheSpecialPriceFromDate($productNew);
                $this->tryToUpdateTheSpecialPriceToDate($productNew);
                $this->tryToUpdateTheCustomAttributes($productNew);

                $this->updateListingsProductsVariations();
            }

            if (!$this->_isJustAddedProduct) {
                $this->tryToUpdateTheLogsNames($productNew);
            }

            $this->tryToPerformCategoriesActions($productNew);

            /** @var Ess_M2ePro_Model_Observer_Ebay_Product $ebayObserver */
            $ebayObserver = Mage::getModel('M2ePro/Observer_Ebay_Product');

            if ($this->_isJustAddedProduct) {
                $ebayObserver->tryToPerformGlobalProductActions($productNew);
            }

            $ebayObserver->tryToPerformWebsiteProductActions($productNew,
                                                             $this->_isJustAddedProduct,
                                                             $this->_productWebsiteIdsOld);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //------------------------------------

    public function catalogProductDeleteBefore(Varien_Event_Observer $observer)
    {
        try {

            $productDeleted = $observer->getEvent()->getProduct();

            if (!($productDeleted instanceof Mage_Catalog_Model_Product) ||
                (int)$productDeleted->getId() <= 0) {
                return;
            }

            Mage::getModel('M2ePro/Listing')->removeDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/Item')->removeDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct($productDeleted);

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return;
        }
    }

    //####################################

    private function tryToUpdateTheStatus($productNew)
    {
        $statusOld = (int)$this->_productStatusOld;
        $statusNew = (int)$productNew->getStatus();

        // M2ePro_TRANSLATIONS
        // Enabled
        // Disabled

        $statusOld = ($statusOld == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';
        $statusNew = ($statusNew == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';

        $changedStores = array();
        $attribute = 'status';

        foreach ($this->_listingsProductsArray as $listingProductArray) {

            if (!$this->isAffectChangedAttributeOnItemStoreId($attribute,$listingProductArray['store_id'])) {
                continue;
            }

            $rez = Mage::getModel('M2ePro/ProductChange')
                        ->updateAttribute($this->_productId, $attribute,
                                          $statusOld, $statusNew,
                                          Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                          $listingProductArray['store_id']);

            if ($rez === false || $statusOld == $statusNew) {
                continue;
            }

            $changedStores[$listingProductArray['store_id']] = true;

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingProductArray['component_mode']);
            $tempLog->addProductMessage(
                $listingProductArray['object']->getListingId(),
                $this->_productId,
                $listingProductArray['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STATUS,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%].

                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%].',
                    array('from'=>$statusOld,'to'=>$statusNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            if (!$this->isAffectChangedAttributeOnItemStoreId($attribute,$otherListingTemp['store_id'])) {
                continue;
            }

            if (!isset($changedStores[$otherListingTemp['store_id']])) {

                $rez = Mage::getModel('M2ePro/ProductChange')
                            ->updateAttribute($this->_productId, $attribute,
                                              $statusOld, $statusNew,
                                              Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                              $otherListingTemp['store_id']);

                if ($rez === false || $statusOld == $statusNew) {
                    continue;
                }
            }

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STATUS,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%].
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('from'=>$statusOld,'to'=>$statusNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }
    }

    private function tryToUpdateThePrice($productNew)
    {
        $priceOld = round((float)$this->_productPriceOld,2);
        $priceNew = round((float)$productNew->getPrice(),2);

        $rez = Mage::getModel('M2ePro/ProductChange')
                    ->updateAttribute($this->_productId, 'price',
                                      $priceOld, $priceNew,
                                      Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false || $priceOld == $priceNew) {
            return;
        }

        foreach ($this->_listingsProductsArray as $listingProductArray) {

             $tempLog = Mage::getModel('M2ePro/Listing_Log');
             $tempLog->setComponentMode($listingProductArray['component_mode']);
             $tempLog->addProductMessage(
                $listingProductArray['object']->getListingId(),
                $this->_productId,
                $listingProductArray['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_PRICE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$priceOld,'!to'=>$priceNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
             );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

             $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
             $tempLog->setComponentMode($otherListingTemp['component_mode']);
             $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_PRICE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$priceOld,'!to'=>$priceNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
             );
        }
    }

    private function tryToUpdateTheSpecialPrice($productNew)
    {
        $specialPriceOld = round((float)$this->_productSpecialPriceOld,2);
        $specialPriceNew = round((float)$productNew->getSpecialPrice(),2);

        $rez = Mage::getModel('M2ePro/ProductChange')
                    ->updateAttribute($this->_productId, 'special_price',
                                      $specialPriceOld, $specialPriceNew,
                                      Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false || $specialPriceOld == $specialPriceNew) {
            return;
        }

        foreach ($this->_listingsProductsArray as $listingProductArray) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingProductArray['component_mode']);
            $tempLog->addProductMessage(
                $listingProductArray['object']->getListingId(),
                $this->_productId,
                $listingProductArray['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$specialPriceOld,'!to'=>$specialPriceNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$specialPriceOld,'!to'=>$specialPriceNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }
    }

    private function tryToUpdateTheSpecialPriceFromDate($productNew)
    {
        $specialPriceFromDateOld = $this->_productSpecialPriceFromDate;
        $specialPriceFromDateNew = $productNew->getSpecialFromDate();

        $rez = Mage::getModel('M2ePro/ProductChange')
                    ->updateAttribute($this->_productId, 'special_price_from_date',
                                      $specialPriceFromDateOld, $specialPriceFromDateNew,
                                      Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false || $specialPriceFromDateOld == $specialPriceFromDateNew) {
            return;
        }

        if (is_null($specialPriceFromDateOld) ||
            $specialPriceFromDateOld === false ||
            $specialPriceFromDateOld == '') {
            $specialPriceFromDateOld = 'None';
        }

        if (is_null($specialPriceFromDateNew) ||
            $specialPriceFromDateNew === false ||
            $specialPriceFromDateNew == '') {
            $specialPriceFromDateNew = 'None';
        }

        foreach ($this->_listingsProductsArray as $listingProductArray) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingProductArray['component_mode']);
            $tempLog->addProductMessage(
                $listingProductArray['object']->getListingId(),
                $this->_productId,
                $listingProductArray['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                // None
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceFromDateOld,'!to'=>$specialPriceFromDateNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                // None
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceFromDateOld,'!to'=>$specialPriceFromDateNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }
    }

    private function tryToUpdateTheSpecialPriceToDate($productNew)
    {
        $specialPriceToDateOld = $this->_productSpecialPriceToDate;
        $specialPriceToDateNew = $productNew->getSpecialToDate();

        $rez = Mage::getModel('M2ePro/ProductChange')
                    ->updateAttribute($this->_productId, 'special_price_to_date',
                                      $specialPriceToDateOld, $specialPriceToDateNew,
                                      Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false || $specialPriceToDateOld == $specialPriceToDateNew) {
            return;
        }

        if (is_null($specialPriceToDateOld) ||
            $specialPriceToDateOld === false ||
            $specialPriceToDateOld == '') {
            $specialPriceToDateOld = 'None';
        }

        if (is_null($specialPriceToDateNew) ||
            $specialPriceToDateNew === false ||
            $specialPriceToDateNew == '') {
            $specialPriceToDateNew = 'None';
        }

        foreach ($this->_listingsProductsArray as $listingProductArray) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingProductArray['component_mode']);
            $tempLog->addProductMessage(
                $listingProductArray['object']->getListingId(),
                $this->_productId,
                $listingProductArray['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                // None;
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceToDateOld,'!to'=>$specialPriceToDateNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                // M2ePro_TRANSLATIONS
                // From [%from%] to [%to%]
                // None
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceToDateOld,'!to'=>$specialPriceToDateNew)
                ),
                Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
            );
        }
    }

    private function tryToUpdateTheCustomAttributes($productNew)
    {
        /** @var $magentoProductModel Ess_M2ePro_Model_Magento_Product */
        $magentoProductModel = Mage::getModel('M2ePro/Magento_Product')->setProduct($productNew);

        foreach ($this->_productCustomAttributes as $attribute) {

            $customAttributeOld = $attribute['value_old'];
            $customAttributeNew = $magentoProductModel->getAttributeValue($attribute['attribute']);

            $changedStores = array();

            if (isset($attribute['listings_products'])) {

                foreach ($attribute['listings_products'] as $listingProductArray) {

                    if (!$this->isAffectChangedAttributeOnItemStoreId($attribute['attribute'],
                                                                      $listingProductArray['store_id'])) {
                        continue;
                    }

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                ->updateAttribute($this->_productId, $attribute['attribute'],
                                                  $customAttributeOld, $customAttributeNew,
                                                  Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                                  $listingProductArray['store_id']);

                    if ($rez === false || $customAttributeOld == $customAttributeNew) {
                        continue;
                    }

                    $changedStores[$listingProductArray['store_id']] = true;

                    $tempLog = Mage::getModel('M2ePro/Listing_Log');
                    $tempLog->setComponentMode($listingProductArray['component_mode']);
                    $tempLog->addProductMessage(
                        $listingProductArray['object']->getListingId(),
                        $this->_productId,
                        $listingProductArray['id'],
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        NULL,
                        Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                        // M2ePro_TRANSLATIONS
                        // Attribute "%attr%" from [%from%] to [%to%]
                        Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                            'Attribute "%attr%" from [%from%] to [%to%].',
                            array(
                                '!attr'=>$attribute['attribute'],
                                '!from'=>$this->cutAttributeTitleLength($customAttributeOld),
                                '!to'=>$this->cutAttributeTitleLength($customAttributeNew)
                            )
                        ),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }
            }

            if (isset($attribute['other_listings'])) {

                foreach ($this->_otherListingsArray as $otherListingTemp) {

                    if (!$this->isAffectChangedAttributeOnItemStoreId($attribute['attribute'],
                                                                      $otherListingTemp['store_id'])) {
                        continue;
                    }

                    if (!isset($changedStores[$otherListingTemp['store_id']])) {

                        $rez = Mage::getModel('M2ePro/ProductChange')
                                    ->updateAttribute($this->_productId, $attribute['attribute'],
                                                      $customAttributeOld, $customAttributeNew,
                                                      Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                                      $otherListingTemp['store_id']);

                        if ($rez === false || $customAttributeOld == $customAttributeNew) {
                            continue;
                        }
                    }

                    $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                    $tempLog->setComponentMode($otherListingTemp['component_mode']);
                    $tempLog->addProductMessage(
                        $otherListingTemp['id'],
                        Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION,
                        NULL,
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                        // M2ePro_TRANSLATIONS
                        // Attribute "%attr%" from [%from%] to [%to%].
                        Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                            'Attribute "%attr%" from [%from%] to [%to%].',
                            array(
                                '!attr'=>$attribute['attribute'],
                                '!from'=>$this->cutAttributeTitleLength($customAttributeOld),
                                '!to'=>$this->cutAttributeTitleLength($customAttributeNew)
                            )
                        ),
                        Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE,
                        Ess_M2ePro_Model_Log_Abstract::PRIORITY_LOW
                    );
                }
            }
        }
    }

    //-----------------------------------

    private function tryToUpdateTheLogsNames($productNew)
    {
        if ($this->_storeId != Mage_Core_Model_App::ADMIN_STORE_ID) {
            return;
        }

        $nameNew = $productNew->getName();

        if ($this->_productNameOld == $nameNew) {
            return;
        }

        Mage::getModel('M2ePro/Listing_Log')->updateProductTitle($this->_productId,$nameNew);
    }

    private function tryToPerformCategoriesActions($productNew)
    {
        $categoryIdsNew = $productNew->getCategoryIds();

        $addedCategories = array_diff($categoryIdsNew,$this->_productCategoryIdsOld);
        $deletedCategories = array_diff($this->_productCategoryIdsOld,$categoryIdsNew);

        $websiteIdsNew  = $productNew->getWebsiteIds();

        $addedWebsites = array_diff($websiteIdsNew, $this->_productWebsiteIdsOld);
        $deletedWebsites = array_diff($this->_productWebsiteIdsOld, $websiteIdsNew);

        $websitesChanges = array(
            // website for default store view
            0 => array(
                'added' => $addedCategories,
                'deleted' => $deletedCategories
            )
        );

        foreach (Mage::app()->getWebsites() as $website) {

            $websiteId = (int)$website->getId();

            $websiteChanges = array(
                'added' => array(),
                'deleted' => array()
            );

            // website has been enabled
            if (in_array($websiteId,$addedWebsites)) {
                $websiteChanges['added'] = $categoryIdsNew;
            // website is enabled
            } else if (in_array($websiteId,$websiteIdsNew)) {
                $websiteChanges['added'] = $addedCategories;
            }

            // website has been disabled
            if (in_array($websiteId,$deletedWebsites)) {
                $websiteChanges['deleted'] = $this->_productCategoryIdsOld;
                // website is enabled
            } else if (in_array($websiteId,$websiteIdsNew)) {
                $websiteChanges['deleted'] = $deletedCategories;
            }

            $websitesChanges[$websiteId] = $websiteChanges;
        }

        /** @var Ess_M2ePro_Model_Observer_Category $categoryObserverModel */
        $categoryObserver = Mage::getModel('M2ePro/Observer_Category');

        /** @var Ess_M2ePro_Model_Observer_Ebay_Category $ebayCategoryObserver */
        $ebayCategoryObserver = Mage::getModel('M2ePro/Observer_Ebay_Category');

        foreach ($websitesChanges as $websiteId => $changes) {

            foreach ($changes['added'] as $categoryId) {
                $categoryObserver->synchProductWithAddedCategoryId($productNew,$categoryId,$websiteId);
                $ebayCategoryObserver->synchProductWithAddedCategoryId($productNew,$categoryId,$websiteId);
            }

            foreach ($changes['deleted'] as $categoryId) {
                $categoryObserver->synchProductWithDeletedCategoryId($productNew,$categoryId,$websiteId);
                $ebayCategoryObserver->synchProductWithDeletedCategoryId($productNew,$categoryId,$websiteId);
            }
        }
    }

    //####################################

    private function getCustomAttributes($listingsProductsArray,$otherListingsArray)
    {
        try {

            $attributesWithListingsProducts = $this->getCustomAttributesWithListingsProducts($listingsProductsArray);
            $attributesWithOtherListings = $this->getCustomAttributesWithOtherListings($otherListingsArray);

            foreach ($attributesWithOtherListings as $hash => $otherListingAttribute) {
                if (isset($attributesWithListingsProducts[$hash])) {
                    $attributesWithListingsProducts[$hash]['other_listings'] = $otherListingAttribute['other_listings'];
                } else {
                    $attributesWithListingsProducts[$hash] = $otherListingAttribute;
                }
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return array();
        }

        return array_values($attributesWithListingsProducts);
    }

    //-----------------------------------

    private function getCustomAttributesWithListingsProducts($listingsProductsArray)
    {
        $attributes = array();

        foreach ($listingsProductsArray as $listingProductArray) {

            $tempAttributes = $listingProductArray['object']->getTrackingAttributes();

            foreach ($tempAttributes as $attribute) {

                $hash = md5($attribute);

                if (!isset($attributes[$hash])) {
                    $attributes[$hash] = array(
                        'attribute' => $attribute,
                        'listings_products' => array($listingProductArray)
                    );
                } else {
                    $attributes[$hash]['listings_products'][] = $listingProductArray;
                }
            }
        }

        return $attributes;
    }

    private function getCustomAttributesWithOtherListings($otherListingsArray)
    {
        $attributes = array();

        if (count($otherListingsArray) <= 0) {
            return $attributes;
        }

        $tempOtherListingsAttributes = Mage::getModel('M2ePro/Ebay_Listing_Other_Source')
                                                ->getTrackingAttributes();

        foreach ($tempOtherListingsAttributes as $attribute) {
            $attributes[md5($attribute)] = array(
                'attribute' => $attribute,
                'other_listings' => $otherListingsArray
            );
        }

        return $attributes;
    }

    //####################################

    private function updateListingsProductsVariations()
    {
        foreach ($this->_listingsProductsArray as $listingProductArray) {
            $variationUpdaterModelPrefix = ucwords($listingProductArray['component_mode']).'_';
            Mage::getModel('M2ePro/'.$variationUpdaterModelPrefix.'Listing_Product_Variation_Updater')
                    ->updateVariations($listingProductArray['object']);
        }
    }

    private function cutAttributeTitleLength($attribute, $length = 150)
    {
        if (strlen($attribute) > $length) {
            return substr($attribute, 0, $length) . ' ...';
        }

        return $attribute;
    }

    //####################################

    private function isAffectChangedAttributeOnItemStoreId($attributeCode, $itemStoreId)
    {
        $cacheKey = $attributeCode.'_'.$itemStoreId;

        if (isset($this->affectedStoreIdAttributeCache[$cacheKey])) {
            return $this->affectedStoreIdAttributeCache[$cacheKey];
        }

        $attributeInstance = Mage::getModel('eav/config')->getAttribute('catalog_product',$attributeCode);

        if (!($attributeInstance instanceof Mage_Catalog_Model_Resource_Eav_Attribute)) {
            return $this->affectedStoreIdAttributeCache[$cacheKey] = false;
        }

        $storeIds = array();

        $attributeType = (int)$attributeInstance->getData('is_global');
        switch ($attributeType) {

            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL:

                if ($itemStoreId == Mage_Core_Model_App::ADMIN_STORE_ID) {
                    $storeIds = array($itemStoreId);
                } else {
                    foreach (Mage::app()->getWebsites() as $website) {
                        /** @var $website Mage_Core_Model_Website */
                        $storeIds = array_merge($storeIds,$website->getStoreIds());
                    }
                }

                break;

            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE:
            case Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE:

                if ($this->_storeId == Mage_Core_Model_App::ADMIN_STORE_ID) {

                    if ($itemStoreId == Mage_Core_Model_App::ADMIN_STORE_ID) {
                        $storeIds = array($itemStoreId);
                    } else {

                        /** @var Mage_Catalog_Model_Product $productTemp */
                        $productTemp = Mage::getModel('catalog/product')
                                            ->setStoreId($itemStoreId)
                                            ->load($this->_productId);

                        if ($productTemp->getAttributeDefaultValue($attributeCode) === false) {
                            $storeIds = array($itemStoreId);
                        }
                    }

                } else {

                    if ($attributeType == Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE) {
                        $storeIds = Mage::getModel('core/store')->load($this->_storeId)->getWebsite()->getStoreIds();
                    } else {
                        $storeIds = array($this->_storeId);
                    }
                }
                break;
        }

        $storeIds = array_values(array_unique($storeIds));
        foreach ($storeIds as &$storeIdTemp) {
            $storeIdTemp = (int)$storeIdTemp;
        }

        return $this->affectedStoreIdAttributeCache[$cacheKey] = in_array($itemStoreId,$storeIds);
    }

    //####################################
}