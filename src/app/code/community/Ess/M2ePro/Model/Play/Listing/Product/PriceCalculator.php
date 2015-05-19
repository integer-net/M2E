<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Play_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Play_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Play_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Play_Listing_Product_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    // ########################################

    /**
     * @var null|string
     */
    private $currency = NULL;

    // ########################################

    /**
     * @param string $value
     * @return Ess_M2ePro_Model_Play_Listing_Product_PriceCalculator
     */
    public function setCurrency($value)
    {
        $this->currency = (string)$value;
        return $this;
    }

    /**
     * @return string
     */
    protected function getCurrency()
    {
        if (empty($this->currency)) {
            throw new LogicException('Initialize all parameters first.');
        }

        return $this->currency;
    }

    // ########################################

    protected function convertValueFromStoreToMarketplace($value)
    {
        return $this->getComponentListing()->convertPriceFromStoreToMarketplace($value, $this->getCurrency());
    }

    // ########################################
}