<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Source
{
    /**
     * @var $magentoProduct Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProduct = null;

    /**
     * @var $listing Ess_M2ePro_Model_Listing
     */
    private $listing = null;

    // ########################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function getMagentoProduct()
    {
        return $this->magentoProduct;
    }

    // ----------------------------------------

    public function setListing(Ess_M2ePro_Model_Listing $listing)
    {
        $this->listing = $listing;
        return $this;
    }

    public function getListing()
    {
        return $this->listing;
    }

    /**
     * @return Ess_M2ePro_Model_Play_Listing
     */
    public function getPlayListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        $result = '';
        $src = $this->getPlayListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getMagentoProduct()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ----------------------------------------

    public function getSearchGeneralId()
    {
        $src = $this->getPlayListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::GENERAL_ID_MODE_NOT_SET) {
            return NULL;
        }

        return $this->getMagentoProduct()->getAttributeValue($src['attribute']);
    }

    // ########################################

    public function getCondition()
    {
        $result = '';
        $src = $this->getPlayListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceConditionValue($result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getPlayListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
            $result = $renderer->parseTemplate($src['value'], $this->getMagentoProduct());
        }

        is_string($result) && $result = trim(str_replace(array("\r","\n","\t"), '', $result));

        return trim($result);
    }

    // ########################################

    public function getShippingPriceGbr()
    {
        $price = 0;
        $src = $this->getPlayListing()->getShippingPriceGbrSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_GBR_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    public function getShippingPriceEuro()
    {
        $price = 0;
        $src = $this->getPlayListing()->getShippingPriceEuroSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_NONE) {
            return $price;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_CUSTOM_VALUE) {
            $price = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::SHIPPING_PRICE_EURO_MODE_CUSTOM_ATTRIBUTE) {
            $price = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $price < 0 && $price = 0;

        return round($price,2);
    }

    // ########################################

    public function getDispatchTo()
    {
        $result = '';
        $src = $this->getPlayListing()->getDispatchToSource();

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = $this->replaceDispatchToValue($result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ########################################

    protected function replaceConditionValue($value)
    {
        $value = (int)$value;

        $replacementCondition = array(
            1 => Ess_M2ePro_Model_Play_Listing::CONDITION_NEW,
            2 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_LIKE_NEW,
            3 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_VERY_GOOD,
            4 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_GOOD,
            5 => Ess_M2ePro_Model_Play_Listing::CONDITION_USED_AVERAGE,
            6 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_LIKE_NEW,
            7 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_VERY_GOOD,
            8 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_GOOD,
            9 => Ess_M2ePro_Model_Play_Listing::CONDITION_COLLECTABLE_AVERAGE,
            10 => Ess_M2ePro_Model_Play_Listing::CONDITION_REFURBISHED
        );

        return array_key_exists($value,$replacementCondition) ? $replacementCondition[$value] : $value;
    }

    protected function replaceDispatchToValue($value)
    {
        $value = strtolower(trim($value));

        $replacementDispatchTo = array(
            'uk' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_UK,
            'europe' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_EUROPA,
            'europe_uk' => Ess_M2ePro_Model_Play_Listing::DISPATCH_TO_BOTH
        );

        return array_key_exists($value,$replacementDispatchTo) ? $replacementDispatchTo[$value] : $value;
    }

    // ########################################
}