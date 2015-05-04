<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Ebay_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Ebay_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Ebay_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    // ########################################

    /**
     * @var bool
     */
    private $isIncreaseByVatPercent = false;

    // ########################################

    /**
     * @param bool $value
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator
     */
    public function setIsIncreaseByVatPercent($value)
    {
        $this->isIncreaseByVatPercent = (bool)$value;
        return $this;
    }

    /**
     * @return bool
     */
    protected function getIsIncreaseByVatPercent()
    {
        return $this->isIncreaseByVatPercent;
    }

    // ########################################

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($variation->getChildObject()->isDelete()) {
            return 0;
        }

        return parent::getVariationValue($variation);
    }

    // ########################################

    protected function prepareFinalValue($value)
    {
        if ($this->getIsIncreaseByVatPercent() &&
            $this->getComponentSellingFormatTemplate()->isPriceIncreaseVatPercentEnabled()) {

            $value = $this->increaseValueByVatPercent($value);
        }

        return parent::prepareFinalValue($value);
    }

    protected function increaseValueByVatPercent($value)
    {
        $vatPercent = $this->getComponentSellingFormatTemplate()->getVatPercent();
        return $value + (($vatPercent*$value) / 100);
    }

    // ########################################

    protected function prepareOptionTitles($optionTitles)
    {
        foreach ($optionTitles as &$optionTitle) {
            $optionTitle = Mage::helper('M2ePro')->reduceWordsInString(
                $optionTitle, Ess_M2ePro_Helper_Component_Ebay::MAX_LENGTH_FOR_OPTION_VALUE
            );
        }

        return $optionTitles;
    }

    // ########################################
}