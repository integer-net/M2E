<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Product
{
    private $_productId = 0;
    private $_storeId = Mage_Core_Model_App::ADMIN_STORE_ID;

    private $_listingsArray = array();
    private $_otherListingsArray = array();

    private $_productNameOld = '';
    private $_productCategoriesOld = array();

    private $_productStatusOld = NULL;
    private $_productPriceOld = 0;
    private $_productSpecialPriceOld = 0;
    private $_productSpecialPriceFromDate = NULL;
    private $_productSpecialPriceToDate = NULL;
    private $_productCustomAttributes = array();

    //####################################

    public function catalogProductSaveBefore(Varien_Event_Observer $observer)
    {
        try {

            $productOld = $observer->getEvent()->getProduct();

            if (!($productOld instanceof Mage_Catalog_Model_Product)) {
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

            // Save preview name
            $this->_productNameOld = $productOld->getName();

            // Save preview categories
            $this->_productCategoriesOld = $productOld->getCategoryIds();

            // Get listings, other listings where is product
            $this->_listingsArray = Mage::getResourceModel('M2ePro/Listing')
                                        ->getListingsWhereIsProduct($this->_productId);
            $this->_otherListingsArray = Mage::getResourceModel('M2ePro/Listing_Other')
                                            ->getItemsWhereIsProduct($this->_productId);

            if (count($this->_listingsArray) > 0 || count($this->_otherListingsArray) > 0) {

                // Save preview status
                $this->_productStatusOld = (int)$productOld->getStatus();

                // Save preview prices
                //--------------------
                $this->_productPriceOld = (float)$productOld->getPrice();
                $this->_productSpecialPriceOld = (float)$productOld->getSpecialPrice();
                $this->_productSpecialPriceFromDate = $productOld->getSpecialFromDate();
                $this->_productSpecialPriceToDate = $productOld->getSpecialToDate();
                //--------------------

                // Save preview attributes
                //--------------------
                $this->_productCustomAttributes = $this->getCustomAttributes($this->_listingsArray,
                                                                             $this->_otherListingsArray);

                /** @var $magentoProductModel Ess_M2ePro_Model_Magento_Product */
                $magentoProductModel = Mage::getModel('M2ePro/Magento_Product')->setProduct($productOld);

                foreach ($this->_productCustomAttributes as &$attribute) {
                    $attribute['value_old'] = $magentoProductModel->getAttributeValue($attribute['attribute']);
                }
                //--------------------
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return;
        }
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            $productNew = $observer->getEvent()->getProduct();

            if (!($productNew instanceof Mage_Catalog_Model_Product) ||
                ($this->_productId > 0 && (int)$productNew->getId() != $this->_productId) ||
                (int)$productNew->getData('store_id') != $this->_storeId) {
                return;
            }

            $this->_productId = (int)$productNew->getId();

            $productNew = Mage::getModel('catalog/product')
                                    ->setStoreId($this->_storeId)
                                    ->load($this->_productId);

            $this->tryToUpdateTheLogsNames($productNew);

            if (count($this->_listingsArray) > 0 || count($this->_otherListingsArray) > 0) {

                // Save global changes
                //--------------------
                Mage::getModel('M2ePro/ProductChange')
                                ->addUpdateAction( $this->_productId,
                                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );
                //--------------------

                $this->tryToUpdateTheStatus($productNew);

                $this->tryToUpdateThePrice($productNew);
                $this->tryToUpdateTheSpecialPrice($productNew);
                $this->tryToUpdateTheSpecialPriceFromDate($productNew);
                $this->tryToUpdateTheSpecialPriceToDate($productNew);

                $this->tryToUpdateTheCustomAttributes($productNew);
                $this->updateListingsProductsVariations($productNew);
            }

            $this->tryToPerformCategoriesActions($productNew);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return;
        }
    }

    //------------------------------------

    public function catalogProductDeleteBefore(Varien_Event_Observer $observer)
    {
        try {

            $productDeleted = $observer->getEvent()->getProduct();

            if (!($productDeleted instanceof Mage_Catalog_Model_Product)) {
                return;
            }

            Mage::getModel('M2ePro/Listing')->removeDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/Listing_Other')->unmapDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/ProductChange')->removeDeletedProduct($productDeleted);
            Mage::getModel('M2ePro/Item')->removeDeletedProduct($productDeleted);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return;
        }
    }

    //####################################

    private function getCustomAttributes($listingsArray,$otherListingsArray)
    {
        try {

            $attributesWithListings = $this->getCustomAttributesWithListings($listingsArray);
            $attributesWithOtherListings = $this->getCustomAttributesWithOtherListings($otherListingsArray);

            foreach ($attributesWithOtherListings as $hash => $otherListingAttribute) {
                if (isset($attributesWithListings[$hash])) {
                    $attributesWithListings[$hash]['other_listings'] = $otherListingAttribute['other_listings'];
                } else {
                    $attributesWithListings[$hash] = $otherListingAttribute;
                }
            }

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Exception')->process($exception);
            return array();
        }

        return array_values($attributesWithListings);
    }

    private function cutAttributeTitleLength($attribute, $length = 50)
    {
        if (strlen($attribute) > $length) {
            return substr($attribute, 0, $length) . ' ...';
        }

        return $attribute;
    }

    //-----------------------------------

    private function getCustomAttributesWithListings($listingsArray)
    {
        $attributes = array();

        foreach ($listingsArray as $listingTemp) {

            /** @var $listingModel Ess_M2ePro_Model_Listing */
            $listingModel = Mage::getModel('M2ePro/Listing')->loadInstance($listingTemp['id']);

            $tempAttributesGeneralTemplate = $listingModel->getGeneralTemplate()->getTrackingAttributes();
            $tempAttributesSellingFormatTemplate = $listingModel->getSellingFormatTemplate()->getTrackingAttributes();
            $tempAttributesDescriptionTemplate = $listingModel->getDescriptionTemplate()->getTrackingAttributes();

            $tempListingAttributes = array_merge(
                $tempAttributesGeneralTemplate,$tempAttributesSellingFormatTemplate
            );
            $tempListingAttributes = array_merge(
                $tempListingAttributes,$tempAttributesDescriptionTemplate
            );
            $tempListingAttributes = array_unique(
                $tempListingAttributes
            );

            foreach ($tempListingAttributes as $attribute) {

                $hash = md5($attribute);

                if (!isset($attributes[$hash])) {
                    $attributes[$hash] = array(
                        'attribute' => $attribute,
                        'listings' => array($listingTemp)
                    );
                } else {
                    $attributes[$hash]['listings'][] = $listingTemp;
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

    private function updateListingsProductsVariations($productNew)
    {
        foreach ($this->_listingsArray as $listingTemp) {

            $listingsProductsTemp = Mage::getModel('M2ePro/Listing')
                                            ->loadInstance($listingTemp['id'])
                                            ->getProducts(true,array('product_id'=>$this->_productId));

            foreach ($listingsProductsTemp as $listingProductTemp) {

                $variationUpdaterModelPrefix = ucwords($listingProductTemp->getData('component_mode')).'_';
                Mage::getModel('M2ePro/'.$variationUpdaterModelPrefix.'Listing_Product_Variation_Updater')
                        ->updateVariations($listingProductTemp);
            }
        }
    }

    private function tryToPerformCategoriesActions($productNew)
    {
        $categoriesNew = $productNew->getCategoryIds();

        $addedCategories = array_diff($categoriesNew,$this->_productCategoriesOld);
        foreach ($addedCategories as $categoryId) {
           Ess_M2ePro_Model_Observer_Category::synchChangesWithListings(
               $categoryId,array($productNew),array()
           );
        }

        $deletedCategories = array_diff($this->_productCategoriesOld,$categoriesNew);
        foreach ($deletedCategories as $categoryId) {
           Ess_M2ePro_Model_Observer_Category::synchChangesWithListings(
               $categoryId,array(),array($productNew)
           );
        }
    }

    //####################################

    private function tryToUpdateTheStatus($productNew)
    {
        $statusOld = (int)$this->_productStatusOld;
        $statusNew = (int)$productNew->getStatus();

        // Parser hack -> Mage::helper('M2ePro')->__('Enabled');
        // Parser hack -> Mage::helper('M2ePro')->__('Disabled');

        $statusOld = ($statusOld == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';
        $statusNew = ($statusNew == Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ? 'Enabled' : 'Disabled';

        $attributeStoreIds = Mage::helper('M2ePro/Magento')
                                        ->getStoreIdsByAttributeAndStore('status',$this->_storeId);

        $changedStores = array();

        foreach ($this->_listingsArray as $listingTemp) {

            if (!in_array($listingTemp['store_id'],$attributeStoreIds)) {
                continue;
            }

            $rez = Mage::getModel('M2ePro/ProductChange')
                        ->updateAttribute( $this->_productId, 'status',
                                           $statusOld, $statusNew,
                                           Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                           $listingTemp['store_id'] );

            if ($rez === false) {
                continue;
            }

            $changedStores[$listingTemp['store_id']] = true;

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingTemp['component_mode']);
            $tempLog->addProductMessage(
                $listingTemp['id'],
                $this->_productId,
                NULL,
                Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_STATUS,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%].');

                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%].',
                    array('from'=>$statusOld,'to'=>$statusNew)
                ),
                Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            if (!in_array($otherListingTemp['store_id'],$attributeStoreIds)) {
                continue;
            }

            if (!isset($changedStores[$otherListingTemp['store_id']])) {

                $rez = Mage::getModel('M2ePro/ProductChange')
                            ->updateAttribute( $this->_productId, 'status',
                                               $statusOld, $statusNew,
                                               Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                               $otherListingTemp['store_id'] );

                if ($rez === false) {
                    continue;
                }
            }

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_STATUS,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%].');
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
                    ->updateAttribute( $this->_productId, 'price',
                                       $priceOld, $priceNew,
                                       Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false) {
            return;
        }

        foreach ($this->_listingsArray as $listingTemp) {

             $tempLog = Mage::getModel('M2ePro/Listing_Log');
             $tempLog->setComponentMode($listingTemp['component_mode']);
             $tempLog->addProductMessage(
                $listingTemp['id'],
                $this->_productId,
                NULL,
                Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_PRICE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$priceOld,'!to'=>$priceNew)
                ),
                Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
             );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

             $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
             $tempLog->setComponentMode($otherListingTemp['component_mode']);
             $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_PRICE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
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
                    ->updateAttribute( $this->_productId, 'special_price',
                                       $specialPriceOld, $specialPriceNew,
                                       Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false) {
            return;
        }

        foreach ($this->_listingsArray as $listingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingTemp['component_mode']);
            $tempLog->addProductMessage(
                $listingTemp['id'],
                $this->_productId,
                NULL,
                Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',array('!from'=>$specialPriceOld,'!to'=>$specialPriceNew)
                ),
                Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
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
                    ->updateAttribute( $this->_productId, 'special_price_from_date',
                                       $specialPriceFromDateOld, $specialPriceFromDateNew,
                                       Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER);

        if ($rez === false) {
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

        foreach ($this->_listingsArray as $listingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingTemp['component_mode']);
            $tempLog->addProductMessage(
                $listingTemp['id'],
                $this->_productId,
                NULL,
                Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                // Parser hack -> Mage::helper('M2ePro')->__('None');
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceFromDateOld,'!to'=>$specialPriceFromDateNew)
                ),
                Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_FROM_DATE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                // Parser hack -> Mage::helper('M2ePro')->__('None');
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
                    ->updateAttribute( $this->_productId, 'special_price_to_date',
                                       $specialPriceToDateOld, $specialPriceToDateNew,
                                       Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER );

        if ($rez === false) {
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

        foreach ($this->_listingsArray as $listingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Log');
            $tempLog->setComponentMode($listingTemp['component_mode']);
            $tempLog->addProductMessage(
                $listingTemp['id'],
                $this->_productId,
                NULL,
                Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                // Parser hack -> Mage::helper('M2ePro')->__('None');
                Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                    'From [%from%] to [%to%]',
                    array('!from'=>$specialPriceToDateOld,'!to'=>$specialPriceToDateNew)
                ),
                Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
            );
        }

        foreach ($this->_otherListingsArray as $otherListingTemp) {

            $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
            $tempLog->setComponentMode($otherListingTemp['component_mode']);
            $tempLog->addProductMessage(
                $otherListingTemp['id'],
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                NULL,
                Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_PRODUCT_SPECIAL_PRICE_TO_DATE,
                // Parser hack -> Mage::helper('M2ePro')->__('From [%from%] to [%to%]');
                // Parser hack -> Mage::helper('M2ePro')->__('None');
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

            $attributeStoreIds = Mage::helper('M2ePro/Magento')
                                            ->getStoreIdsByAttributeAndStore($attribute['attribute'],
                                                                             $this->_storeId);

            $changedStores = array();

            if (isset($attribute['listings'])) {

                foreach ($attribute['listings'] as $listingTemp) {

                    if (!in_array($listingTemp['store_id'],$attributeStoreIds)) {
                        continue;
                    }

                    $rez = Mage::getModel('M2ePro/ProductChange')
                                ->updateAttribute( $this->_productId, $attribute['attribute'],
                                                   $customAttributeOld, $customAttributeNew,
                                                   Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                                   $listingTemp['store_id']);

                    if ($rez === false) {
                        continue;
                    }

                    $changedStores[$listingTemp['store_id']] = true;

                    $tempLog = Mage::getModel('M2ePro/Listing_Log');
                    $tempLog->setComponentMode($listingTemp['component_mode']);
                    $tempLog->addProductMessage(
                        $listingTemp['id'],
                        $this->_productId,
                        NULL,
                        Ess_M2ePro_Model_Listing_Log::INITIATOR_EXTENSION,
                        NULL,
                        Ess_M2ePro_Model_Listing_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                        // ->__('Attribute "%attr%" from [%from%] to [%to%].');
                        Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                            'Attribute "%attr%" from [%from%] to [%to%].',
                            array(
                                '!attr'=>$attribute['attribute'],
                                '!from'=>$this->cutAttributeTitleLength($customAttributeOld),
                                '!to'=>$this->cutAttributeTitleLength($customAttributeNew)
                            )
                        ),
                        Ess_M2ePro_Model_Listing_Log::TYPE_NOTICE,
                        Ess_M2ePro_Model_Listing_Log::PRIORITY_LOW
                    );
                }
            }

            if (isset($attribute['other_listings'])) {

                foreach ($this->_otherListingsArray as $otherListingTemp) {

                    if (!in_array($otherListingTemp['store_id'],$attributeStoreIds)) {
                        continue;
                    }

                    if (!isset($changedStores[$otherListingTemp['store_id']])) {

                        $rez = Mage::getModel('M2ePro/ProductChange')
                                    ->updateAttribute( $this->_productId, $attribute['attribute'],
                                                       $customAttributeOld, $customAttributeNew,
                                                       Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,
                                                       $otherListingTemp['store_id'] );

                        if ($rez === false) {
                            continue;
                        }
                    }

                    $tempLog = Mage::getModel('M2ePro/Listing_Other_Log');
                    $tempLog->setComponentMode($otherListingTemp['component_mode']);
                    $tempLog->addProductMessage(
                        $otherListingTemp['id'],
                        Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION,
                        NULL,
                        Ess_M2ePro_Model_Listing_Other_Log::ACTION_CHANGE_CUSTOM_ATTRIBUTE,
                        // ->__('Attribute "%attr%" from [%from%] to [%to%].');
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

    //####################################
}