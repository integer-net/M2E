<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_MultipleAbstract
    extends Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
{
    /**
     * @var array[Ess_M2ePro_Model_Listing_Product]
     */
    protected $listingsProducts = array();

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request]
     */
    protected $requestsObjects = array();

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response]
     */
    protected $responsesObjects = array();

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData]
     */
    protected $requestsDataObjects = array();

    // ########################################

    public function __construct(array $params = array(), array $listingsProducts)
    {
        if (count($listingsProducts) == 0) {
            throw new Exception('Multiple Item Connector has received empty array');
        }

        foreach($listingsProducts as $listingProduct) {
            if (!($listingProduct instanceof Ess_M2ePro_Model_Listing_Product)) {
                throw new Exception('Multiple Item Connector has received invalid product data type');
            }
        }

        $tempAccount = $listingsProducts[0]->getAccount();
        $tempMarketplace = $listingsProducts[0]->getMarketplace();

        foreach($listingsProducts as $listingProduct) {

            if ($tempAccount->getId() != $listingProduct->getAccount()->getId()) {
                throw new Exception('Multiple Item Connector has received products from different accounts');
            }

            if ($tempMarketplace->getId() != $listingProduct->getMarketplace()->getId()) {
                throw new Exception('Multiple Item Connector has received products from different marketplaces');
            }
        }

        $this->listingsProducts = $listingsProducts;
        parent::__construct($params,$tempMarketplace,$tempAccount);
    }

    // ########################################

    public function process()
    {
        $result = parent::process();

        foreach ($this->messages as $message) {

            $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
            }

            foreach ($this->listingsProducts as $product) {
                $this->getLogger()->logListingProductMessage($product, $message, $priority);
            }
        }

        if (!isset($result['result'])) {
            return $result;
        }

        foreach ($result['result'] as $listingProductId => $listingsProductResult) {

            if (!isset($listingsProductResult['messages'])){
                continue;
            }

            $listingProduct = $this->getListingProduct($listingProductId);

            foreach ($listingsProductResult['messages'] as $message) {

                $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

                if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                    $priority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;
                }

                $this->getLogger()->logListingProductMessage($listingProduct, $message, $priority);
            }
        }

        return $result;
    }

    protected function processResponseInfo($responseInfo)
    {
        try {
            parent::processResponseInfo($responseInfo);
        } catch (Exception $exception) {

            $message = array(
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_ERROR,
                parent::MESSAGE_TEXT_KEY => $exception->getMessage()
            );

            foreach ($this->listingsProducts as $listingProduct) {
                $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                             Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
            }

            throw $exception;
        }
    }

    // ########################################

    protected function eventBeforeProcess()
    {
        foreach ($this->listingsProducts as $listingProduct) {
            $this->getLocker($listingProduct->getId())->update();
        }
    }

    protected function eventAfterProcess()
    {
        foreach ($this->listingsProducts as $listingProduct) {
            $this->getLocker($listingProduct->getId())->remove();
        }
    }

    // ########################################

    protected function logRequestMessages(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        foreach ($this->getRequestObject($listingProduct)->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ---------------------------------------

    /**
     * @param $id
     * @return Ess_M2ePro_Model_Listing_Product
     * @throws Exception
     */
    protected function getListingProduct($id)
    {
        foreach ($this->listingsProducts as $listingProduct) {
            /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
            if ($listingProduct->getId() == $id) {
                return $listingProduct;
            }
        }

        throw new Exception('Listing product was not found');
    }

    /**
     * @param array $result
     * @return bool
     */
    protected function isResultSuccess(array $result)
    {
        $messages = isset($result['messages']) ? $result['messages'] : array();

        foreach ($messages as $message) {
            if ($message[parent::MESSAGE_TYPE_KEY] == parent::MESSAGE_TYPE_ERROR) {
                return false;
            }
        }

        return true;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->requestsObjects[$listingProduct->getId()])) {
            $this->requestsObjects[$listingProduct->getId()] = $this->makeRequestObject($listingProduct);
        }
        return $this->requestsObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($this->responsesObjects[$listingProduct->getId()])) {
            $this->responsesObjects[$listingProduct->getId()] =
                        $this->makeResponseObject($listingProduct,
                                                  $this->getRequestDataObject($listingProduct));
        }
        return $this->responsesObjects[$listingProduct->getId()];
    }

    // ----------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct, array $data)
    {
        if (!isset($this->requestsDataObjects[$listingProduct->getId()])) {
            $this->requestsDataObjects[$listingProduct->getId()] = parent::makeRequestDataObject($listingProduct,$data);
        }
        return $this->requestsDataObjects[$listingProduct->getId()];
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        return $this->requestsDataObjects[$listingProduct->getId()];
    }

    // ########################################
}