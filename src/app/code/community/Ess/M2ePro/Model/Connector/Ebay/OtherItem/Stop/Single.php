<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/** @method Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Stop_Response getResponseObject */

class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Stop_Single
    extends Ess_M2ePro_Model_Connector_Ebay_OtherItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','end');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Other_Log::ACTION_STOP_PRODUCT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->otherListing->isStoppable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingOtherMessage($this->otherListing,$message,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $data = $this->getRequestObject()->getData();
        $this->logRequestMessages();

        return $this->buildRequestDataObject($data)->getData();
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return $response;
        }

        if ($response['already_stop']) {

            $this->getResponseObject()->processAlreadyStopped($response);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was already stopped on eBay');
                parent::MESSAGE_TEXT_KEY => 'Item was already stopped on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully stopped');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully stopped',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                   Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        return $response;
    }

    // ########################################
}