<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Item_List_Multiple
    extends Ess_M2ePro_Model_Connector_Ebay_Item_MultipleAbstract
{
    private $failedListingProductIds = array();

    // ########################################

    protected function getCommand()
    {
        return array('item','add','multiple');
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
        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (!$listingProduct->isListable()) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Item is listed or not available');
                    parent::MESSAGE_TEXT_KEY => 'Item is listed or not available',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                $this->failedListingProductIds[] = $listingProduct->getId();
                continue;
            }

            if(!$listingProduct->getChildObject()->isSetCategoryTemplate()) {

                $message = array(
                    // Parser hack -> Mage::helper('M2ePro')->__('Categories settings are not set');
                    parent::MESSAGE_TEXT_KEY => 'Categories settings are not set',
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                $this->failedListingProductIds[] = $listingProduct->getId();
                continue;
            }

            if ($this->params['status_changer'] != Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_USER &&
                $theSameListingProduct = $this->getRequestObject($listingProduct)->getTheSameProductAlreadyListed()) {

                $message = array(
                    parent::MESSAGE_TEXT_KEY => Mage::helper('M2ePro')->__(
                        'There is another item with the same eBay user ID,
                        product ID and marketplace presented in "%s" (%d) Listing.',
                        $theSameListingProduct->getListing()->getTitle(),
                        $theSameListingProduct->getListing()->getId()
                    ),
                    parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR
                );

                $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);

                $this->failedListingProductIds[] = $listingProduct->getId();
                continue;
            }
        }

        if (count($this->listingsProducts) <= count($this->failedListingProductIds)) {
            return false;
        }

        return true;
    }

    protected function getRequestData()
    {
        $data = array(
            'products' => array()
        );

        foreach ($this->listingsProducts as $listingProduct) {

            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */

            if (in_array($listingProduct->getId(),$this->failedListingProductIds)) {
                continue;
            }

            $this->getRequestObject($listingProduct)->clearVariations();

            $tempData = $this->getRequestObject($listingProduct)->getData();
            $this->logRequestMessages($listingProduct);

            $data['products'][$listingProduct->getId()] =
                $this->buildRequestDataObject($listingProduct,$tempData)->getData();
        }

        return $data;
    }

    //----------------------------------------

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR || !isset($response['result'])) {
            return $response;
        }

        foreach ($response['result'] as $listingProductId => $listingsProductResult) {

            if (!$this->isResultSuccess($listingsProductResult)) {
                continue;
            }

            $listingProduct = $this->getListingProduct($listingProductId);
            $messages = isset($listingsProductResult['messages']) ? $listingsProductResult['messages'] : array();

            $this->getResponseObject($listingProduct)->processSuccess($listingsProductResult, array(
                'is_images_upload_error' => $this->isImagesUploadFailed($messages)
            ));

            $message = array(
                // Parser hack -> Mage::helper('M2ePro')->__('Item was successfully listed');
                parent::MESSAGE_TEXT_KEY => 'Item was successfully listed',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_SUCCESS
            );

            $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }

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

    protected function processAsPotentialDuplicate()
    {
        foreach ($this->listingsProducts as $listingProduct) {

            $this->getResponseObject($listingProduct)->markAsPotentialDuplicate();

            $message = array(
                parent::MESSAGE_TEXT_KEY => 'An error occured while listing the item. '.
                                    'The item has been blocked. The next M2E Synchronization will resolve the problem.',
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($listingProduct, $message);
        }
    }

    // ########################################
}