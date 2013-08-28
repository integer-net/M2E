<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_List_Multiple
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_MultipleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','multiple');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        $countListedItems = 0;

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            if (!$listingProduct->isListable()) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->addListingsProductsLogsMessage($listingProduct, $message,
                                                      Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                $countListedItems++;
            }
        }

        if (count($this->listingsProducts) <= $countListedItems) {
            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $requestData = array();

        $tempParams = $this->params;
        $tempParams['logs_action_id'] = $this->logsActionId;
        $tempParams['logs_initiator'] = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN) {
            $tempParams['logs_initiator'] = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
        } else if ($this->params['status_changer'] == Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER) {
            $tempParams['logs_initiator'] = Ess_M2ePro_Model_Log_Abstract::INITIATOR_USER;
        } else {
            $tempParams['logs_initiator'] = Ess_M2ePro_Model_Log_Abstract::INITIATOR_EXTENSION;
        }
        $tempParams['logs_action'] = $this->getListingsLogsCurrentAction();

        $requestData['products'] = array();
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            if ($listingProduct->isListable()) {

                $productVariations = $listingProduct->getVariations(true);

                foreach ($productVariations as $variation) {
                    /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
                    $variation->deleteInstance();
                }

                $requestData['products'][$listingProduct->getId()] =
                    Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->getListRequestData(
                        $listingProduct, $tempParams
                    );
            }
        }

        return $this->nativeRequestData = $requestData;
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if (isset($response['result'])) {

            foreach ($response['result'] as $tempIdProduct=>$tempResultProduct) {

                $listingProductInArray = NULL;
                foreach ($this->listingsProducts as $listingProduct) {
                    /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                    if ($tempIdProduct == $listingProduct->getId()) {
                        $listingProductInArray = $listingProduct;
                        break;
                    }
                }

                if (is_null($listingProductInArray)) {
                    continue;
                }

                $resultSuccess = true;
                if (isset($tempResultProduct['messages'])){
                    foreach ($tempResultProduct['messages'] as $message) {
                        if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                            $resultSuccess = false;
                            break;
                        }
                    }
                }

                if ($resultSuccess) {

                    $tempParams = array(
                        'ebay_item_id' => $tempResultProduct['ebay_item_id'],
                        'start_date_raw' => $tempResultProduct['ebay_start_date_raw'],
                        'end_date_raw' => $tempResultProduct['ebay_end_date_raw'],
                        'is_eps_ebay_images_mode' => $tempResultProduct['is_eps_ebay_images_mode'],
                        'ebay_item_fees' => $tempResultProduct['ebay_item_fees']
                    );

                    Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')->updateAfterListAction(
                        $listingProductInArray,
                        $this->nativeRequestData['products'][$listingProductInArray->getId()],
                        array_merge($this->params,$tempParams)
                    );

                    $message = array(
                        // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
                        parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
                        parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
                    );

                    $this->addListingsProductsLogsMessage($listingProductInArray, $message,
                                                          Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
                }
            }
        }

        return $response;
    }

    // ########################################
}