<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/** @method Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Relist_Response getResponseObject */

class Ess_M2ePro_Model_Connector_Ebay_OtherItem_Relist_Single
    extends Ess_M2ePro_Model_Connector_Ebay_OtherItem_Abstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','relist');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Other_Log::ACTION_RELIST_PRODUCT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->otherListing->isRelistable()) {

            $message = array(
                // ->__('The item either is listed or not available');
                parent::MESSAGE_TEXT_KEY => 'The item either is listed or not available',
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

        /** @var $responseObject Ess_M2ePro_Model_Ebay_Listing_Other_Action_Type_Relist_Response*/
        $responseObject = $this->getResponseObject();

        if ($response['already_active']) {

            $responseObject->processAlreadyActive($response);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was already started on eBay');
                parent::MESSAGE_TEXT_KEY => 'Item was already started on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully relisted');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully relisted',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingOtherMessage($this->otherListing, $message,
                                                   Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        return $response;
    }

    // ########################################

    protected function processResponseInfo($responseInfo)
    {
        try {
            parent::processResponseInfo($responseInfo);
        } catch (Exception $exception) {

            if (strpos($exception->getMessage(), 'code:34') === false ||
                $this->account->getChildObject()->isModeSandbox()) {
                throw $exception;
            }

            $this->processAsPotentialDuplicate();
        }
    }

    private function processAsPotentialDuplicate()
    {
        $this->getResponseObject()->markAsPotentialDuplicate();

        $message = array(
            parent::MESSAGE_TEXT_KEY => 'An error occured while listing the item. '.
                                'The item has been blocked. The next M2E Synchronization will resolve the problem.',
            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
        );

        $this->getLogger()->logListingOtherMessage($this->otherListing, $message);
    }

    // ########################################
}