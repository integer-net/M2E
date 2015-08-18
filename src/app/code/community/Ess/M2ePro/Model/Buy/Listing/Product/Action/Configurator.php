<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const DATA_TYPE_SELLING      = 'selling';
    const DATA_TYPE_DETAILS      = 'details';
    const DATA_TYPE_SHIPPING     = 'shipping';
    const DATA_TYPE_NEW_PRODUCT  = 'new_product';

    // ########################################

    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_SELLING,
            self::DATA_TYPE_DETAILS,
            self::DATA_TYPE_SHIPPING,
            self::DATA_TYPE_NEW_PRODUCT,
        );
    }

    // ########################################

    public function isSellingAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SELLING);
    }

    public function allowSelling()
    {
        if ($this->isSellingAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_SELLING;
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

    public function isShippingAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SHIPPING);
    }

    public function allowShipping()
    {
        if ($this->isShippingAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_SHIPPING;
        return $this;
    }

    // ----------------------------------------

    public function isNewProductAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_NEW_PRODUCT);
    }

    public function allowNewProduct()
    {
        if ($this->isNewProductAllowed()) {
            return $this;
        }

        $this->allowedDataTypes[] = self::DATA_TYPE_NEW_PRODUCT;
        return $this;
    }

    // ########################################
}