<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Product_Helper
{
    const LIST_TYPE_GENERAL_ID = 1;
    const LIST_TYPE_WORLDWIDE_ID = 2;
    const LIST_TYPE_TEMPLATE_NEW_PRODUCT = 3;
    const LIST_TYPE_TEMPLATE_NEW_PRODUCT_WORLDWIDE_ID = 4;

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

        $type = $params['list_types'][$listingProduct->getId()];

        if ($type == self::LIST_TYPE_GENERAL_ID) {

            $tempGeneralId = $listingProduct->getChildObject()->getGeneralId();
            empty($tempGeneralId) && $tempGeneralId = $listingProduct->getChildObject()->getAddingGeneralId();

            if (empty($tempGeneralId)) {
                throw new Exception(
                    'ASIN/ISBN is empty.'
                );
            }

            $requestData['product_id'] = $tempGeneralId;
            $requestData['product_id_type'] = Mage::helper('M2ePro/Component_Amazon')->isASIN($tempGeneralId) ?
                                              'ASIN' : 'ISBN';
            return $requestData;
        }

        if ($type == self::LIST_TYPE_WORLDWIDE_ID) {

            $tempWorldwideId = $listingProduct->getChildObject()->getWorldwideId();
            empty($tempWorldwideId) && $tempWorldwideId = $listingProduct->getChildObject()->getAddingWorldwideId();

            if (empty($tempWorldwideId)) {
                throw new Exception(
                    'UPC/EAN is empty.'
                );
            }

            $requestData['product_id'] = $tempWorldwideId;
            $requestData['product_id_type'] = Mage::helper('M2ePro')->isUPC($tempWorldwideId) ?
                                              'UPC' : 'EAN';
            return $requestData;
        }

        if ($type == self::LIST_TYPE_TEMPLATE_NEW_PRODUCT_WORLDWIDE_ID) {

            $tempWorldwideId = $listingProduct->getChildObject()->getTemplateNewProductSource()->getWorldWideId();

            if (empty($tempWorldwideId)) {
                throw new Exception(
                    'UPC/EAN is empty.'
                );
            }

            $requestData['product_id'] = $tempWorldwideId;
            $requestData['product_id_type'] = Mage::helper('M2ePro')->isUPC($tempWorldwideId) ?
                                              'UPC' : 'EAN';
            return $requestData;
        }

        if ($type == self::LIST_TYPE_TEMPLATE_NEW_PRODUCT) {

            /* @var $templateNewProductSourceInstance Ess_M2ePro_Model_Amazon_Template_NewProduct_Source */
            $templateNewProductSourceInstance = $listingProduct->getChildObject()->getTemplateNewProductSource();

            $tempWorldwideId = $templateNewProductSourceInstance->getWorldWideId();

            if (!empty($tempWorldwideId)) {
                $requestData['product_id'] = $tempWorldwideId;
                $requestData['product_id_type'] = Mage::helper('M2ePro')->isUPC($tempWorldwideId) ?
                                                  'UPC' : 'EAN';
            }

            $requestData['number_of_items'] = $templateNewProductSourceInstance->getNumberOfItems();
            if (is_null($requestData['number_of_items'])) {
                unset($requestData['number_of_items']);
            }

            $requestData['item_package_quantity'] = $templateNewProductSourceInstance->getItemPackageQuantity();
            if (is_null($requestData['item_package_quantity'])) {
                unset($requestData['item_package_quantity']);
            }

            $requestData['product_data'] = $templateNewProductSourceInstance->getProductData();
            $requestData['description_data'] = $templateNewProductSourceInstance->getDescriptionData();
            $requestData['images_data'] = $templateNewProductSourceInstance->getImagesData();

            if (is_null($requestData['description_data']['package_weight'])) {
                unset(
                    $requestData['description_data']['package_weight'],
                    $requestData['description_data']['package_weight_unit_of_measure']
                );
            }

            if (is_null($requestData['description_data']['shipping_weight'])) {
                unset(
                    $requestData['description_data']['shipping_weight'],
                    $requestData['description_data']['shipping_weight_unit_of_measure']
                );
            }

            $requestData['registered_parameter'] = $templateNewProductSourceInstance->getRegisteredParameter();

            if (empty($requestData['product_id']) && empty($requestData['registered_parameter'])) {
                throw new Exception('UPC/EAN or registered parameter is required.');
            }

            return $requestData;
        }

        return $requestData;
    }

    public function updateAfterListAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                          array $nativeRequestData = array(), array $params = array())
    {
        // Add New Amazon Item
        //---------------------
        $this->createNewAmazonItem($listingProduct,$nativeRequestData['sku']);
        //---------------------

        // Save additional info
        //---------------------
        $dataForUpdate = array(
            'sku' => $nativeRequestData['sku']
        );

        if (!empty($nativeRequestData['product_id']) &&
            !empty($nativeRequestData['product_id_type'])) {

            if (in_array($nativeRequestData['product_id_type'],array('ASIN','ISBN'))) {

                $dataForUpdate['general_id'] = $nativeRequestData['product_id'];

                $isIsbnGeneralId = (int)Mage::helper('M2ePro')->isISBN($nativeRequestData['product_id']);
                $dataForUpdate['is_isbn_general_id'] = $isIsbnGeneralId;

            } else if (in_array($nativeRequestData['product_id_type'],array('UPC','EAN'))) {

                $dataForUpdate['worldwide_id'] = $nativeRequestData['product_id'];

                $isUpcWorldwideId = (int)Mage::helper('M2ePro')->isUPC($nativeRequestData['product_id']);
                $dataForUpdate['is_upc_worldwide_id'] = $isUpcWorldwideId;
            }
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

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        $permissions['general'] && $this->addDetailsData($listingProduct,$requestData);

        if ($permissions['qty']) {
            $this->addQtyData($listingProduct,$requestData);
        }

        if ($permissions['price']) {
            $this->addPriceData($listingProduct,$requestData);
        }
        //-------------------

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

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        // Get Main Data
        //-------------------
        $requestData['qty'] = 0;
        //-------------------

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

    //----------------------------------------

    public function getDeleteRequestData(Ess_M2ePro_Model_Listing_Product $listingProduct, array $params = array())
    {
        $requestData = array();

        // Get Amazon SKU Info
        //-------------------
        $requestData['sku'] = $listingProduct->getChildObject()->getSku();
        //-------------------

        return $requestData;
    }

    public function updateAfterDeleteAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                            array $nativeRequestData = array(), array $params = array())
    {
        // Update Listing Product
        //---------------------
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'end_date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'online_qty' => 0
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

    // ########################################

    protected function addQtyData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['qty'] = $listingProduct->getChildObject()->getQty();

        $requestData['handling_time'] = $listingProduct->getChildObject()->getHandlingTime();
        if (empty($requestData['handling_time'])) {
            unset($requestData['handling_time']);
        }

        $requestData['restock_date'] = $listingProduct->getChildObject()->getRestockDate();
        if (empty($requestData['restock_date'])) {
            unset($requestData['restock_date']);
        }
    }

    protected function addPriceData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['price'] = $listingProduct->getChildObject()->getPrice();

        $salePriceInfo = $listingProduct->getChildObject()->getSalePriceInfo();
        $requestData['sale_price'] = $salePriceInfo['price'];

        if (is_null($requestData['sale_price'])) {
            unset($requestData['sale_price']);
        } else if ($requestData['sale_price'] > 0) {
            $requestData['sale_price_start_date'] = $salePriceInfo['start_date'];
            $requestData['sale_price_end_date'] = $salePriceInfo['end_date'];
        }
    }

    protected function addDetailsData(Ess_M2ePro_Model_Listing_Product $listingProduct, array &$requestData)
    {
        $requestData['condition'] = $listingProduct->getChildObject()->getCondition();

        if (is_null($requestData['condition'])) {
            unset($requestData['condition']);
        } else {
            $requestData['condition_note'] = $listingProduct->getChildObject()->getConditionNote();
            if (is_null($requestData['condition_note'])) {
                unset($requestData['condition_note']);
            }
        }
    }

    // ########################################

    protected function createNewAmazonItem(Ess_M2ePro_Model_Listing_Product $listingProduct, $sku)
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

        return Mage::getModel('M2ePro/Amazon_Item')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                                array $nativeRequestData = array(),
                                                array $params = array(),
                                                $startDate = false)
    {
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'is_afn_channel' => Ess_M2ePro_Model_Amazon_Listing_Product::IS_AFN_CHANNEL_NO,
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

            $dataForUpdate['online_sale_price'] = NULL;
            $dataForUpdate['online_sale_price_start_date'] = NULL;
            $dataForUpdate['online_sale_price_end_date'] = NULL;

            if (isset($nativeRequestData['sale_price'])) {
                $salePrice = (float)$nativeRequestData['sale_price'];
                if ($salePrice > 0) {
                    $dataForUpdate['online_sale_price'] = $salePrice;
                    $dataForUpdate['online_sale_price_start_date'] = $nativeRequestData['sale_price_start_date'];
                    $dataForUpdate['online_sale_price_end_date'] = $nativeRequestData['sale_price_end_date'];
                } else {
                    $dataForUpdate['online_sale_price'] = 0;
                }
            }
        }

        $listingProduct->addData($dataForUpdate)->save();
    }

    // ########################################
}