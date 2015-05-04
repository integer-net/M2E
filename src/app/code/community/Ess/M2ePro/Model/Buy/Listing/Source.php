<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Source
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
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    public function getBuyListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        $result = '';
        $src = $this->getBuyListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getMagentoProduct()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ----------------------------------------

    public function getSearchGeneralId()
    {
        $src = $this->getBuyListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_NOT_SET) {
            return NULL;
        }

        $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);

        $generalIdModes = array(
            Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_ISBN,
            Ess_M2ePro_Model_Buy_Listing::GENERAL_ID_MODE_WORLDWIDE
        );

        if (in_array($src['mode'], $generalIdModes)) {
            $result = str_replace('-','',$result);
        }

        return $result;
    }

    // ########################################

    public function getCondition()
    {
        $result = 1;
        $src = $this->getBuyListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_DEFAULT) {
            $result = (int)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = (int)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result)) {
            $result < 0  && $result = 0;
            $result > 10  && $result = 10;
        }

        return trim($result);
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getBuyListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
            $result = $renderer->parseTemplate($src['value'], $this->getMagentoProduct());
        }

        is_string($result) && $result = trim(str_replace(array("\r","\n","\t"), '', $result));

        return trim($result);
    }

    // ########################################

    public function getShippingStandardRate()
    {
        $result = 0;
        $src = $this->getBuyListing()->getShippingStandardModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        return $result;
    }

    //-----------------------------------------

    public function getShippingExpeditedMode()
    {
        $src = $this->getBuyListing()->getShippingExpeditedModeSource();

        return (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
    }

    public function getShippingExpeditedRate()
    {
        $result = 0;
        $src = $this->getBuyListing()->getShippingExpeditedModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        return $result;
    }

    //-----------------------------------------

    public function getShippingOneDayMode()
    {
        $src = $this->getBuyListing()->getShippingOneDayModeSource();

        return (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
    }

    public function getShippingOneDayRate()
    {
        $result = 0;
        $src = $this->getBuyListing()->getShippingOneDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        return $result;
    }

    //-----------------------------------------

    public function getShippingTwoDayMode()
    {
        $src = $this->getBuyListing()->getShippingTwoDayModeSource();

        return (int)($src['mode'] != Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED);
    }

    public function getShippingTwoDayRate()
    {
        $result = 0;
        $src = $this->getBuyListing()->getShippingTwoDayModeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DEFAULT ||
            $src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_DISABLED) {
            $result = '';
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_FREE) {
            $result = 0;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_VALUE) {
            $result = (float)$src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Buy_Listing::SHIPPING_MODE_CUSTOM_ATTRIBUTE) {
            $result = (float)$this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        if (is_int($result) || is_float($result)) {
            $result < 0  && $result = 0;
        }

        return $result;
    }

    // ########################################
}