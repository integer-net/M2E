<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $validatorsData = array();

    /**
     * @var array
     */
    private $requestsTypes = array(
        'details',
        'images',
        'price',
        'qty'
    );

    /**
     * @var array[Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract]
     */
    private $requests = array();

    // ########################################

    public function setValidatorsData(array $data)
    {
        $this->validatorsData = $data;
    }

    /**
     * @return array
     */
    public function getValidatorsData()
    {
        return $this->validatorsData;
    }

    // ########################################

    public function getData()
    {
        $this->beforeBuildDataEvent();
        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectRequestsWarningMessages();

        return $data;
    }

    // ########################################

    protected function beforeBuildDataEvent() {}

    abstract protected function getActionData();

    // -----------------------------------------

    protected function prepareFinalData(array $data)
    {
        return $data;
    }

    protected function collectRequestsWarningMessages()
    {
        foreach ($this->requestsTypes as $requestType) {

            $messages = $this->getRequest($requestType)->getWarningMessages();

            foreach ($messages as $message) {
                $this->addWarningMessage($message);
            }
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Details
     */
    public function getRequestDetails()
    {
        return $this->getRequest('details');
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Images
     */
    public function getRequestImages()
    {
        return $this->getRequest('images');
    }

    // -----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Price
     */
    public function getRequestPrice()
    {
        return $this->getRequest('price');
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Qty
     */
    public function getRequestQty()
    {
        return $this->getRequest('qty');
    }

    // ########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Action_Request_Abstract $request */
            $request = Mage::getModel('M2ePro/Amazon_Listing_Product_Action_Request_'.ucfirst($type));

            $request->setParams($this->getParams());
            $request->setListingProduct($this->getListingProduct());
            $request->setConfigurator($this->getConfigurator());
            $request->setValidatorsData($this->getValidatorsData());

            $this->requests[$type] = $request;
        }

        return $this->requests[$type];
    }

    // ########################################
}