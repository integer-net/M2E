<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Item_List_Verify
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Item_SingleAbstract
{
    protected $customMessages = array();

    // ########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getListingsLogsCurrentAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_UNKNOWN;
    }

    //----------------------------------------

    protected function updateOrLockListing() {}

    protected function checkUnlockListing() {}

    //----------------------------------------

    protected function addBaseListingsLogsMessage($listingProduct,
                                                  array $message,
                                                  $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM)
    {
        if (!isset($message[parent::MESSAGE_TEXT_KEY]) || $message[parent::MESSAGE_TEXT_KEY] == '') {
            return;
        }

        $text = $message[parent::MESSAGE_TEXT_KEY];

        if (!isset($message[parent::MESSAGE_TYPE_KEY]) || $message[parent::MESSAGE_TYPE_KEY] == '') {
            return;
        }

        switch ($message[parent::MESSAGE_TYPE_KEY]) {
            case parent::MESSAGE_TYPE_ERROR:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                    $this->setStatus(self::STATUS_ERROR);
                break;
            case parent::MESSAGE_TYPE_WARNING:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
                    $this->setStatus(self::STATUS_WARNING);
                break;
            case parent::MESSAGE_TYPE_SUCCESS:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            case parent::MESSAGE_TYPE_NOTICE:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE;
                    $this->setStatus(self::STATUS_SUCCESS);
                break;
            default:
                    $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
                    $this->setStatus(self::STATUS_ERROR);
                break;
        }

        $this->customMessages[] = array(
            'type' => $type,
            'text' => $text
        );
    }

    public function getCustomMessages($type = NULL)
    {
        $messages = array();

        foreach ($this->customMessages as $message) {
            if (!is_null($type) && $message['type'] != $type) {
                continue;
            }

            $messages[] = $message;
        }

        return $messages;
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

        if(!$this->listingProduct->getChildObject()->isSetCategoryTemplate()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Categories settings are not set');
                parent::MESSAGE_TEXT_KEY => 'Categories settings are not set',
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

        $helper = Mage::getModel('M2ePro/Connector_Server_Ebay_Item_Helper');
        $tempRequestData = $helper->getListRequestData($this->listingProduct, $this->params);
        $this->logAdditionalWarningMessages($this->listingProduct);

        $this->nativeRequestData = $tempRequestData;
        $this->nativeRequestData['verify_call'] = true;
        unset($this->nativeRequestData['images'],$this->nativeRequestData['variation_image']);

        return $this->nativeRequestData;
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        return (isset($response['ebay_item_fees']) && is_array($response['ebay_item_fees']))
                    ? $response['ebay_item_fees'] : array();
    }

    // ########################################
}