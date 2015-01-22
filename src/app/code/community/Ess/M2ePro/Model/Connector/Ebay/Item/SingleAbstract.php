<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Connector_Ebay_Item_SingleAbstract
    extends Ess_M2ePro_Model_Connector_Ebay_Item_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected $requestObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected $responseObject = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected $requestDataObject = NULL;

    // ########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct->loadInstance($listingProduct->getId());
        parent::__construct($params,$this->listingProduct->getMarketplace(),$this->listingProduct->getAccount());
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

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message, $priority);
        }

        return $result;
    }

    // ----------------------------------------

    protected function eventAfterProcess()
    {
        $this->unlockListingProduct();
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());

        if ($this->listingProduct->isLockedObject(NULL) ||
            $this->listingProduct->isLockedObject('in_action') ||
            $lockItem->isExist()) {

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

        $this->lockListingProduct();
        return $this->filterManualListingProduct();
    }

    // -----------------------------------------

    protected function lockListingProduct()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());

        $lockItem->create();
        $lockItem->makeShutdownFunction();
    }

    protected function unlockListingProduct()
    {
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(Ess_M2ePro_Helper_Component_Ebay::NICK.'_listing_product_'.$this->listingProduct->getId());

        $lockItem->remove();
    }

    // -----------------------------------------

    abstract protected function filterManualListingProduct();

    // ########################################

    protected function logRequestMessages()
    {
        foreach ($this->getRequestObject()->getWarningMessages() as $message) {

            $message = array(
                parent::MESSAGE_TEXT_KEY => $message,
                parent::MESSAGE_TYPE_KEY => parent::MESSAGE_TYPE_WARNING
            );

            $this->getLogger()->logListingProductMessage($this->listingProduct, $message,
                                                         Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
     */
    protected function getRequestObject()
    {
        if (is_null($this->requestObject)) {
            $this->requestObject = $this->makeRequestObject($this->listingProduct);
        }
        return $this->requestObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Response
     */
    protected function getResponseObject()
    {
        if (is_null($this->responseObject)) {
            $this->responseObject = $this->makeResponseObject($this->listingProduct,
                                                              $this->getRequestDataObject());
        }
        return $this->responseObject;
    }

    // ----------------------------------------

    /**
     * @param array $data
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function buildRequestDataObject(array $data)
    {
        if (is_null($this->requestDataObject)) {
            $this->requestDataObject = parent::makeRequestDataObject($this->listingProduct, $data);
        }
        return $this->requestDataObject;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
     */
    protected function getRequestDataObject()
    {
        return $this->requestDataObject;
    }

    // ########################################
}