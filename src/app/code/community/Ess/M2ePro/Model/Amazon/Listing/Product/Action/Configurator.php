<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const DATA_TYPE_QTY     = 'qty';
    const DATA_TYPE_PRICE   = 'price';
    const DATA_TYPE_IMAGES  = 'images';
    const DATA_TYPE_DETAILS = 'details';

    // ########################################

    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_DETAILS,
            self::DATA_TYPE_IMAGES,
        );
    }

    // ########################################

    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    public function allowQty()
    {
        if ($this->isQtyAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_QTY;
        return $this;
    }

    // ----------------------------------------

    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    public function allowPrice()
    {
        if ($this->isPriceAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_PRICE;
        return $this;
    }

    // ----------------------------------------

    public function isDetailsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DETAILS);
    }

    public function allowDetails()
    {
        if ($this->isDetailsAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_DETAILS;
        return $this;
    }

    // ----------------------------------------

    public function isImagesAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    public function allowImages()
    {
        if ($this->isImagesAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_IMAGES;
        return $this;
    }

    // ########################################
}