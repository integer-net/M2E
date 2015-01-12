<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Product_Helper
{
    // ########################################

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

        if (empty($requestData['general_id'])) {

            $tempGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();

            if (empty($tempGeneralId)) {
                throw new Exception('You must specify General ID if you want to add new inventory item.');
            }

            $requestData['general_id'] = $tempGeneralId;
        }

        if (empty($requestData['general_id_type'])) {

            $tempGeneralIdType = $listingProduct->getChildObject()->getAddingGeneralIdType();

            if (empty($tempGeneralIdType)) {
                throw new Exception('You must specify General ID Type if you want to add new inventory item.');
            }

            $requestData['general_id_type'] = $tempGeneralIdType;
        }

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Add New Play Item
        //---------------------
        $this->createNewPlayItem($listingProduct,$nativeRequestData['sku']);
        //---------------------

        // Save additional info
        //---------------------
        $dataForUpdate = array(
            'sku' => $nativeRequestData['sku'],
            'general_id' => $nativeRequestData['general_id'],
            'general_id_type' => $nativeRequestData['general_id_type']
        );

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

            $requestData['condition'] = $listingProduct->getChildObject()->getAddingCondition();
            if (is_null($requestData['condition'])) {
                unset($requestData['condition']);
            }

            $requestData['condition_note'] = $listingProduct->getChildObject()->getAddingConditionNote();
            if (is_null($requestData['condition_note'])) {
                unset($requestData['condition_note']);
            }
        }

        if ($permissions['qty']) {
            $requestData['qty'] = $listingProduct->getChildObject()->getQty();
        }

        if ($permissions['price']) {

            $requestData['dispatch_to'] = $listingProduct->getChildObject()->getAddingDispatchTo();
            if (is_null($requestData['dispatch_to'])) {
                unset($requestData['dispatch_to']);
            }

            $requestData['dispatch_from'] = $listingProduct->getChildObject()->getAddingDispatchFrom();
            if (is_null($requestData['dispatch_from'])) {
                unset($requestData['dispatch_from']);
            }

            $requestData['price_gbr'] = $listingProduct->getChildObject()->getPriceGbr(true);
            $requestData['price_euro'] = $listingProduct->getChildObject()->getPriceEuro(true);
            $requestData['shipping_price_gbr'] = $listingProduct->getChildObject()->getShippingPriceGbr();
            $requestData['shipping_price_euro'] = $listingProduct->getChildObject()->getShippingPriceEuro();
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

        if (!isset($requestData['general_id'])) {
            $requestData['general_id'] = $listingProduct->getChildObject()->getGeneralId();
        }

        if (!isset($requestData['general_id_type'])) {
            $requestData['general_id_type'] = $listingProduct->getChildObject()->getGeneralIdType();
        }

        if (!isset($requestData['price_gbr'])) {
            $requestData['price_gbr'] = $listingProduct->getChildObject()->getOnlinePriceGbr();
        }

        if (!isset($requestData['price_euro'])) {
            $requestData['price_euro'] = $listingProduct->getChildObject()->getOnlinePriceEuro();
        }

        if (!isset($requestData['shipping_price_gbr'])) {
            $requestData['shipping_price_gbr'] = $listingProduct->getChildObject()->getOnlineShippingPriceGbr();
        }

        if (!isset($requestData['shipping_price_euro'])) {
            $requestData['shipping_price_euro'] = $listingProduct->getChildObject()->getOnlineShippingPriceEuro();
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

        if (!isset($requestData['dispatch_to'])) {
            $requestData['dispatch_to'] = $listingProduct->getChildObject()->getDispatchTo();
        }

        if (!isset($requestData['dispatch_from'])) {
            $requestData['dispatch_from'] = $listingProduct->getChildObject()->getDispatchFrom();
        }
    }

    // ########################################

    protected function createNewPlayItem(Ess_M2ePro_Model_Listing_Product $listingProduct, $sku)
    {
        $dataForAdd = array(
            'account_id' => (int)$listingProduct->getListing()->getAccountId(),
            'marketplace_id' => (int)$listingProduct->getListing()->getMarketplaceId(),
            'sku' => $sku,
            'product_id' =>(int)$listingProduct->getProductId(),
            'store_id' => (int)$listingProduct->getListing()->getStoreId()
        );

        return Mage::getModel('M2ePro/Play_Item')->setData($dataForAdd)->save()->getId();
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

        if (isset($nativeRequestData['price_gbr'])) {
            $dataForUpdate['online_price_gbr'] = (float)$nativeRequestData['price_gbr'];
        }
        if (isset($nativeRequestData['price_euro'])) {
            $dataForUpdate['online_price_euro'] = (float)$nativeRequestData['price_euro'];
        }

        if (isset($nativeRequestData['shipping_price_gbr'])) {
            $dataForUpdate['online_shipping_price_gbr'] = (float)$nativeRequestData['shipping_price_gbr'];
        }
        if (isset($nativeRequestData['shipping_price_euro'])) {
            $dataForUpdate['online_shipping_price_euro'] = (float)$nativeRequestData['shipping_price_euro'];
        }

        if (isset($nativeRequestData['condition'])) {
            $dataForUpdate['condition'] = (string)$nativeRequestData['condition'];
        }

        if (isset($nativeRequestData['condition_note'])) {
            $dataForUpdate['condition_note'] = (string)$nativeRequestData['condition_note'];
        }

        if (isset($nativeRequestData['dispatch_to'])) {
            $dataForUpdate['dispatch_to'] = (string)$nativeRequestData['dispatch_to'];
        }

        if (isset($nativeRequestData['dispatch_from'])) {
            $dataForUpdate['dispatch_from'] = (string)$nativeRequestData['dispatch_from'];
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}