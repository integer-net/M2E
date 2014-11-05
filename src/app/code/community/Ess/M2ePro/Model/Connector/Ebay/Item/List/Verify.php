<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_List_Verify
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    // ########################################

    public function process()
    {
        $this->getLogger()->setStoreMode(true);
        return parent::process();
    }

    public function getCustomMessages($type = NULL)
    {
        $messages = array();

        foreach ($this->getLogger()->getStoredMessages() as $message) {
            if (!is_null($type) && $message['type'] != $type) {
                continue;
            }
            $messages[] = $message;
        }

        return $messages;
    }

    //----------------------------------------

    protected function eventBeforeProcess() {}

    protected function eventAfterProcess() {}

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->listingProduct->isListable()) {

            $message = array(
                // M2ePro_TRANSLATIONS
                // Item is listed or not available
                parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
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
        $this->getRequestObject()->clearVariations();
        $data = $this->getRequestObject()->getData();

        $data['verify_call'] = true;

        return $this->buildRequestDataObject($data)->getData();
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if (isset($response['ebay_item_fees']) && is_array($response['ebay_item_fees'])) {
            return $response['ebay_item_fees'];
        }
        return array();
    }

    // ########################################
}