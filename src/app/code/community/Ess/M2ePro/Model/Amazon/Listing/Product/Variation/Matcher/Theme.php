<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Theme
{
    // ##########################################################

    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    private $magentoProduct = null;

    private $marketplaceId = null;

    private $sourceAttributes = array();

    private $themes = array();

    private $matchedTheme = null;

    // ##########################################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $product)
    {
        $this->magentoProduct   = $product;
        $this->sourceAttributes = array();

        return $this;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
        return $this;
    }

    // ----------------------------------------------------------

    public function setSourceAttributes(array $attributes)
    {
        $this->sourceAttributes = $attributes;
        $this->magentoProduct   = null;

        return $this;
    }

    // ----------------------------------------------------------

    public function setThemes(array $themes)
    {
        $this->themes = $themes;
        return $this;
    }

    // ##########################################################

    public function getMatchedTheme()
    {
        if (is_null($this->matchedTheme)) {
            $this->match();
        }

        return $this->matchedTheme;
    }

    // ##########################################################

    private function match()
    {
        $this->validate();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute $attributeMatcher */
        $attributeMatcher = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute');
        $attributeMatcher->setMarketplaceId($this->marketplaceId);

        if (!is_null($this->magentoProduct)) {
            if ($this->magentoProduct->isGroupedType()) {
                $this->matchedTheme = null;
                return $this;
            }

            $attributeMatcher->setMagentoProduct($this->magentoProduct);
        }

        if (!empty($this->sourceAttributes)) {
            $attributeMatcher->setSourceAttributes($this->sourceAttributes);
            $attributeMatcher->canUseDictionary(false);
        }

        foreach ($this->themes as $themeName => $themeAttributes) {
            $attributeMatcher->setDestinationAttributes($themeAttributes['attributes']);

            if ($attributeMatcher->isAmountEqual() && $attributeMatcher->isFullyMatched()) {
                $this->matchedTheme = $themeName;
                break;
            }
        }

        return $this;
    }

    private function validate()
    {
        if (is_null($this->marketplaceId)) {
            throw new Exception('Marketplace ID was not set.');
        }

        if (is_null($this->magentoProduct) && empty($this->sourceAttributes)) {
            throw new Exception('Magento Product and Channel Attributes were not set.');
        }
    }

    // ##########################################################
}