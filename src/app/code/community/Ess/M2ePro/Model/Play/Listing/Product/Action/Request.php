<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Play_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator
     */
    private $configurator = NULL;

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /**
     * @var array
     */
    private $warningMessages = array();

    // ########################################

    abstract public function getData();

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

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        Mage::helper('M2ePro/Component_Play')->getMarketplace();
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
     * @return Ess_M2ePro_Model_Play_Listing_Product
     */
    protected function getPlayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getPlayListingProduct()->getVariationManager();
    }

    // ########################################

    protected function addWarningMessage($message)
    {
        $this->warningMessages[] = $message;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->warningMessages;
    }

    // ########################################
}