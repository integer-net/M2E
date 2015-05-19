<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Request
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
        'selling',
    );

    /**
     * @var Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Abstract[]
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
        if (!isset($data['sku'])) {
            $data['sku'] = $this->getPlayListingProduct()->getSku();
        }

        if (!isset($data['general_id'])) {
            $data['general_id'] = $this->getPlayListingProduct()->getGeneralId();
        }

        if (!isset($data['general_id_type'])) {
            $data['general_id_type'] = $this->getPlayListingProduct()->getGeneralIdType();
        }

        if (!isset($data['price_gbr'])) {
            $data['price_gbr'] = $this->getPlayListingProduct()->getOnlinePriceGbr();
        }

        if (!isset($data['price_euro'])) {
            $data['price_euro'] = $this->getPlayListingProduct()->getOnlinePriceEuro();
        }

        if (!isset($data['shipping_price_gbr'])) {
            $data['shipping_price_gbr'] = $this->getPlayListingProduct()->getOnlineShippingPriceGbr();
        }

        if (!isset($data['shipping_price_euro'])) {
            $data['shipping_price_euro'] = $this->getPlayListingProduct()->getOnlineShippingPriceEuro();
        }

        if (!isset($data['qty'])) {
            $data['qty'] = $this->getPlayListingProduct()->getOnlineQty();
        }

        if (!isset($data['condition'])) {
            $data['condition'] = $this->getPlayListingProduct()->getCondition();
        }

        if (!isset($data['condition_note'])) {
            $data['condition_note'] = $this->getPlayListingProduct()->getConditionNote();
        }

        if (!isset($data['dispatch_to'])) {
            $data['dispatch_to'] = $this->getPlayListingProduct()->getDispatchTo();
        }

        if (!isset($data['dispatch_from'])) {
            $data['dispatch_from'] = $this->getPlayListingProduct()->getDispatchFrom();
        }

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
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Details
     */
    public function getRequestDetails()
    {
        return $this->getRequest('details');
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Selling
     */
    public function getRequestSelling()
    {
        return $this->getRequest('selling');
    }

    // ########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Abstract
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Play_Listing_Product_Action_Request_Abstract $request */
            $request = Mage::getModel('M2ePro/Play_Listing_Product_Action_Request_'.ucfirst($type));

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