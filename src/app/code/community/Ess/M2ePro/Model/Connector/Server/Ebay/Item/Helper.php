<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_Helper
{
    // ########################################

    public function getListRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        // Set permissions
        //-----------------
        $permissions = array(
            'general'=>true,
            'variations'=>true,
            'qty'=>true,
            'price'=>true,
            'title'=>true,
            'subtitle'=>true,
            'description'=>true
        );

        if (isset($params['only_data'])) {
            foreach ($permissions as &$value) {
                $value = false;
            }
            $permissions = array_merge($permissions,$params['only_data']);
        }

        if (isset($params['all_data'])) {
            foreach ($permissions as &$value) {
                $value = true;
            }
        }
        //-----------------

        $requestData = array();

        // Prepare Variations
        //-------------------
        $tempLogsActionId = isset($params['logs_action_id']) ? $params['logs_action_id'] : NULL;
        $tempLogsInitiator = isset($params['logs_initiator']) ? $params['logs_initiator'] :
                                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        $tempLogsAction = isset($params['logs_action']) ? $params['logs_action'] :
                                                    Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING;
        Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater')->updateVariations($listingProduct,
                                                                                          $tempLogsActionId,
                                                                                          $tempLogsInitiator,
                                                                                          $tempLogsAction);
        $tempVariations = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                ->getRequestData($listingProduct,$params);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }
        //-------------------

        // Get Variations
        //-------------------
        if ($permissions['variations'] && $requestData['is_variation_item']) {

            $additionalData = $listingProduct->getChildObject()->getData('additional_data');
            is_string($additionalData) && $additionalData = json_decode($additionalData,true);
            if (isset($additionalData['variations_sets'])) {
                $requestData['variations_sets'] =  $additionalData['variations_sets'];
            }

            $requestData['variation'] = $tempVariations;
            $requestData['variation_image'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                    ->getImagesData($listingProduct,$params);
            if (count($requestData['variation_image']) == 0) {
                unset($requestData['variation_image']);
            }
        }
        //-------------------

        // Get General Info
        //-------------------
        $permissions['general'] && $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        $permissions['general'] && $this->addSellingFormatData($listingProduct,$requestData);

        $this->addDescriptionData($listingProduct,$requestData,$permissions);

        if (($permissions['qty'] || $permissions['price']) && !$requestData['is_variation_item']) {
            $this->addQtyPriceData($listingProduct,$requestData,$permissions);
        }

        if ($permissions['general']) {

            $this->addCategoriesData($listingProduct,$requestData);
            $this->addStoreCategoriesData($listingProduct,$requestData);

            $this->addProductDetailsData($listingProduct,$requestData);
            $this->addItemSpecificsData($listingProduct,$requestData);
            $this->addCustomItemSpecificsData($listingProduct,$requestData);
            $this->addMotorsSpecificsData($listingProduct,$requestData);
            $this->addAttributeSetData($listingProduct,$requestData);

            $this->addInternationalTradeData($listingProduct,$requestData);

            $requestData['tax_category'] = $listingProduct->getChildObject()->getTaxCategory();
            $requestData['item_condition'] = $listingProduct->getChildObject()->getItemCondition();
            $requestData['listing_enhancements'] = $listingProduct->getGeneralTemplate()
                ->getChildObject()->getEnhancements();
        }
        //-------------------

        // Get Shipping Info
        //-------------------
        if ($permissions['general']) {

            $this->addShippingData($listingProduct,$requestData);

            $requestData['country'] = $listingProduct->getGeneralTemplate()->getChildObject()->getCountry();
            $requestData['postal_code'] = $listingProduct->getGeneralTemplate()->getChildObject()->getPostalCode();
            $requestData['address'] = $listingProduct->getGeneralTemplate()->getChildObject()->getAddress();
        }
        //-------------------

        // Get Payment Info
        //-------------------
        if ($permissions['general']) {

            $this->addPaymentData($listingProduct,$requestData);

            $requestData['vat_percent'] = $listingProduct->getGeneralTemplate()
                ->getChildObject()->getVatPercent();
            $requestData['use_tax_table'] = $listingProduct->getGeneralTemplate()
                ->getChildObject()->isUseEbayTaxTableEnabled();
        }
        //-------------------

        // Get Refund Info
        //-------------------
        if ($permissions['general']) {
            $requestData['return_policy'] = $listingProduct->getGeneralTemplate()->getChildObject()->getRefundOptions();
        }
        //-------------------

        // Get Images Info
        //-------------------
        $permissions['general'] && $this->addImagesData($listingProduct,$requestData);
        //-------------------

        $requestData['is_m2epro_listed_item'] = Ess_M2ePro_Model_Ebay_Listing_Product::IS_M2EPRO_LISTED_ITEM_YES;

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Add New eBay Item Id
        //---------------------
        $ebayItemsId = $this->createNewEbayItemsId($listingProduct,$params['ebay_item_id']);
        //---------------------

        // Save additional info
        //---------------------
        $additionalData = $listingProduct->getChildObject()->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        $additionalData['is_eps_ebay_images_mode'] = $params['is_eps_ebay_images_mode'];
        $additionalData['ebay_item_fees'] = $params['ebay_item_fees'];

        $listingProduct->setData('is_m2epro_listed_item',
                                 Ess_M2ePro_Model_Ebay_Listing_Product::IS_M2EPRO_LISTED_ITEM_YES);

        $listingProduct->setData('additional_data', json_encode($additionalData))->save();
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        $ebayItemsId,
                                        false);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,$nativeRequestData,$params,false);
        //---------------------
    }

    //----------------------------------------

    public function getRelistRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        // Set permissions
        //-----------------
        $permissions = array(
            'base'=>true,
            'additional'=>true
        );

        if (isset($params['only_data'])) {
            foreach ($permissions as &$value) {
                $value = false;
            }
            $permissions = array_merge($permissions,$params['only_data']);
        }

        if (isset($params['all_data'])) {
            foreach ($permissions as &$value) {
                $value = true;
            }
        }
        //-----------------

        $requestData = array();

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getChildObject()->getEbayItem()->getItemId();
        //-------------------

        // Prepare Variations
        //-------------------
        $tempLogsActionId = isset($params['logs_action_id']) ? $params['logs_action_id'] : NULL;
        $tempLogsInitiator = isset($params['logs_initiator']) ? $params['logs_initiator'] :
                                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        $tempLogsAction = isset($params['logs_action']) ? $params['logs_action'] :
                                                    Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING;
        Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater')->updateVariations($listingProduct,
                                                                                          $tempLogsActionId,
                                                                                          $tempLogsInitiator,
                                                                                          $tempLogsAction);
        $tempVariations = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                ->getRequestData($listingProduct,$params);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }
        //-------------------

        // Add eBay image upload mode
        //---------------------
        $additionalData = $listingProduct->getChildObject()->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        if (isset($additionalData['is_eps_ebay_images_mode'])) {
            $requestData['is_eps_ebay_images_mode'] = $additionalData['is_eps_ebay_images_mode'];
        }
        //---------------------

        // Get Variations
        //-------------------
        if ($permissions['additional'] && $requestData['is_variation_item']) {

            $additionalData = $listingProduct->getChildObject()->getData('additional_data');
            is_string($additionalData) && $additionalData = json_decode($additionalData,true);
            if (isset($additionalData['variations_sets'])) {
                $requestData['variations_sets'] =  $additionalData['variations_sets'];
            }

            $requestData['variation'] = $tempVariations;
            $requestData['variation_image'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                    ->getImagesData($listingProduct,$params);
            if (count($requestData['variation_image']) == 0) {
                unset($requestData['variation_image']);
            }
        }
        //-------------------

        // Get General Info
        //-------------------
        if ($permissions['additional']) {

            $this->addDescriptionData($listingProduct,$requestData,array());
            $this->addShippingData($listingProduct,$requestData);

            $this->addInternationalTradeData($listingProduct,$requestData);

            if (!$requestData['is_variation_item']) {
                $this->addQtyPriceData($listingProduct,$requestData,array());
            }
        }
        //-------------------

        $this->addIsM2eProListedItemData($listingProduct,$requestData);

        return $requestData;
    }

    public function updateAfterRelistAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Add New eBay Item Id
        //---------------------
        $ebayItemsId = $this->createNewEbayItemsId($listingProduct,$params['ebay_item_id']);
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        $ebayItemsId,
                                        false);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,$nativeRequestData,$params,false);
        //---------------------
    }

    //----------------------------------------

    public function getReviseRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $params['return_variation_has_sales_key_when_qty_is_zero'] = true;
        $requestData = $this->getListRequestData($listingProduct,$params);
        unset($requestData['is_m2epro_listed_item']);

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getChildObject()->getEbayItem()->getItemId();
        //-------------------

        // Delete title and subtitle when item has bid(s)
        //-------------------
        if (isset($requestData['title']) || isset($requestData['subtitle']) ||
            isset($requestData['duration']) || isset($requestData['is_private'])) {

            $tempDeleteData = (is_null($listingProduct->getChildObject()->getOnlineQtySold())
                                   ? 0
                                   : $listingProduct->getChildObject()->getOnlineQtySold() > 0) ||
                              ($listingProduct->getChildObject()->isListingTypeAuction()
                                   && $listingProduct->getChildObject()->getOnlineBids() > 0);

            if (isset($requestData['title']) && $tempDeleteData) {
                unset($requestData['title']);
            }
            if (isset($requestData['subtitle']) && $tempDeleteData) {
                unset($requestData['subtitle']);
            }
            if (isset($requestData['duration']) && $tempDeleteData) {
                unset($requestData['duration']);
            }
            if (isset($requestData['is_private']) && $tempDeleteData) {
                unset($requestData['is_private']);
            }
        }

        if (isset($requestData['bestoffer_mode']) && $requestData['bestoffer_mode']) {
            unset($requestData['duration']);
        }
        //-------------------

        // Add eBay image upload mode
        //---------------------
        $additionalData = $listingProduct->getChildObject()->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        if (isset($additionalData['is_eps_ebay_images_mode'])) {
            $requestData['is_eps_ebay_images_mode'] = $additionalData['is_eps_ebay_images_mode'];
        }
        //---------------------

        $this->addIsM2eProListedItemData($listingProduct,$requestData);

        return $requestData;
    }

    public function updateAfterReviseAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        NULL,
                                        true);
        //---------------------

        // Update Variations
        //---------------------
        Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                   ->updateAfterAction($listingProduct,$nativeRequestData,$params,true);
        //---------------------
    }

    //----------------------------------------

    public function getStopRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingProduct->getChildObject()->getEbayItem()->getItemId();
        //-------------------

        return $requestData;
    }

    public function updateAfterStopAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED
        );
        if (isset($params['status_changer'])) {
            $dataForUpdate['status_changer'] = (int)$params['status_changer'];
        }
        if (isset($params['end_date_raw'])) {
            $dataForUpdate['end_date'] = Ess_M2ePro_Model_Connector_Server_Ebay_Abstract::ebayTimeToString(
                $params['end_date_raw']
            );
        }
        $listingProduct->addData($dataForUpdate)->save();
        //---------------------

        // Update Variations
        //---------------------
        $productVariations = $listingProduct->getVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $dataForUpdate = array(
                'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_NO
            );
            if ($variation->isListed()) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }
            $variation->addData($dataForUpdate)->save();
        }
        //---------------------
    }

    // ########################################

    protected function addIsM2eProListedItemData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $isListedByM2ePro = $listingProduct->getChildObject()->getData('is_m2epro_listed_item');
        !is_null($isListedByM2ePro) && $requestData['is_m2epro_listed_item'] = (int)$isListedByM2ePro;
    }

    //----------------------------------------

    protected function addSellingFormatData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        if ($listingProduct->getChildObject()->isListingTypeFixed()) {
            $requestData['listing_type'] = Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_FIXED;
        } else {
            $requestData['listing_type'] = Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_AUCTION;
        }

        $requestData['duration'] = $listingProduct->getChildObject()->getDuration();
        $requestData['is_private'] = $listingProduct->getSellingFormatTemplate()->getChildObject()->isPrivateListing();

        $requestData['currency'] = $listingProduct->getSellingFormatTemplate()->getChildObject()->getCurrency();
        $requestData['hit_counter'] = $listingProduct->getDescriptionTemplate()->getChildObject()->getHitCounterType();
    }

    protected function addDescriptionData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array &$requestData,$permissions = array())
    {
        if (!isset($permissions['title']) || $permissions['title']) {
            $requestData['title'] = $listingProduct->getChildObject()->getTitle();
        }

        if (!isset($permissions['subtitle']) || $permissions['subtitle']) {
            $requestData['subtitle'] = $listingProduct->getChildObject()->getSubTitle();
        }

        if (!isset($permissions['description']) || $permissions['description']) {
            $requestData['description'] = $listingProduct->getChildObject()->getDescription();
        }
    }

    protected function addQtyPriceData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                       array &$requestData, $permissions = array())
    {
        if (!isset($permissions['qty']) || $permissions['qty']) {
            $requestData['qty'] = $listingProduct->getChildObject()->getQty();
        }

        if (!isset($permissions['price']) || $permissions['price']) {

            if ($listingProduct->getChildObject()->isListingTypeFixed()) {
                $requestData['price_fixed'] = $listingProduct->getChildObject()->getBuyItNowPrice();
                $this->addBestOfferData($listingProduct,$requestData);
            } else {
                $requestData['price_start'] = $listingProduct->getChildObject()->getStartPrice();
                $requestData['price_reserve'] = $listingProduct->getChildObject()->getReservePrice();
                $requestData['price_buyitnow'] = $listingProduct->getChildObject()->getBuyItNowPrice();
            }
        }
    }

    //----------------------------------------

    protected function addCategoriesData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['category_main_id'] = $listingProduct->getChildObject()->getMainCategory();
        $requestData['category_secondary_id'] = $listingProduct->getChildObject()->getSecondaryCategory();
    }

    protected function addStoreCategoriesData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['store_category_main_id'] = $listingProduct->getChildObject()->getMainStoreCategory();
        $requestData['store_category_secondary_id'] = $listingProduct->getChildObject()->getSecondaryStoreCategory();
    }

    //----------------------------------------

    protected function addBestOfferData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        if ($listingProduct->getChildObject()->isListingTypeFixed()) {
            $requestData['bestoffer_mode'] = $listingProduct->getSellingFormatTemplate()
                ->getChildObject()->isBestOfferEnabled();
            if ($requestData['bestoffer_mode']) {
                $requestData['bestoffer_accept_price'] = $listingProduct->getChildObject()->getBestOfferAcceptPrice();
                $requestData['bestoffer_reject_price'] = $listingProduct->getChildObject()->getBestOfferRejectPrice();
            }
        }
    }

    protected function addProductDetailsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['product_details'] = array();

        $temp = $listingProduct->getChildObject()->getProductDetail('isbn');
        $temp && $requestData['product_details']['isbn'] = $temp;

        $temp = $listingProduct->getChildObject()->getProductDetail('epid');
        $temp && $requestData['product_details']['epid'] = $temp;

        $temp = $listingProduct->getChildObject()->getProductDetail('upc');
        $temp && $requestData['product_details']['upc'] = $temp;

        $temp = $listingProduct->getChildObject()->getProductDetail('ean');
        $temp && $requestData['product_details']['ean'] = $temp;
    }

    //----------------------------------------

    protected function addItemSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['item_specifics'] = array();

        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ITEM_SPECIFICS);
        $tempListingSpecifics = $listingProduct->getGeneralTemplate()->getChildObject()->getSpecifics(true, $filter);
        foreach ($tempListingSpecifics as $tempSpecific) {

            $tempSpecific->setMagentoProduct($listingProduct->getMagentoProduct());

            $tempAttributeData = $tempSpecific->getAttributeData();
            $tempAttributeValues = $tempSpecific->getValues();

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $requestData['item_specifics'][] = array(
                'name' => $tempAttributeData['id'],
                'value' => $values
            );
        }
    }

    protected function addCustomItemSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $filter = array('mode' => Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_CUSTOM_ITEM_SPECIFICS);
        $tempListingSpecifics = $listingProduct->getGeneralTemplate()->getChildObject()->getSpecifics(true, $filter);
        foreach ($tempListingSpecifics as $tempSpecific) {

            $tempSpecific->setMagentoProduct($listingProduct->getMagentoProduct());

            $tempAttributeData = $tempSpecific->getAttributeData();
            $tempAttributeValues = $tempSpecific->getValues();

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue['value'] == '--') {
                    continue;
                }
                $values[] = $tempAttributeValue['value'];
            }

            $requestData['item_specifics'][] = array(
                'name' => $tempAttributeData['title'],
                'value' => $values
            );
        }
    }

    protected function addMotorsSpecificsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $marketplace = $listingProduct->getGeneralTemplate()->getMarketplace();
        if ($marketplace->getId() != Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS) {
            return;
        }

        $categoryId = $listingProduct->getChildObject()->getMainCategory();
        $categoryData = $marketplace->getChildObject()->getCategory($categoryId);

        $features = !empty($categoryData['features']) ? (array)json_decode($categoryData['features'], true) : array();
        $attributes = !empty($features['parts_compatibility_attributes'])
            ? $features['parts_compatibility_attributes']
            : array();

        if (empty($attributes)) {
            return;
        }

        $specifics = $listingProduct->getChildObject()->getMotorsSpecifics();

        foreach ($specifics as $specific) {
            $compatibilityData = $specific->getCompatibilityData();
            $compatibilityList = array();

            foreach ($compatibilityData as $key => $value) {
                if ($value == '--') {
                    unset($compatibilityData[$key]);
                    continue;
                }

                $name = $key;
                foreach ($attributes as $attribute) {
                    if ($attribute['title'] == $key) {
                        $name = $attribute['ebay_id'];
                        break;
                    }
                }

                $compatibilityList[] = array(
                    'name'  => $name,
                    'value' => $value
                );
            }

            $requestData['motors_specifics'][] = $compatibilityList;
        }
    }

    protected function addAttributeSetData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['attribute_set'] = array(
            'attribute_set_id' => 0,
            'attributes' => array()
        );

        $filters = array('mode' => Ess_M2ePro_Model_Ebay_Template_General_Specific::MODE_ATTRIBUTE_SET);
        $tempListingSpecifics = $listingProduct->getGeneralTemplate()->getChildObject()->getSpecifics(true, $filters);
        foreach ($tempListingSpecifics as $tempSpecific) {

            $tempSpecific->setMagentoProduct($listingProduct->getMagentoProduct());

            $tempAttributeData = $tempSpecific->getAttributeData();
            $tempAttributeValues = $tempSpecific->getValues();

            $requestData['attribute_set']['attribute_set_id'] = $tempSpecific->getModeRelationId();
            $requestData['attribute_set']['attributes'][] = array(
                'id' => $tempAttributeData['id'],
                'value' => $tempAttributeValues
            );
        }
    }

    //----------------------------------------

    protected function addShippingData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['shipping'] = array();

        $generalTemplate = $listingProduct->getGeneralTemplate();
        /** @var $generalTemplateEbay Ess_M2ePro_Model_Ebay_Template_General */
        $generalTemplateEbay = $generalTemplate->getChildObject();

        if ($generalTemplateEbay->isLocalShippingEnabled()) {

            $requestData['use_local_shipping_rate_table'] =
                $generalTemplateEbay->isUseEbayLocalShippingRateTableEnabled();

            $requestData['shipping']['local'] = array();

            if ($generalTemplateEbay->isLocalShippingFreightEnabled()) {
                $requestData['shipping']['local']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_FREIGHT;
            }
            if ($generalTemplateEbay->isLocalShippingLocalEnabled()) {
                $requestData['shipping']['local']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_LOCAL;
            }
            if ($generalTemplateEbay->isLocalShippingFlatEnabled()) {
                $requestData['shipping']['local']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_FLAT;
            }

            if ($generalTemplateEbay->isLocalShippingCalculatedEnabled()) {
                $requestData['shipping']['local']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_CALCULATED;
                $requestData['shipping']['local']['handing_fee'] = $listingProduct
                    ->getChildObject()->getLocalHandling();
                $requestData['shipping']['calculated'] = $this->getCalculatedData(
                    $generalTemplateEbay, $listingProduct
                );
            }

            if ($generalTemplateEbay->isLocalShippingFlatEnabled() ||
                $generalTemplateEbay->isLocalShippingCalculatedEnabled()) {

                $combinedDiscountProfile = $generalTemplateEbay->getLocalShippingCombinedDiscountProfileId();
                $cashOnDelivery = $generalTemplateEbay->isLocalShippingCashOnDeliveryEnabled();
                $cashOnDeliveryCost = $listingProduct->getChildObject()->getLocalShippingCashOnDeliveryCost();

                $requestData['shipping']['get_it_fast'] = $generalTemplateEbay->isGetItFastEnabled();
                $requestData['shipping']['dispatch_time'] = $generalTemplateEbay->getDispatchTime();
                $requestData['shipping']['local']['discount'] = $generalTemplateEbay->isLocalShippingDiscountEnabled();
                $requestData['shipping']['local']['combined_discount_profile'] = $combinedDiscountProfile;
                $requestData['shipping']['local']['cash_on_delivery'] = $cashOnDelivery;
                $requestData['shipping']['local']['cash_on_delivery_cost'] = $cashOnDeliveryCost;
                $requestData['shipping']['local']['methods'] = array();

                $tempShippingsMethods = $generalTemplateEbay->getShippings(true);
                foreach ($tempShippingsMethods as $tempMethod) {
                    if (!$tempMethod->isShippingTypeLocal()) {
                       continue;
                    }
                    $tempMethod->setMagentoProduct($listingProduct->getMagentoProduct());
                    $tempDataMethod = array(
                        'service' => $tempMethod->getShippingValue()
                    );
                    if ($generalTemplateEbay->isLocalShippingFlatEnabled()) {
                        $tempDataMethod['cost'] = $tempMethod->getCost();
                        $tempDataMethod['cost_additional'] = $tempMethod->getCostAdditional();
                    }
                    if ($generalTemplateEbay->isLocalShippingCalculatedEnabled()) {
                        $tempDataMethod['is_free'] = $tempMethod->isCostModeFree();
                    }
                    $requestData['shipping']['local']['methods'][] = $tempDataMethod;
                }
            }
        }

        if ($generalTemplateEbay->isInternationalShippingEnabled() &&
            !$generalTemplateEbay->isLocalShippingFreightEnabled() &&
            !$generalTemplateEbay->isLocalShippingLocalEnabled()) {

            $requestData['use_international_shipping_rate_table'] =
                $generalTemplateEbay->isUseEbayInternationalShippingRateTableEnabled();

            $requestData['shipping']['international'] = array();

            if ($generalTemplateEbay->isInternationalShippingFlatEnabled()) {
                $requestData['shipping']['international']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_FLAT;
            }

            if ($generalTemplateEbay->isInternationalShippingCalculatedEnabled()) {
                $requestData['shipping']['international']['type'] =
                    Ess_M2ePro_Model_Ebay_Template_General::EBAY_SHIPPING_TYPE_CALCULATED;
                $requestData['shipping']['international']['handing_fee'] = $listingProduct
                    ->getChildObject()->getInternationalHandling();
                if (!isset($requestData['shipping']['calculated'])) {
                    $requestData['shipping']['calculated'] = $this->getCalculatedData(
                        $generalTemplateEbay, $listingProduct
                    );
                }
            }

            $discount = $generalTemplateEbay->isInternationalShippingDiscountEnabled();
            $combinedDiscountProfile = $generalTemplateEbay->getInternationalShippingCombinedDiscountProfileId();

            $requestData['shipping']['international']['discount'] = $discount;
            $requestData['shipping']['international']['combined_discount_profile'] = $combinedDiscountProfile;
            $requestData['shipping']['international']['methods'] = array();

            $tempShippingsMethods = $generalTemplateEbay->getShippings(true);
            foreach ($tempShippingsMethods as $tempMethod) {
                if (!$tempMethod->isShippingTypeInternational()) {
                   continue;
                }
                $tempMethod->setMagentoProduct($listingProduct->getMagentoProduct());
                $tempDataMethod = array(
                    'service' => $tempMethod->getShippingValue(),
                    'locations' => $tempMethod->getLocations()
                );
                if ($generalTemplateEbay->isInternationalShippingFlatEnabled()) {
                    $tempDataMethod['cost'] = $tempMethod->getCost();
                    $tempDataMethod['cost_additional'] = $tempMethod->getCostAdditional();
                }
                $requestData['shipping']['international']['methods'][] = $tempDataMethod;
            }
        }

        if ($generalTemplateEbay->isLocalShippingFlatEnabled()
            && $generalTemplateEbay->isUseEbayLocalShippingRateTableEnabled()
            && !$generalTemplateEbay->isInternationalShippingCalculatedEnabled()
            && !isset($requestData['shipping']['calculated'])
        ) {
            $calculatedData = $this->getCalculatedData($generalTemplateEbay, $listingProduct);
            unset($calculatedData['package_size']);
            unset($calculatedData['originating_postal_code']);
            unset($calculatedData['dimensions']);

            $requestData['shipping']['calculated'] = $calculatedData;
        }
    }

    protected function getCalculatedData(
        Ess_M2ePro_Model_Ebay_Template_General $generalTemplateEbay,
        Ess_M2ePro_Model_Listing_Product $listingProduct
    ) {
        if (is_null($generalTemplateEbay->getCalculatedShipping())) {
            return array();
        }

        $measurementSystem = $generalTemplateEbay->getCalculatedShipping()->getMeasurementSystem();
        $originatingPostalCode = $generalTemplateEbay->getCalculatedShipping()->getPostalCode();

        $calculatedData = array(
            'measurement_system' => $measurementSystem,
            'package_size' => $listingProduct->getChildObject()->getPackageSize(),
            'originating_postal_code' => $originatingPostalCode,
            'dimensions' => $listingProduct->getChildObject()->getDimensions(),
            'weight' => $listingProduct->getChildObject()->getWeight()
        );

        if ($measurementSystem ==
            Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::MEASUREMENT_SYSTEM_ENGLISH) {
            $calculatedData['measurement_system'] =
                Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::EBAY_MEASUREMENT_SYSTEM_ENGLISH;
        }
        if ($measurementSystem ==
            Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::MEASUREMENT_SYSTEM_METRIC) {
            $calculatedData['measurement_system'] =
                Ess_M2ePro_Model_Ebay_Template_General_CalculatedShipping::EBAY_MEASUREMENT_SYSTEM_METRIC;
        }

        return $calculatedData;
    }

    //----------------------------------------

    protected function addPaymentData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['payment'] = array(
            'methods' => $listingProduct->getGeneralTemplate()->getChildObject()->getPaymentMethods()
        );

        if (in_array('PayPal',$requestData['payment']['methods'])) {
            $immediate_payment = $listingProduct->getGeneralTemplate()
                ->getChildObject()->isPayPalImmediatePaymentEnabled();
            $requestData['payment']['paypal'] = array(
                'email' => $listingProduct->getGeneralTemplate()->getChildObject()->getPayPalEmailAddress(),
                'immediate_payment' => $immediate_payment
            );
        }
    }

    //----------------------------------------

    protected function addImagesData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['images'] = array(
            'gallery_type' => $listingProduct->getGeneralTemplate()->getChildObject()->getGalleryType(),
            'images' => $listingProduct->getChildObject()->getImagesForEbay(),
            'supersize' => $listingProduct->getDescriptionTemplate()->getChildObject()->isUseSupersizeImagesEnabled()
        );
    }

    protected function addInternationalTradeData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['international_trade'] = 'None';
        $internationalTrade = $listingProduct->getGeneralTemplate()
            ->getChildObject()->getSettings('international_trade');

        if (!empty($internationalTrade)) {
            if (isset($internationalTrade['international_trade_uk']) &&
                (int)$internationalTrade['international_trade_uk'] == 1) {
                $requestData['international_trade'] = 'UK';
            }

            if (isset($internationalTrade['international_trade_na']) &&
                (int)$internationalTrade['international_trade_na'] == 1) {
                $requestData['international_trade'] = 'North America';
            }
        }
    }

    // ########################################

    protected function createNewEbayItemsId(Ess_M2ePro_Model_Listing_Product $listingProduct, $ebayRealItemId)
    {
        $dataForAdd = array(
            'item_id' => (double)$ebayRealItemId,
            'product_id' => (int)$listingProduct->getProductId(),
            'store_id' => (int)$listingProduct->getListing()->getStoreId()
        );
        return Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                array $nativeRequestData = array(),
                                                array $params = array(),
                                                $ebayItemsId = NULL,
                                                $saveSoldData = false)
    {
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

        !is_null($ebayItemsId) && $dataForUpdate['ebay_item_id'] = (int)$ebayItemsId;
        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];

        if (isset($params['start_date_raw'])) {
            $dataForUpdate['start_date'] = Ess_M2ePro_Model_Connector_Server_Ebay_Abstract::ebayTimeToString(
                $params['start_date_raw']
            );
        }
        if (isset($params['end_date_raw'])) {
            $dataForUpdate['end_date'] = Ess_M2ePro_Model_Connector_Server_Ebay_Abstract::ebayTimeToString(
                $params['end_date_raw']
            );
        }

        if ($saveSoldData) {

            $dataForUpdate['online_qty_sold'] = is_null($listingProduct->getChildObject()->getOnlineQtySold())
                ? 0 : $listingProduct->getChildObject()->getOnlineQtySold();

            $tempIsVariation = $nativeRequestData['is_variation_item'] && isset($nativeRequestData['variation']);
            $tempUpdateFlag = $tempIsVariation || isset($nativeRequestData['qty']);

            if ($tempUpdateFlag) {
                $tempQty = $tempIsVariation ? $listingProduct->getChildObject()->getQty() : $nativeRequestData['qty'];
                $dataForUpdate['online_qty'] = (int)$tempQty + (int)$dataForUpdate['online_qty_sold'];
            }

        } else {

            $dataForUpdate['online_qty_sold'] = 0;

            $tempIsVariation = $nativeRequestData['is_variation_item'] && isset($nativeRequestData['variation']);
            $tempUpdateFlag = $tempIsVariation || isset($nativeRequestData['qty']);

            if ($tempUpdateFlag) {
                $tempQty = $tempIsVariation ? $listingProduct->getChildObject()->getQty() : $nativeRequestData['qty'];
                $dataForUpdate['online_qty'] = $tempQty;
            }
        }

        if ($listingProduct->getChildObject()->isListingTypeFixed()) {

            $dataForUpdate['online_start_price'] = NULL;
            $dataForUpdate['online_reserve_price'] = NULL;
            $dataForUpdate['online_bids'] = NULL;

            $tempIsVariation = $nativeRequestData['is_variation_item'] && isset($nativeRequestData['variation']);
            $tempUpdateFlag = $tempIsVariation || isset($nativeRequestData['price_fixed']);

            if ($tempUpdateFlag) {

                if ($tempIsVariation) {

                    $tempPrice = NULL;
                    foreach ($nativeRequestData['variation'] as $variation) {
                        if ((int)$variation['qty'] <= 0) {
                            continue;
                        }
                        if (!is_null($tempPrice) && (float)$variation['price'] >= $tempPrice) {
                            continue;
                        }
                        $tempPrice = (float)$variation['price'];
                    }

                } else {
                    $tempPrice = $nativeRequestData['price_fixed'];
                }

                $dataForUpdate['online_buyitnow_price'] = (float)$tempPrice;
            }

        } else {

            !$saveSoldData && $dataForUpdate['online_bids'] = 0;

            if (isset($nativeRequestData['price_start'])) {
                $dataForUpdate['online_start_price'] = (float)$nativeRequestData['price_start'];
            }
            if (isset($nativeRequestData['price_reserve'])) {
                $dataForUpdate['online_reserve_price'] = (float)$nativeRequestData['price_reserve'];
            }
            if (isset($nativeRequestData['price_buyitnow'])) {
                $dataForUpdate['online_buyitnow_price'] = (float)$nativeRequestData['price_buyitnow'];
            }
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}