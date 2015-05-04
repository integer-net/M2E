<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Source
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
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ########################################

    public function getSku()
    {
        $result = '';
        $src = $this->getAmazonListing()->getSkuSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_DEFAULT) {
            $result = $this->getMagentoProduct()->getSku();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_PRODUCT_ID) {
            $result = $this->getMagentoProduct()->getProductId();
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::SKU_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ----------------------------------------

    public function getSearchGeneralId()
    {
        $result = '';
        $src = $this->getAmazonListing()->getGeneralIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    public function getSearchWorldwideId()
    {
        $result = '';
        $src = $this->getAmazonListing()->getWorldwideIdSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_NOT_SET) {
            $result = NULL;
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
            $result = str_replace('-','',$result);
        }

        is_string($result) && $result = trim($result);

        return $result;
    }

    // ########################################

    public function getHandlingTime()
    {
        $result = 0;
        $src = $this->getAmazonListing()->getHandlingTimeSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_RECOMMENDED) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::HANDLING_TIME_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        $result = (int)$result;
        $result < 0  && $result = 0;
        $result > 30 && $result = 30;

        return $result;
    }

    public function getRestockDate()
    {
        $result = '';
        $src = $this->getAmazonListing()->getRestockDateSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_VALUE) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::RESTOCK_DATE_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    //-----------------------------------------

    public function getCondition()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_DEFAULT) {
            $result = $src['value'];
        }

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_MODE_CUSTOM_ATTRIBUTE) {
            $result = $this->getMagentoProduct()->getAttributeValue($src['attribute']);
        }

        return trim($result);
    }

    public function getConditionNote()
    {
        $result = '';
        $src = $this->getAmazonListing()->getConditionNoteSource();

        if ($src['mode'] == Ess_M2ePro_Model_Amazon_Listing::CONDITION_NOTE_MODE_CUSTOM_VALUE) {
            $renderer = Mage::helper('M2ePro/Module_Renderer_Description');
            $result = $renderer->parseTemplate($src['value'], $this->getMagentoProduct());
        }

        return trim($result);
    }

    // ########################################
}