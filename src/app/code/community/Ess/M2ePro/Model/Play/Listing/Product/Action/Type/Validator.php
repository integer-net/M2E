<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Validator
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    /** @var Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator $configurator */
    private $configurator = NULL;

    /**
     * @var array
     */
    private $messages = array();

    /**
     * @var array
     */
    protected $data = array();

    // ########################################

    public function setParams(array $params)
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

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ----------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator $configurator)
    {
        $this->configurator = $configurator;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->configurator;
    }

    // ########################################

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

    abstract public function validate();

    protected function addMessage($message, $type = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR)
    {
        $this->messages[] = array(
            'text' => $message,
            'type' => $type,
        );
    }

    // ----------------------------------------

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    // ----------------------------------------

    /**
     * @param $key
     * @return array
     */
    public function getData($key = null)
    {
        if (is_null($key)) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    // ########################################

    protected function validateSku()
    {
        if (!$this->getPlayListingProduct()->getSku()) {

            // M2ePro_TRANSLATIONS
            // You have to list Item first.
            $this->addMessage('You have to list Item first.');

            return false;
        }

        return true;
    }

    protected function validateLockedObject()
    {
        if ($this->getListingProduct()->isLockedObject(NULL) ||
            $this->getListingProduct()->isLockedObject('in_action')) {

            // M2ePro_TRANSLATIONS
            // Another Action is being processed. Try again when the Action is completed.
            $this->addMessage('Another Action is being processed. Try again when the Action is completed.');

            return false;
        }

        return true;
    }

    protected function validateVariationProductMatching()
    {
        if (!$this->getVariationManager()->isVariationProductMatched()) {
            // M2ePro_TRANSLATIONS
            // You have to select Magento Variation.
            $this->addMessage('You have to select Magento Variation.');

            return false;
        }

        return true;
    }

    protected function validateQty()
    {
        if (!$this->getConfigurator()->isSelling()) {
            return true;
        }

        $qty = $this->getQty();
        if ($qty <= 0) {

            // M2ePro_TRANSLATIONS
            // The Quantity must be greater than 0. Please, check the Selling Format Policy and Product Settings.
            $this->addMessage(
                'The Quantity must be greater than 0. Please, check the Selling Format Policy and Product Settings.'
            );

            return false;
        }

        $this->data['qty'] = $qty;

        return true;
    }

    protected function validatePrice()
    {
        if (!$this->getConfigurator()->isSelling()) {
            return true;
        }

        $dispatchTo = $this->getDispatchTo();

        if (Mage::helper('M2ePro/Component_Play')->isDispatchToAllowedForGbr($dispatchTo)) {

            $priceGbr = $this->getPriceGbr();
            if ($priceGbr <= 0) {

                // M2ePro_TRANSLATIONS
                // The Price GBP must be greater than 0. Please, check the Selling Format Policy and Product Settings.
                $this->addMessage(
                    'The Price GBP must be greater than 0. '.
                    'Please, check the Selling Format Policy and Product Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                );

                return false;
            }
            $this->data['price_gbr'] = $priceGbr;
        }

        if (Mage::helper('M2ePro/Component_Play')->isDispatchToAllowedForEuro($dispatchTo)) {

            $priceEuro = $this->getPriceEuro();
            if ($priceEuro <= 0) {

                // M2ePro_TRANSLATIONS
                // The Price EUR must be greater than 0. Please, check the Selling Format Policy and Product Settings.
                $this->addMessage(
                    'The Price EUR must be greater than 0. '.
                    'Please, check the Selling Format Policy and Product Settings.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
                );

                return false;
            }
            $this->data['price_euro'] = $priceEuro;
        }

        return true;
    }

    // ########################################

    protected function getQty()
    {
        if (!empty($this->data['qty'])) {
            return $this->data['qty'];
        }

        return $this->getPlayListingProduct()->getQty();
    }

    // ########################################

    protected function getPriceGbr()
    {
        if (!empty($this->data['price_gbr'])) {
            return $this->data['price_gbr'];
        }

        return $this->getPlayListingProduct()->getPriceGbr(true);
    }

    protected function getPriceEuro()
    {
        if (!empty($this->data['price_euro'])) {
            return $this->data['price_euro'];
        }

        return $this->getPlayListingProduct()->getPriceEuro(true);
    }

    // ########################################

    protected function getDispatchFrom()
    {
        if (!empty($this->data['dispatch_from'])) {
            return $this->data['dispatch_from'];
        }

        $dispatchFrom = $this->getPlayListingProduct()->getDispatchFrom();
        if (!empty($dispatchFrom)) {
            return $dispatchFrom;
        }

        return $this->getPlayListing()->getDispatchFrom();
    }

    protected function getDispatchTo()
    {
        if (!empty($this->data['dispatch_to'])) {
            return $this->data['dispatch_to'];
        }

        $dispatchFrom = $this->getPlayListingProduct()->getDispatchTo();
        if (!empty($dispatchFrom)) {
            return $dispatchFrom;
        }

        return $this->getPlayListingProduct()->getListingSource()->getDispatchTo();
    }

    // ########################################
}