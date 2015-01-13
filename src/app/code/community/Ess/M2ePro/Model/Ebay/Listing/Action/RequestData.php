<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var array
     */
    protected $data = array();

    // ########################################

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    // ########################################

    public function hasQtyData()
    {
        return isset($this->data['qty']);
    }

    public function hasPriceData()
    {
        return $this->hasPriceFixedData();
    }

    // ----------------------------------------

    public function hasPriceFixedData()
    {
        return isset($this->data['price_fixed']);
    }

    // ----------------------------------------

    public function hasTitleData()
    {
        return isset($this->data['title']);
    }

    public function hasSubtitleData()
    {
        return isset($this->data['subtitle']);
    }

    public function hasDescriptionData()
    {
        return isset($this->data['description']);
    }

    // ########################################

    public function getQtyData()
    {
        return $this->hasQtyData() ? $this->data['qty'] : NULL;
    }

    public function getPriceFixedData()
    {
        return $this->hasPriceFixedData() ? $this->data['price_fixed'] : NULL;
    }

    // ----------------------------------------

    public function getTitleData()
    {
        return $this->hasTitleData() ? $this->data['title'] : NULL;
    }

    public function getSubtitleData()
    {
        return $this->hasSubtitleData() ? $this->data['subtitle'] : NULL;
    }

    public function getDescriptionData()
    {
        return $this->hasDescriptionData() ? $this->data['description'] : NULL;
    }

    // ########################################
}