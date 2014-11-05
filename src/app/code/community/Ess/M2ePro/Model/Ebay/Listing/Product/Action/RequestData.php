<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData extends Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    // ########################################

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

    public function isVariationItem()
    {
        return isset($this->data['is_variation_item']) && $this->data['is_variation_item'];
    }

    // ----------------------------------------

    public function hasVariationsData()
    {
        return $this->isVariationItem() && isset($this->data['variation']);
    }

    public function hasVariationsImagesData()
    {
        return $this->isVariationItem() && isset($this->data['variation_image']);
    }

    // ----------------------------------------

    public function hasQtyData()
    {
        return !$this->isVariationItem() && isset($this->data['qty']);
    }

    public function hasPriceData()
    {
        return !$this->isVariationItem() &&
                (
                    $this->hasPriceFixedData() ||
                    $this->hasPriceStartData() ||
                    $this->hasPriceReserveData() ||
                    $this->hasPriceBuyItNowData()
                );
    }

    // ----------------------------------------

    public function hasPriceFixedData()
    {
        return !$this->isVariationItem() && isset($this->data['price_fixed']);
    }

    public function hasPriceStartData()
    {
        return !$this->isVariationItem() && isset($this->data['price_start']);
    }

    public function hasPriceReserveData()
    {
        return !$this->isVariationItem() && isset($this->data['price_reserve']);
    }

    public function hasPriceBuyItNowData()
    {
        return !$this->isVariationItem() && isset($this->data['price_buyitnow']);
    }

    // ----------------------------------------

    public function hasOutOfStockControlData()
    {
        return isset($this->data['out_of_stock_control']);
    }

    // ----------------------------------------

    public function hasSkuData()
    {
        return isset($this->data['sku']);
    }

    public function hasPrimaryCategoryData()
    {
        return isset($this->data['category_main_id']);
    }

    // ----------------------------------------

    public function hasImagesData()
    {
        return isset($this->data['images']);
    }

    // ########################################

    public function getVariationsData()
    {
        return $this->hasVariationsData() ? $this->data['variation'] : NULL;
    }

    public function getVariationsImagesData()
    {
        return $this->hasVariationsImagesData() ? $this->data['variation_image'] : NULL;
    }

    // ----------------------------------------

    public function getVariationQtyData()
    {
        if (!$this->hasVariationsData()) {
            return NULL;
        }

        $qty = 0;
        foreach ($this->getVariationsData() as $variationData) {
            $qty += (int)$variationData['qty'];
        }

        return $qty;
    }

    public function getVariationPriceData($calculateWithEmptyQty = true)
    {
        if (!$this->hasVariationsData()) {
            return NULL;
        }

        $price = NULL;

        foreach ($this->getVariationsData() as $variationData) {

            if (!$calculateWithEmptyQty && (int)$variationData['qty'] <= 0) {
                continue;
            }

            if (!is_null($price) && (float)$variationData['price'] >= $price) {
                continue;
            }

            $price = (float)$variationData['price'];
        }

        return (float)$price;
    }

    // ----------------------------------------

    public function getPriceStartData()
    {
        return $this->hasPriceStartData() ? $this->data['price_start'] : NULL;
    }

    public function getPriceReserveData()
    {
        return $this->hasPriceReserveData() ? $this->data['price_reserve'] : NULL;
    }

    public function getPriceBuyItNowData()
    {
        return $this->hasPriceBuyItNowData() ? $this->data['price_buyitnow'] : NULL;
    }

    // ----------------------------------------

    public function getOutOfStockControlData()
    {
        return $this->hasOutOfStockControlData() ? $this->data['out_of_stock_control'] : NULL;
    }

    // ----------------------------------------

    public function getSkuData()
    {
        return $this->hasSkuData() ? $this->data['sku'] : NULL;
    }

    public function getPrimaryCategoryData()
    {
        return $this->hasPrimaryCategoryData() ? $this->data['category_main_id'] : NULL;
    }

    // ----------------------------------------

    public function getImagesData()
    {
        return $this->hasImagesData() ? $this->data['images'] : NULL;
    }

    // ########################################

    public function getImagesCount()
    {
        if (!$this->hasImagesData()) {
            return 0;
        }

        $images = $this->getImagesData();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    public function getVariationsImagesCount()
    {
        if (!$this->hasVariationsImagesData()) {
            return 0;
        }

        $images = $this->getVariationsImagesData();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    public function getTotalImagesCount()
    {
        return $this->getImagesCount() + $this->getVariationsImagesCount();
    }

    // ########################################
}