<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    private $requestsTypes = array(
        'selling',
        'description',
        'categories',
        'variations',
        'shipping',
        'payment',
        'return'
    );

    /**
     * @var array[Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract]
     */
    private $requests = array();

    // ########################################

    public function getData()
    {
        $this->initializeVariations();
        $this->beforeBuildDataEvent();

        $data = $this->getActionData();

        $data = $this->prepareFinalData($data);
        $this->collectRequestsWarningMessages();

        return $data;
    }

    // -----------------------------------------

    abstract protected function getActionData();

    // ########################################

    protected function initializeVariations()
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater $variationUpdater */
        $variationUpdater = Mage::getModel('M2ePro/Ebay_Listing_Product_Variation_Updater');
        $variationUpdater->process($this->getListingProduct());
        $variationUpdater->afterMassProcessEvent();

        $isVariationItem = $this->getEbayListingProduct()->isVariationsReady();

        $this->setIsVariationItem($isVariationItem);

        $validateVariationsKey = Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater::VALIDATE_MESSAGE_DATA_KEY;

        if ($this->getListingProduct()->hasData($validateVariationsKey)) {

            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    $this->getListingProduct()->getData($validateVariationsKey)
                )
            );

            $this->getListingProduct()->unsetData($validateVariationsKey);
        }
    }

    protected function beforeBuildDataEvent() {}

    // -----------------------------------------

    protected function prepareFinalData(array $data)
    {
        $data['is_eps_ebay_images_mode'] = $this->getIsEpsImagesMode();

        if (!isset($data['out_of_stock_control'])) {
            $data['out_of_stock_control'] = $this->getOutOfStockControlMode();
        }

        if ($this->getIsVariationItem() &&
            !empty($data['item_specifics']) && is_array($data['item_specifics'])  &&
            !empty($data['variations_sets']) && is_array($data['variations_sets'])) {

            $variationAttributes = array_keys($data['variations_sets']);
            $variationAttributes = array_map('strtolower',$variationAttributes);

            foreach ($data['item_specifics'] as $key => $itemSpecific) {
                if (in_array(strtolower($itemSpecific['name']), $variationAttributes)) {
                    unset($data['item_specifics'][$key]);
                }
            }
        }

        if (isset($data['variation']) && is_array($data['variation'])) {
            foreach ($data['variation'] as &$variation) {
                unset($variation['_instance_']);
            }
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

    // ----------------------------------------

    protected function getIsEpsImagesMode()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['is_eps_ebay_images_mode'])) {
            return NULL;
        }

        return $additionalData['is_eps_ebay_images_mode'];
    }

    protected function getOutOfStockControlMode()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['out_of_stock_control'])) {
            return NULL;
        }

        return $additionalData['out_of_stock_control'];
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling
     */
    public function getRequestSelling()
    {
        return $this->getRequest('selling');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Description
     */
    public function getRequestDescription()
    {
        return $this->getRequest('description');
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Variations
     */
    public function getRequestVariations()
    {
        return $this->getRequest('variations');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Categories
     */
    public function getRequestCategories()
    {
        return $this->getRequest('categories');
    }

    // ----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Payment
     */
    public function getRequestPayment()
    {
        return $this->getRequest('payment');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Shipping
     */
    public function getRequestShipping()
    {
        return $this->getRequest('shipping');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Return
     */
    public function getRequestReturn()
    {
        return $this->getRequest('return');
    }

    // ########################################

    /**
     * @param $type
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
     */
    private function getRequest($type)
    {
        if (!isset($this->requests[$type])) {

            /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract $request */
            $request = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Request_'.ucfirst($type));

            $request->setParams($this->getParams());
            $request->setListingProduct($this->getListingProduct());
            $request->setIsVariationItem($this->getIsVariationItem());
            $request->setConfigurator($this->getConfigurator());

            $this->requests[$type] = $request;
        }

        return $this->requests[$type];
    }

    // ########################################
}