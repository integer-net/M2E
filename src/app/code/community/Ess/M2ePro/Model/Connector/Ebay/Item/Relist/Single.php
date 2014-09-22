<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Relist_Single
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','relist');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_RELIST_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->listingProduct->isRelistable()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // The item either is listed, or not listed yet or not available
                parent::MESSAGE_TEXT_KEY => 'The item either is listed, or not listed yet or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if ($this->listingProduct->isLockedObject(NULL) ||
            $this->listingProduct->isLockedObject('in_action')) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Another action is being processed. Try again when the action is completed.
                parent::MESSAGE_TEXT_KEY => 'Another action is being processed. '
                                           .'Try again when the action is completed.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if(!$this->listingProduct->getChildObject()->isSetCategoryTemplate()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Categories settings are not set
                parent::MESSAGE_TEXT_KEY => 'Categories settings are not set',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
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

            foreach ($this->messages as $message) {
                $this->checkAndLogNotAccessedError($message);
                $this->checkAndLogConditionError($message);
            }

            return $response;
        }

        $params = array(
            'is_images_upload_error' => $this->isImagesUploadFailed($this->messages)
        );

        if ($response['already_active']) {

            $this->getResponseObject()->processAlreadyActive($response, $params);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was already started on eBay
                parent::MESSAGE_TEXT_KEY => 'Item was already started on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response, $params);

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item was successfully relisted
                parent::MESSAGE_TEXT_KEY => 'Item was successfully relisted',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
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

        $this->getLogger()->logListingProductMessage($this->listingProduct, $message);
    }

    // ########################################

    private function checkAndLogNotAccessedError($message)
    {
        if ($message[parent::MESSAGE_SENDER_KEY] != 'component' ||
            (int)$message[parent::MESSAGE_CODE_KEY] != 17) {
            return;
        }

        $this->getResponseObject()->markAsNotListedItem();

        $message = array(
            // M2ePro_TRANSLATIONS
            // This item cannot be accessed on eBay. M2E set Not Listed status.
            parent::MESSAGE_TEXT_KEY => 'This item cannot be accessed on eBay. M2E set Not Listed status.',
            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
        );

        $this->getLogger()->logListingProductMessage(
            $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    private function checkAndLogConditionError($message)
    {
        if ($message[parent::MESSAGE_SENDER_KEY] != 'component' ||
            (int)$message[parent::MESSAGE_CODE_KEY] != 21916884) {
            return;
        }

        $this->getResponseObject()->markAsNeedUpdateConditionData();

        $message = array(
            parent::MESSAGE_TEXT_KEY => 'M2E was not able to send Condition on eBay. Please try to perform the Relist'.
                                        ' action once more.',
            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
        );

        $this->getLogger()->logListingProductMessage(
            $this->listingProduct, $message, Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
        );
    }

    // ########################################
}