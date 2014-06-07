<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_List_Single
    extends Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
{
    // ########################################

    protected function getCommand()
    {
        return array('item','add','single');
    }

    protected function getLogAction()
    {
        return Ess_M2ePro_Model_Listing_Log::ACTION_LIST_PRODUCT_ON_COMPONENT;
    }

    protected function getActionType()
    {
        return Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->listingProduct->isListable()) {

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
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

            $this->getLogger()->logListingProductMessage($this->listingProduct,$message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

            return false;
        }

        if ($this->params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
            $theSameListingProduct = $this->getRequestObject()->getTheSameProductAlreadyListed()) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                    'There is another item with the same eBay user ID,
                    product ID and marketplace presented in "%s" (%d) Listing.',
                    $theSameListingProduct->getListing()->getTitle(),
                    $theSameListingProduct->getListing()->getId()
                ),
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
        $this->logRequestMessages();

        return $this->buildRequestDataObject($data)->getData();
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return $response;
        }

        $this->getResponseObject()->processSuccess($response, array(
            'is_images_upload_error' => $this->isImagesUploadFailed($this->messages)
        ));

        $message = array(
            // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
            parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
            parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
        );

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
}