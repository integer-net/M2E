<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_Helper
{
    // ########################################

    public function getListRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();
        $permissions = $this->getPreparedPermissions(false,$params);
        $this->addVariationsData($listingProduct,$requestData,$permissions,$params);

        if ($permissions['general']) {

            $requestData['sku'] = $listingProduct->getChildObject()->getSku();

            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperCategory')
                                                ->getRequestData($listingProduct,$requestData);
            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperShipping')
                                                ->getRequestData($listingProduct,$requestData);
            $this->addPaymentData($listingProduct,$requestData);
            $this->addReturnData($listingProduct,$requestData);

            $this->addConditionData($listingProduct,$requestData);
            $this->addProductDetailsData($listingProduct,$requestData);

            $this->addSellingFormatData($listingProduct,$requestData);
            $this->addEnhancements($listingProduct,$requestData);
            $this->addImagesData($listingProduct,$requestData);

            $this->addCharityData($listingProduct,$requestData);
        }

        $this->addDescriptionData($listingProduct,$requestData,$permissions);

        if (!$requestData['is_variation_item']) {
            $this->addQtyPriceData($listingProduct,$requestData,$permissions);
        }

        $requestData['is_m2epro_listed_item'] = 1;

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
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        $additionalData['is_eps_ebay_images_mode'] = $params['is_eps_ebay_images_mode'];
        $additionalData['ebay_item_fees'] = $params['ebay_item_fees'];
        $listingProduct->setData('is_m2epro_listed_item',1);
        $listingProduct->setData('is_need_synchronize',0);
        $listingProduct->setData('synch_reasons',NULL);
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
        $requestData = array();
        $permissions = $this->getPreparedPermissions(true,$params);
        $this->addVariationsData($listingProduct,$requestData,$permissions,$params);

        $requestData['item_id'] = $listingProduct->getChildObject()->getEbayItemIdReal();

        if ($permissions['additional']) {

            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperShipping')
                                                ->getRequestData($listingProduct,$requestData);

            $this->addDescriptionData($listingProduct,$requestData,$permissions);

            if (!$requestData['is_variation_item']) {
                $this->addQtyPriceData($listingProduct,$requestData,$permissions);
            }
        }

        $this->addEpsOrSelfHostedMode($listingProduct,$requestData);
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

        // Save additional info
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        $additionalData['ebay_item_fees'] = $params['ebay_item_fees'];
        $listingProduct->setData('additional_data', json_encode($additionalData))->save();
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

        $requestData['item_id'] = $listingProduct->getChildObject()->getEbayItemIdReal();

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

        $this->addEpsOrSelfHostedMode($listingProduct,$requestData);
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

        // Save additional info
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();

        foreach ($params['ebay_item_fees'] as $feeCode => $feeData) {
            if ($feeData['fee'] == 0) {
                continue;
            }

            if (!isset($additionalData['ebay_item_fees'][$feeCode])) {
                $additionalData['ebay_item_fees'][$feeCode] = $feeData;
            } else {
                $additionalData['ebay_item_fees'][$feeCode]['fee'] += $feeData['fee'];
            }
        }

        $listingProduct->setData('additional_data', json_encode($additionalData))->save();
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
        return array(
            'item_id' => $listingProduct->getChildObject()->getEbayItemIdReal()
        );
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

        // Save additional info
        //---------------------
        $additionalData = $listingProduct->getData('additional_data');
        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();
        $additionalData['ebay_item_fees'] = array();
        $listingProduct->setData('additional_data', json_encode($additionalData))->save();
        //---------------------

        // Update Variations
        //---------------------
        $productVariations = $listingProduct->getVariations(true);
        foreach ($productVariations as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $dataForUpdate = array(
                'add' => 0
            );
            if ($variation->getChildObject()->isListed()) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
            }
            $variation->addData($dataForUpdate)->save();
        }
        //---------------------
    }

    // ########################################

    protected function getPreparedPermissions($relistPermissions = false, array $params = array())
    {
        if ($relistPermissions) {
            $permissions = array(
                'base'=>true,
                'additional'=>true
            );
        } else {
            $permissions = array(
                'general'=>true,
                'variations'=>true,
                'qty'=>true,
                'price'=>true,
                'title'=>true,
                'subtitle'=>true,
                'description'=>true
            );
        }

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

        return $permissions;
    }

    protected function addVariationsData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                         array &$requestData, array $permissions, array $params = array())
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater $tempUpdater */
        $tempUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $tempUpdater->setLoggingData(isset($params['logs_action_id']) ? $params['logs_action_id'] : NULL,
                                     isset($params['logs_initiator']) ? $params['logs_initiator'] :
                                                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                     isset($params['logs_action']) ? $params['logs_action'] :
                                                    Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING);

        $tempUpdater->updateVariations($listingProduct);

        $tempVariations = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                ->getRequestData($listingProduct,$params);

        $requestData['is_variation_item'] = false;
        if (is_array($tempVariations) && count($tempVariations) > 0) {
            $requestData['is_variation_item'] = true;
        }

        $tempPermission = (isset($permissions['variations']) && $permissions['variations']) ||
                          (isset($permissions['additional']) && $permissions['additional']);

        if ($tempPermission && $requestData['is_variation_item']) {

            $additionalData = $listingProduct->getData('additional_data');
            is_string($additionalData) && $additionalData = json_decode($additionalData,true);
            if (isset($additionalData['variations_sets'])) {
                $requestData['variations_sets'] = $additionalData['variations_sets'];
            }

            $requestData['variation'] = $tempVariations;
            $requestData['variation_image'] = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_HelperVariations')
                                                    ->getImagesData($listingProduct,$params);

            if (count($requestData['variation_image']) == 0) {
                unset($requestData['variation_image']);
            }
        }
    }

    //----------------------------------------

    protected function addEpsOrSelfHostedMode(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $additionalData = $listingProduct->getData('additional_data');

        is_string($additionalData) && $additionalData = json_decode($additionalData,true);
        !is_array($additionalData) && $additionalData = array();

        if (isset($additionalData['is_eps_ebay_images_mode'])) {
            $requestData['is_eps_ebay_images_mode'] = $additionalData['is_eps_ebay_images_mode'];
        }
    }

    protected function addIsM2eProListedItemData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $isListedByM2ePro = $listingProduct->getChildObject()->getData('is_m2epro_listed_item');
        !is_null($isListedByM2ePro) && $requestData['is_m2epro_listed_item'] = (int)$isListedByM2ePro;
    }

    // ########################################

    protected function addSellingFormatData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $ebaySellingFormatTemplate Ess_M2ePro_Model_Ebay_Template_SellingFormat */
        $ebaySellingFormatTemplate = $listingProduct->getChildObject()->getEbaySellingFormatTemplate();

        if ($ebaySellingFormatTemplate->isListingTypeFixed()) {
            $requestData['listing_type'] = Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_FIXED;
        } else {
            $requestData['listing_type'] = Ess_M2ePro_Model_Ebay_Template_SellingFormat::EBAY_LISTING_TYPE_AUCTION;
        }

        $requestData['duration'] = $listingProduct->getChildObject()->getDuration();
        $requestData['is_private'] = $ebaySellingFormatTemplate->isPrivateListing();
        $requestData['currency'] = $listingProduct->getMarketplace()->getChildObject()->getCurrency();
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

    protected function addCharityData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $ebaySellingFormatTemplate Ess_M2ePro_Model_Ebay_Template_SellingFormat */
        $ebaySellingFormatTemplate = $listingProduct->getChildObject()->getEbaySellingFormatTemplate();

        $charity = $ebaySellingFormatTemplate->getCharity();
        if (!is_null($charity)) {
            $requestData['charity_id'] = $charity['id'];
            $requestData['charity_percent'] = $charity['percentage'];
        }
    }

    protected function addBestOfferData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed()) {
            return;
        }

        $requestData['bestoffer_mode'] = $listingProduct->getChildObject()
                                                ->getEbaySellingFormatTemplate()->isBestOfferEnabled();

        if ($requestData['bestoffer_mode']) {
            $requestData['bestoffer_accept_price'] = $listingProduct->getChildObject()->getBestOfferAcceptPrice();
            $requestData['bestoffer_reject_price'] = $listingProduct->getChildObject()->getBestOfferRejectPrice();
        }
    }

    // ########################################

    protected function addDescriptionData(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array &$requestData,$permissions = array())
    {
        /** @var $descriptionTemplate Ess_M2ePro_Model_Ebay_Template_Description */
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();

        if (!isset($permissions['title']) || $permissions['title']) {

            $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
            $requestData['title'] = $descriptionTemplate->getTitleResultValue();
            $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

            if (!empty($notFoundAttributes)) {
                $this->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Title'), $notFoundAttributes
                );
            }
        }

        if (!isset($permissions['subtitle']) || $permissions['subtitle']) {

            $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
            $requestData['subtitle'] = $descriptionTemplate->getSubTitleResultValue();
            $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

            if (!empty($notFoundAttributes)) {
                $this->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Subtitle'), $notFoundAttributes
                );
            }
        }

        if (!isset($permissions['description']) || $permissions['description']) {

            $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
            $requestData['description'] = $descriptionTemplate->getDescriptionResultValue();
            $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

            if (!empty($notFoundAttributes)) {
                $this->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__('Description'), $notFoundAttributes
                );
            }
        }
    }

    protected function addConditionData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $descriptionTemplate Ess_M2ePro_Model_Ebay_Template_Description */
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();

        $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
        $tempCondition = $descriptionTemplate->getCondition();
        $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

        if (!empty($notFoundAttributes)) {
            $this->addNotFoundAttributesMessage(
                $listingProduct, Mage::helper('M2ePro')->__('Condition'), $notFoundAttributes
            );
        } else {
            $requestData['item_condition'] = $tempCondition;
        }

        $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
        $requestData['item_condition_note'] = $descriptionTemplate->getConditionNote();
        $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

        if (!empty($notFoundAttributes)) {
            $this->addNotFoundAttributesMessage(
                $listingProduct, Mage::helper('M2ePro')->__('Condition Description'), $notFoundAttributes
            );
        }
    }

    protected function addProductDetailsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $descriptionTemplate Ess_M2ePro_Model_Ebay_Template_Description */
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();

        $requestData['product_details'] = array();
        $tempTypes = array('isbn','epid','upc','ean');

        foreach ($tempTypes as $tempType) {

            $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();
            $tempValue = $descriptionTemplate->getProductDetail($tempType);
            $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

            if (!empty($notFoundAttributes)) {
                $this->addNotFoundAttributesMessage(
                    $listingProduct, Mage::helper('M2ePro')->__(strtoupper($tempType)), $notFoundAttributes
                );
                continue;
            }

            if (!$tempValue) {
                continue;
            }

            $requestData['product_details'][$tempType] = $tempValue;
        }
    }

    protected function addEnhancements(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $descriptionTemplate Ess_M2ePro_Model_Ebay_Template_Description */
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();

        $requestData['hit_counter'] = $descriptionTemplate->getHitCounterType();
        $requestData['listing_enhancements'] = $descriptionTemplate->getEnhancements();
    }

    protected function addImagesData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $descriptionTemplate Ess_M2ePro_Model_Ebay_Template_Description */
        $descriptionTemplate = $listingProduct->getChildObject()->getDescriptionTemplate();

        $descriptionTemplate->getMagentoProduct()->clearNotFoundAttributes();

        $requestData['images'] = array(
            'gallery_type' => $descriptionTemplate->getGalleryType(),
            'images' => $descriptionTemplate->getImagesForEbay(),
            'supersize' => $descriptionTemplate->isUseSupersizeImagesEnabled()
        );

        $notFoundAttributes = $descriptionTemplate->getMagentoProduct()->getNotFoundAttributes();

        if (!empty($notFoundAttributes)) {
            $this->addNotFoundAttributesMessage(
                $listingProduct, Mage::helper('M2ePro')->__('Main Image / Gallery Images'), $notFoundAttributes
            );
        }
    }

    // ########################################

    protected function addPaymentData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $paymentTemplate Ess_M2ePro_Model_Ebay_Template_Payment */
        $paymentTemplate = $listingProduct->getChildObject()->getPaymentTemplate();

        $requestData['payment'] = array();
        $requestData['payment']['methods'] = array();

        $services = $paymentTemplate->getServices(true);

        if ($paymentTemplate->isPayPalEnabled()) {
            $requestData['payment']['methods'][] = 'PayPal';
        }

        foreach ($services as $service) {
            /** @var $service Ess_M2ePro_Model_Ebay_Template_Payment_Service */
            $requestData['payment']['methods'][] = $service->getCodeName();
        }

        if (in_array('PayPal',$requestData['payment']['methods'])) {

            $requestData['payment']['paypal'] = array(
                'email' => $paymentTemplate->getPayPalEmailAddress(),
                'immediate_payment' => $paymentTemplate->isPayPalImmediatePaymentEnabled()
            );
        }
    }

    protected function addReturnData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        /** @var $returnTemplate Ess_M2ePro_Model_Ebay_Template_Return */
        $returnTemplate = $listingProduct->getChildObject()->getReturnTemplate();

        $requestData['return_policy'] = array(
            'accepted'      => $returnTemplate->getAccepted(),
            'option'        => $returnTemplate->getOption(),
            'within'        => $returnTemplate->getWithin(),
            'description'   => $returnTemplate->getDescription(),
            'shippingcost'  => $returnTemplate->getShippingCost(),
            'restockingfee' => $returnTemplate->getRestockingFee()
        );
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

        !empty($nativeRequestData['title']) && $dataForUpdate['online_title'] = $nativeRequestData['title'];
        !empty($nativeRequestData['sku']) && $dataForUpdate['online_sku'] = $nativeRequestData['sku'];

        !is_null($ebayItemsId) && $dataForUpdate['ebay_item_id'] = (int)$ebayItemsId;
        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];

        if (isset($nativeRequestData['category_main_id'])) {
            $dataForUpdate['online_category'] = Mage::helper('M2ePro/Component_Ebay_Category')->getPathById(
                $nativeRequestData['category_main_id'],
                $listingProduct->getMarketplace()->getId()
            );
        }

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

    public function addNotFoundAttributesMessage(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                 $optionTitle, array $attributesCodes)
    {
        $attributesTitles = array();
        foreach ($attributesCodes as $attributeCode) {
            $attributesTitles[] = Mage::helper('M2ePro/Magento_Attribute')
                                        ->getAttributeLabel($attributeCode,
                                                            $listingProduct->getListing()->getStoreId());
        }

        $message = Mage::helper('M2ePro')->__(
            '%s: attribute(s) %s were not found in this product.',
            $optionTitle, implode(',',$attributesTitles)
        );

        $this->addAdditionalWarningMessage($listingProduct,$message);
    }

    public function addAdditionalWarningMessage(Ess_M2ePro_Model_Listing_Product $listingProduct, $message)
    {
        $messages = $listingProduct->getData('__additional_warning_messages__');
        !$messages && $messages = array();
        $messages[] = $message;
        $listingProduct->setData('__additional_warning_messages__',$messages);
    }

    // ########################################
}