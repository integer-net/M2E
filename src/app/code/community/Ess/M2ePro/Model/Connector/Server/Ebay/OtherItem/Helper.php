<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_OtherItem_Helper
{
    // ########################################

    public function getRelistRequestData(Ess_M2ePro_Model_Listing_Other $listingOther, array $params = array())
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
        $requestData['item_id'] = $listingOther->getChildObject()->getItemId();
        //-------------------

        // Get General Info
        //-------------------
        if ($permissions['additional']) {
            $this->addDescriptionData($listingOther,$requestData,array());
            $this->addQtyPriceData($listingOther,$requestData,array());
        }
        //-------------------

        $this->addIsM2eProListedItemData($listingOther,$requestData);

        return $requestData;
    }

    public function updateAfterRelistAction(Ess_M2ePro_Model_Listing_Other $listingOther,
                                            array $nativeRequestData = array(), array $params = array())
    {
        if ($params['ebay_item_id'] != $listingOther->getData('item_id')) {

            $newEbayOldItems = $listingOther->getData('old_items');
            is_null($newEbayOldItems) && $newEbayOldItems = '';
            $newEbayOldItems != '' && $newEbayOldItems .= ',';
            $newEbayOldItems .= $listingOther->getData('item_id');

            $listingOther->addData(array('old_items'=>$newEbayOldItems))->save();

            if ((int)$listingOther->getProductId() > 0) {
                $this->createNewEbayItemsId($listingOther,$params['ebay_item_id']);
            }
        }

        $this->updateProductAfterAction($listingOther,
                                        $nativeRequestData,
                                        $params,
                                        $params['ebay_item_id'],
                                        false);
    }

    //----------------------------------------

    public function getReviseRequestData(Ess_M2ePro_Model_Listing_Other $listingOther, array $params = array())
    {
        // Set permissions
        //-----------------
        $permissions = array(
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

        // Get eBay Item Info
        //-------------------
        $requestData['item_id'] = $listingOther->getChildObject()->getItemId();
        //-------------------

        $this->addDescriptionData($listingOther,$requestData,$permissions);
        $this->addQtyPriceData($listingOther,$requestData,$permissions);

        // Delete title and subtitle when item has bid(s)
        //-------------------
        if (!empty($requestData['title']) || !empty($requestData['subtitle'])) {

            $tempDeleteData = (is_null($listingOther->getChildObject()->getOnlineQtySold())
                                   ? 0
                                   : $listingOther->getChildObject()->getOnlineQtySold() > 0)
                              ||
                              ($listingOther->getChildObject()->getOnlineBids() > 0);

            if (!empty($requestData['title']) && $tempDeleteData) {
                unset($requestData['title']);
            }
            if (!empty($requestData['subtitle']) && $tempDeleteData) {
                unset($requestData['subtitle']);
            }
        }
        //-------------------

        $this->addIsM2eProListedItemData($listingOther,$requestData);

        return $requestData;
    }

    public function updateAfterReviseAction(Ess_M2ePro_Model_Listing_Other $listingOther,
                                            array $nativeRequestData = array(), array $params = array())
    {
        $this->updateProductAfterAction($listingOther,
                                        $nativeRequestData,
                                        $params,
                                        NULL,
                                        true);
    }

    //----------------------------------------

    public function getStopRequestData(Ess_M2ePro_Model_Listing_Other $listingOther, array $params = array())
    {
        $requestData = array();
        $requestData['item_id'] = $listingOther->getChildObject()->getItemId();
        return $requestData;
    }

    public function updateAfterStopAction(Ess_M2ePro_Model_Listing_Other $listingOther,
                                          array $nativeRequestData = array(), array $params = array())
    {
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
        $listingOther->addData($dataForUpdate)->save();
    }

    // ########################################

    protected function addIsM2eProListedItemData(Ess_M2ePro_Model_Listing_Other $listingOther, array &$requestData)
    {
        $requestData['is_m2epro_listed_item'] = Ess_M2ePro_Model_Ebay_Listing_Product::IS_M2EPRO_LISTED_ITEM_NO;
    }

    protected function addDescriptionData(Ess_M2ePro_Model_Listing_Other $listingOther,
                                          array &$requestData, $permissions = array())
    {
        if (!isset($permissions['title']) || $permissions['title']) {
            $temp = $listingOther->getChildObject()->getMappedTitle();
            !is_null($temp) && $requestData['title'] = $temp;
        }

        if (!isset($permissions['subtitle']) || $permissions['subtitle']) {
            $temp = $listingOther->getChildObject()->getMappedSubTitle();
            !is_null($temp) && $requestData['subtitle'] = $temp;
        }

        if (!isset($permissions['description']) || $permissions['description']) {
            $temp = $listingOther->getChildObject()->getMappedDescription();
            !is_null($temp) && $requestData['description'] = $temp;
        }
    }

    protected function addQtyPriceData(Ess_M2ePro_Model_Listing_Other $listingOther,
                                       array &$requestData, $permissions = array())
    {
        if (!isset($permissions['qty']) || $permissions['qty']) {
            $temp = $listingOther->getChildObject()->getMappedQty();
            !is_null($temp) && $requestData['qty'] = $temp;
        }

        if (!isset($permissions['price']) || $permissions['price']) {
            $temp = $listingOther->getChildObject()->getMappedPrice();
            !is_null($temp) && $requestData['price_fixed'] = $temp;
        }
    }

    // ########################################

    protected function createNewEbayItemsId(Ess_M2ePro_Model_Listing_Other $listingOther, $ebayRealItemId)
    {
        $dataForAdd = array(
            'item_id' => (double)$ebayRealItemId,
            'product_id' => (int)$listingOther->getProductId(),
            'store_id' => (int)$listingOther->getChildObject()->getRelatedStoreId()
        );
        return Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save()->getId();
    }

    protected function updateProductAfterAction(Ess_M2ePro_Model_Listing_Other $listingOther,
                                                array $nativeRequestData = array(),
                                                array $params = array(),
                                                $ebayItemId = NULL,
                                                $saveEbayQtySold = false)
    {
        $dataForUpdate = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED
        );

        !is_null($ebayItemId) && $dataForUpdate['item_id'] = $ebayItemId;

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

        if (isset($nativeRequestData['title'])) {
            $dataForUpdate['title'] = $nativeRequestData['title'];
        }

        if ($saveEbayQtySold) {

            $dataForUpdate['online_qty_sold'] = is_null($listingOther->getChildObject()->getOnlineQtySold())
                ? 0 : $listingOther->getChildObject()->getOnlineQtySold();

            if (isset($nativeRequestData['qty'])) {
                $dataForUpdate['online_qty'] = (int)$nativeRequestData['qty'] + (int)$dataForUpdate['online_qty_sold'];
            }

        } else {

            $dataForUpdate['online_qty_sold'] = 0;

            if (isset($nativeRequestData['qty'])) {
                $dataForUpdate['online_qty'] = (int)$nativeRequestData['qty'];
            }
        }

        if (isset($nativeRequestData['price_fixed'])) {
            $dataForUpdate['online_price'] = (float)$nativeRequestData['price_fixed'];
        }

        $listingOther->addData($dataForUpdate)->save();
    }

    // ########################################
}