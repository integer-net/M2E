<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_RequestData
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    // ########################################

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    // ----------------------------------------

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ########################################

    public function hasSku()
    {
        return isset($this->data['sku']);
    }

    public function hasGeneralId()
    {
        return isset($this->data['general_id']);
    }

    public function hasGeneralIdType()
    {
        return isset($this->data['general_id_type']);
    }

    // ----------------------------------------

    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    // ----------------------------------------

    public function hasPriceGbr()
    {
        return isset($this->data['price_gbr']);
    }

    public function hasPriceEuro()
    {
        return isset($this->data['price_euro']);
    }

    // ----------------------------------------

    public function hasShippingPriceGbr()
    {
        return isset($this->data['shipping_price_gbr']);
    }

    public function hasShippingPriceEuro()
    {
        return isset($this->data['shipping_price_euro']);
    }

    // ----------------------------------------

    public function hasCondition()
    {
        return isset($this->data['condition']);
    }

    public function hasConditionNote()
    {
        return isset($this->data['condition_note']);
    }

    // ----------------------------------------

    public function hasDispatchTo()
    {
        return isset($this->data['dispatch_to']);
    }

    public function hasDispatchFrom()
    {
        return isset($this->data['dispatch_from']);
    }

    // ########################################

    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getGeneralId()
    {
        return $this->hasGeneralId() ? $this->data['general_id'] : NULL;
    }

    public function getGeneralIdType()
    {
        return $this->hasGeneralIdType() ? $this->data['general_id_type'] : NULL;
    }

    // ----------------------------------------

    public function getQty()
    {
        return $this->hasQty() ? (int)$this->data['qty'] : NULL;
    }

    // ----------------------------------------

    public function getPriceGbr()
    {
        return $this->hasPriceGbr() ? (float)$this->data['price_gbr'] : NULL;
    }

    public function getPriceEuro()
    {
        return $this->hasPriceEuro() ? (float)$this->data['price_euro'] : NULL;
    }

    // ----------------------------------------

    public function getShippingPriceGbr()
    {
        return $this->hasShippingPriceGbr() ? (float)$this->data['shipping_price_gbr'] : NULL;
    }

    public function getShippingPriceEuro()
    {
        return $this->hasShippingPriceEuro() ? (float)$this->data['shipping_price_euro'] : NULL;
    }

    // ----------------------------------------

    public function getCondition()
    {
        return $this->hasCondition() ? $this->data['condition'] : NULL;
    }

    public function getConditionNote()
    {
        return $this->hasConditionNote() ? $this->data['condition_note'] : NULL;
    }

    // ----------------------------------------

    public function getDispatchTo()
    {
        return $this->hasDispatchTo() ? isset($this->data['dispatch_to']) : NULL;
    }

    public function getDispatchFrom()
    {
        return $this->hasDispatchFrom() ? isset($this->data['dispatch_from']) : NULL;
    }

    // ########################################
}