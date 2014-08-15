<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Buy_Product_Helper
{
    // ########################################

    public function getNewSkuRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        /* @var $templateNewProductSourceInstance Ess_M2ePro_Model_Buy_Template_NewProduct_Source */
        $templateNewProductSourceInstance = $listingProduct->getChildObject()->getTemplateNewProductSource();

        $requestData['core'] = $templateNewProductSourceInstance->getCoreData();
        $requestData['attributes'] = $templateNewProductSourceInstance->getAttributesData();

        return $requestData;
    }

    public function updateAfterNewSkuAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Save additional info
        //---------------------
        $dataForUpdate = array(
            'general_id' => $params['general_id']
        );

        $listingProduct->addData($dataForUpdate)->save();
        //---------------------
    }

    //----------------------------------------

    public function getListRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = $this->getReviseRequestData($listingProduct,$params);

        if (empty($requestData['sku'])) {

            $tempSku = $listingProduct->getData('sku');

            if (empty($tempSku)) {
                throw new Exception('You must specify SKU if you want to add new inventory item.');
            }

            $requestData['sku'] = $tempSku;
        }

        if (empty($requestData['product_id'])) {

            $tempGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();

            if (empty($tempGeneralId)) {
                throw new Exception('You must specify General ID if you want to add new inventory item.');
            }

            $requestData['product_id'] = $tempGeneralId;
            $requestData['product_id_type'] = $listingProduct->getListing()->getChildObject()->getGeneralIdMode();

            // prepare to the server format
            $requestData['product_id_type']--;
        }

        if (empty($requestData['condition'])) {

            $tempCondition = $listingProduct->getChildObject()->getAddingCondition();

            if (empty($tempCondition)) {
                throw new Exception('You must specify Condition if you want to add new inventory item.');
            }

            $requestData['condition'] = $tempCondition;
        }

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Add New Buy Item
        //---------------------
        $this->createNewBuyItem($listingProduct,$nativeRequestData['sku']);
        //---------------------

        // Save additional info
        //---------------------
        $dataForUpdate = array(
            'sku' => $nativeRequestData['sku'],
            'condition' => $nativeRequestData['condition']
        );

        if (($nativeRequestData['product_id_type']+1) ==
            Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_GENERAL_ID) {

            $dataForUpdate['general_id'] = $nativeRequestData['product_id'];
        }

        $listingProduct->addData($dataForUpdate);
        //---------------------

        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        Mage::helper('M2ePro')->getCurrentGmtDate());
        //---------------------
    }

    //----------------------------------------

    public function getRelistRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        return $this->getReviseRequestData($listingProduct,$params);
    }

    public function updateAfterRelistAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params,
                                        Mage::helper('M2ePro')->getCurrentGmtDate());
        //---------------------
    }

    //----------------------------------------

    public function getReviseRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();
        $permissions = $this->getPreparedPermissions($params);

        // Get SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        if ($permissions['general']) {

            $requestData['condition_note'] = $listingProduct->getChildObject()->getAddingConditionNote();
            if (is_null($requestData['condition_note'])) {
                unset($requestData['condition_note']);
            }

            $addingShippingStandardRate = $listingProduct->getChildObject()->getAddingShippingStandardRate();
            $requestData['shipping_standard_rate'] = $addingShippingStandardRate;
            if (is_null($requestData['shipping_standard_rate'])) {
                unset($requestData['shipping_standard_rate']);
            }

            $addingShippingExpeditedMode = $listingProduct->getChildObject()->getAddingShippingExpeditedMode();
            $requestData['shipping_expedited_mode'] = $addingShippingExpeditedMode;
            if (is_null($requestData['shipping_expedited_mode'])) {
                unset($requestData['shipping_expedited_mode']);
            }

            $addingShippingExpeditedRate = $listingProduct->getChildObject()->getAddingShippingExpeditedRate();
            $requestData['shipping_expedited_rate'] = $addingShippingExpeditedRate;
            if (is_null($requestData['shipping_expedited_rate'])) {
                unset($requestData['shipping_expedited_rate']);
            }

            $addingShippingOneDayMode = $listingProduct->getChildObject()->getAddingShippingOneDayMode();
            $requestData['shipping_one_day_mode'] = $addingShippingOneDayMode;
            if (is_null($requestData['shipping_one_day_mode'])) {
                unset($requestData['shipping_one_day_mode']);
            }

            $addingShippingOneDayRate = $listingProduct->getChildObject()->getAddingShippingOneDayRate();
            $requestData['shipping_one_day_rate'] = $addingShippingOneDayRate;
            if (is_null($requestData['shipping_one_day_rate'])) {
                unset($requestData['shipping_one_day_rate']);
            }

            $addingShippingTwoDayMode = $listingProduct->getChildObject()->getAddingShippingTwoDayMode();
            $requestData['shipping_two_day_mode'] = $addingShippingTwoDayMode;
            if (is_null($requestData['shipping_two_day_mode'])) {
                unset($requestData['shipping_two_day_mode']);
            }

            $addingShippingTwoDayRate = $listingProduct->getChildObject()->getAddingShippingTwoDayRate();
            $requestData['shipping_two_day_rate'] = $addingShippingTwoDayRate;
            if (is_null($requestData['shipping_two_day_rate'])) {
                unset($requestData['shipping_two_day_rate']);
            }
        }

        if ($permissions['qty']) {
            $requestData['qty'] = $listingProduct->getChildObject()->getQty();
        }

        if ($permissions['price']) {
            $requestData['price'] = $listingProduct->getChildObject()->getPrice();
        }
        //-------------------

        $this->checkRequiredRequestParams($listingProduct,$requestData);

        return $requestData;
    }

    public function updateAfterReviseAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $this->updateProductAfterAction($listingProduct,
                                        $nativeRequestData,
                                        $params);
        //---------------------
    }

    //----------------------------------------

    public function getStopRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        $requestData['qty'] = 0;
        //-------------------

        $this->checkRequiredRequestParams($listingProduct,$requestData);

        return $requestData;
    }

    public function updateAfterStopAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            'end_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'online_qty' => 0,
            'ignore_next_inventory_synch' => 1
        );

        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];

        $listingProduct->addData($dataForUpdate)->save();
        //---------------------
    }

    // ########################################

    protected function getPreparedPermissions(array $params = array())
    {
        $permissions = array(
            'general'=>true,
            'qty'=>true,
            'price'=>true
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

        return $permissions;
    }

    protected function isAllPermissionsEnabled(array $permissions = array())
    {
        foreach ($permissions as $key => $value) {
            if (!$value) {
                return false;
            }
        }
        return true;
    }

    protected function checkRequiredRequestParams(Ess_M2ePro_Model_Listing_Product $listingProduct, &$requestData)
    {
        if (!isset($requestData['sku'])) {
            $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        }

        if (!isset($requestData['product_id'])) {
            $requestData['product_id'] = $listingProduct->getChildObject()->getGeneralId();
        }

        if (!isset($requestData['product_id_type'])) {
            $requestData['product_id_type'] = 0; // BUY SKU
        }

        if (!isset($requestData['price'])) {
            $requestData['price'] = $listingProduct->getChildObject()->getOnlinePrice();
        }

        if (!isset($requestData['qty'])) {
            $requestData['qty'] = $listingProduct->getChildObject()->getOnlineQty();
        }

        if (!isset($requestData['condition'])) {
            $requestData['condition'] = $listingProduct->getChildObject()->getCondition();
        }

        if (!isset($requestData['condition_note'])) {
            $requestData['condition_note'] = $listingProduct->getChildObject()->getConditionNote();
        }

        if (!isset($requestData['shipping_standard_rate'])) {
            $requestData['shipping_standard_rate'] = $listingProduct->getChildObject()->getShippingStandardRate();
            is_null($requestData['shipping_standard_rate']) && $requestData['shipping_standard_rate'] = '';
        }

        if (!isset($requestData['shipping_expedited_mode'])) {
            $requestData['shipping_expedited_mode'] = $listingProduct->getChildObject()->getShippingExpeditedMode();
            is_null($requestData['shipping_expedited_mode']) && $requestData['shipping_expedited_mode'] = 0;
        }

        if (!isset($requestData['shipping_expedited_rate'])) {
            $requestData['shipping_expedited_rate'] = $listingProduct->getChildObject()->getShippingExpeditedRate();
            is_null($requestData['shipping_expedited_rate']) && $requestData['shipping_expedited_rate'] = '';
        }

        if (!isset($requestData['shipping_one_day_mode'])) {
            $requestData['shipping_one_day_mode'] = '';
        }

        if (!isset($requestData['shipping_one_day_rate'])) {
            $requestData['shipping_one_day_rate'] = '';
        }

        if (!isset($requestData['shipping_two_day_mode'])) {
            $requestData['shipping_two_day_mode'] = '';
        }

        if (!isset($requestData['shipping_two_day_rate'])) {
            $requestData['shipping_two_day_rate'] = '';
        }
    }

    // ########################################

    protected function createNewBuyItem(Ess_M2ePro_Model_Listing_Product $listingProduct, $sku)
    {
        $dataForAdd = array(
            'account_id' => (int)$listingProduct->getListing()->getAccountId(),
            'marketplace_id' => (int)$listingProduct->getListing()->getMarketplaceId(),
            'sku' => $sku,
            'product_id' =>(int)$listingProduct->getProductId(),
            'store_id' => (int)$listingProduct->getListing()->getStoreId()
        );

        if ($listingProduct->getChildObject()->isVariationsReady()) {

            $variations = $listingProduct->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);
            $options = $variation->getOptions();

            $dataForAdd['variation_options'] = array();
            foreach ($options as $optionData) {
                $dataForAdd['variation_options'][$optionData['attribute']] = $optionData['option'];
            }
            $dataForAdd['variation_options'] = json_encode($dataForAdd['variation_options']);
        }

        return Mage::getModel('M2ePro/Buy_Item')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                array $nativeRequestData = array(),
                                                array $params = array(),
                                                $startDate = false)
    {
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ignore_next_inventory_synch' => 1
        );

        if ($this->isAllPermissionsEnabled($this->getPreparedPermissions($params['params']))) {
            $dataForUpdate['synch_status'] = Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK;
            $dataForUpdate['synch_reasons'] = NULL;
        }

        isset($params['status_changer']) && $dataForUpdate['status_changer'] = (int)$params['status_changer'];
        $startDate !== false && $dataForUpdate['start_date'] = $startDate;

        if (isset($nativeRequestData['qty'])) {

            $dataForUpdate['online_qty'] = (int)$nativeRequestData['qty'];

            if ((int)$dataForUpdate['online_qty'] > 0) {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
                $dataForUpdate['end_date'] = NULL;
            } else {
                $dataForUpdate['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                $dataForUpdate['end_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
            }
        }

        if (isset($nativeRequestData['price'])) {
            $dataForUpdate['online_price'] = (float)$nativeRequestData['price'];
        }

        if (isset($nativeRequestData['condition_note'])) {
            $dataForUpdate['condition_note'] = (string)$nativeRequestData['condition_note'];
        }

        if (isset($nativeRequestData['shipping_standard_rate'])) {
            $dataForUpdate['shipping_standard_rate'] = $nativeRequestData['shipping_standard_rate'];
            $dataForUpdate['shipping_standard_rate'] === '' && $dataForUpdate['shipping_standard_rate'] = NULL;
        }

        if (isset($nativeRequestData['shipping_expedited_mode'])) {
            $dataForUpdate['shipping_expedited_mode'] = (int)$nativeRequestData['shipping_expedited_mode'];
        }

        if (isset($nativeRequestData['shipping_expedited_rate'])) {
            $dataForUpdate['shipping_expedited_rate'] = $nativeRequestData['shipping_expedited_rate'];
            $dataForUpdate['shipping_expedited_rate'] === '' && $dataForUpdate['shipping_expedited_rate'] = NULL;
        }

        if (isset($nativeRequestData['shipping_one_day_mode'])) {
            $dataForUpdate['shipping_one_day_mode'] = $nativeRequestData['shipping_one_day_mode'];
            $dataForUpdate['shipping_one_day_mode'] === '' && $dataForUpdate['shipping_one_day_mode'] = NULL;
        }

        if (isset($nativeRequestData['shipping_one_day_rate'])) {
            $dataForUpdate['shipping_one_day_rate'] = $nativeRequestData['shipping_one_day_rate'];
            $dataForUpdate['shipping_one_day_rate'] === '' && $dataForUpdate['shipping_one_day_rate'] = NULL;
        }

        if (isset($nativeRequestData['shipping_two_day_mode'])) {
            $dataForUpdate['shipping_two_day_mode'] = $nativeRequestData['shipping_two_day_mode'];
            $dataForUpdate['shipping_two_day_mode'] === '' && $dataForUpdate['shipping_two_day_mode'] = NULL;
        }

        if (isset($nativeRequestData['shipping_two_day_rate'])) {
            $dataForUpdate['shipping_two_day_rate'] = $nativeRequestData['shipping_two_day_rate'];
            $dataForUpdate['shipping_two_day_rate'] === '' && $dataForUpdate['shipping_two_day_rate'] = NULL;
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}