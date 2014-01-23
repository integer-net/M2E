<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_Revise_Single
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','update','revise');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_REVISE_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Connector_Ebay_Item_Dispatcher::ACTION_REVISE;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->listingProduct->isRevisable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is not listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is not listed or not available',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if(!$this->listingProduct->getChildObject()->isSetCategoryTemplate()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Categories settings are not set');
                parent::MESSAGE_TEXT_KEY => 'Categories settings are not set',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if (Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater')
                    ->isAddedNewVariationsAttributes($this->listingProduct)) {

            $message = array(
                // ->__('Variation attributes were changed. Please stop and list product.');
                parent::MESSAGE_TEXT_KEY => 'Variation attributes were changed. Please stop and list product.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);

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

        $params = array(
            'is_images_upload_error' => $this->isImagesUploadFailed($this->messages)
        );

        if ($response['already_stop']) {

            $this->getResponseObject()->processAlreadyStopped($response, $params);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was already stopped on eBay');
                parent::MESSAGE_TEXT_KEY => 'Item was already stopped on eBay',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
            );

        } else {

            $this->getResponseObject()->processSuccess($response, $params);

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully revised');
                parent::MESSAGE_TEXT_KEY => $this->getResponseObject()->getSuccessfulMessage(),
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );
        }

        $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

        return $response;
    }

    // ########################################
}