<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Response
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Play_Listing_Product_Action_RequestData
     */
    protected $requestData = NULL;

    // ########################################

    abstract public function processSuccess($params = array());

    // ########################################

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->params;
    }

    // ----------------------------------------

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ----------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator $object)
    {
        $this->configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ----------------------------------------

    public function setRequestData(Ess_M2ePro_Model_Play_Listing_Product_Action_RequestData $object)
    {
        $this->requestData = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_RequestData
     */
    protected function getRequestData()
    {
        return $this->requestData;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product
     */
    protected function getPlayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    protected function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return Mage::helper('M2ePro/Component_Play')->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Marketplace
     */
    protected function getPlayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Play_Account
     */
    protected function getPlayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ########################################

    protected function appendStatusChangerValue($data)
    {
        if (isset($this->params['status_changer'])) {
            $data['status_changer'] = (int)$this->params['status_changer'];
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendConditionValues($data)
    {
        if ($this->getRequestData()->hasCondition()) {
            $data['condition'] = $this->getRequestData()->getCondition();
        }

        if ($this->getRequestData()->hasConditionNote()) {
            $data['condition_note'] = $this->getRequestData()->getConditionNote();
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendQtyValues($data)
    {
        if (!$this->getRequestData()->hasQty()) {
            return $data;
        }

        $data['online_qty'] = $this->getRequestData()->getQty();

        if ($data['online_qty'] > 0) {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
        } else {
            $data['status'] = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
        }

        return $data;
    }

    protected function appendPriceValues($data)
    {
        if ($this->getRequestData()->hasPriceGbr()) {
            $data['online_price_gbr'] = (float)$this->getRequestData()->getPriceGbr();
        }

        if ($this->getRequestData()->hasPriceEuro()) {
            $data['online_price_euro'] = (float)$this->getRequestData()->getPriceEuro();
        }

        return $data;
    }

    // ----------------------------------------

    protected function appendShippingValues($data)
    {
        if ($this->getRequestData()->hasShippingPriceGbr()) {
            $data['online_shipping_price_gbr'] = (float)$this->getRequestData()->getShippingPriceGbr();
        }

        if ($this->getRequestData()->hasShippingPriceEuro()) {
            $data['online_shipping_price_euro'] = (float)$this->getRequestData()->getShippingPriceEuro();
        }

        return $data;
    }

    protected function appendDispatchValues($data)
    {
        if ($this->getRequestData()->hasDispatchFrom()) {
            $data['dispatch_from'] = $this->getRequestData()->getDispatchFrom();
        }

        if ($this->getRequestData()->hasDispatchTo()) {
            $data['dispatch_to'] = $this->getRequestData()->getDispatchTo();
        }

        return $data;
    }

    // ########################################
}