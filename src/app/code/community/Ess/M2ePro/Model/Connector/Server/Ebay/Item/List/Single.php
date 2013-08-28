<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_List_Single
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    // ########################################

    protected function validateNeedRequestSend()
    {
        if (!$this->listingProduct->isListable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->addListingsProductsLogsMessage($this->listingProduct,$message,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $productVariations = $this->listingProduct->getVariations(true);

        foreach ($productVariations as $variation) {
           /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
           $variation->deleteInstance();
        }

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

        return $this->nativeRequestData = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                                                ->getListRequestData($this->listingProduct,$tempParams);
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType != parent::MESSAGE_TYPE_ERROR) {

            $tempParams = array(
                'ebay_item_id' => $response['ebay_item_id'],
                'start_date_raw' => $response['ebay_start_date_raw'],
                'end_date_raw' => $response['ebay_end_date_raw'],
                'is_eps_ebay_images_mode' => $response['is_eps_ebay_images_mode'],
                'ebay_item_fees' => $response['ebay_item_fees']
            );

            Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper')
                                ->updateAfterListAction($this->listingProduct, $this->nativeRequestData,
                                                        array_merge($this->params,$tempParams));

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );

            $this->addListingsProductsLogsMessage($this->listingProduct, $message,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }

        return $response;
    }

    // ########################################
}