<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute
{
    // ##########################################################

    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    private $magentoProduct = null;

    private $marketplaceId = null;

    private $sourceAttributes = array();

    private $destinationAttributes = array();

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver $resolver */
    private $resolver = null;

    private $matchedAttributes = array();

    private $canUseDictionary = true;

    // ##########################################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $product)
    {
        $this->magentoProduct = $product;
        $this->sourceAttributes = array();

        $this->matchedAttributes = array();

        return $this;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
        $this->matchedAttributes = array();

        return $this;
    }

    // ----------------------------------------------------------

    public function setSourceAttributes(array $attributes)
    {
        $this->sourceAttributes = $attributes;
        $this->magentoProduct   = null;

        $this->matchedAttributes = array();

        return $this;
    }

    public function setDestinationAttributes(array $attributes)
    {
        $this->destinationAttributes = $attributes;
        $this->matchedAttributes = array();

        return $this;
    }

    // ----------------------------------------------------------

    public function canUseDictionary($flag = true)
    {
        $this->canUseDictionary = $flag;
        return $this;
    }

    // ##########################################################

    public function isAmountEqual()
    {
        return count($this->getSourceAttributesData()) == count($this->getDestinationAttributesData());
    }

    // ----------------------------------------------------------

    public function getMatchedAttributes()
    {
        if (empty($this->matchedAttributes)) {
            $this->match();
        }

        return $this->matchedAttributes;
    }

    // ----------------------------------------------------------

    public function isFullyMatched()
    {
        return count($this->getMagentoUnmatchedAttributes()) <= 0 && count($this->getChannelUnmatchedAttributes()) <= 0;
    }

    public function isNotMatched()
    {
        return count($this->getMatchedAttributes()) <= 0;
    }

    public function isPartiallyMatched()
    {
        return !$this->isFullyMatched() && !$this->isNotMatched();
    }

    // ----------------------------------------------------------

    public function getMagentoUnmatchedAttributes()
    {
        return array_keys($this->getMatchedAttributes(), null);
    }

    public function getChannelUnmatchedAttributes()
    {
        $matchedChannelAttributes = array_values($this->getMatchedAttributes());
        return array_diff($this->destinationAttributes, $matchedChannelAttributes);
    }

    // ##########################################################

    private function match()
    {
        $this->validate();

        if (!is_null($this->magentoProduct) && $this->magentoProduct->isGroupedType()) {
            $channelAttribute = reset($this->destinationAttributes);

            $this->matchedAttributes = array(
                Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL => $channelAttribute
            );

            return $this;
        }

        foreach ($this->getSourceAttributesData() as $magentoAttribute => $names) {
            $this->getResolver()->addSourceAttribute(
                $magentoAttribute, $this->prepareAttributeNames($magentoAttribute, $names)
            );
        }

        foreach ($this->getDestinationAttributesData() as $channelAttribute => $names) {
            $this->getResolver()->addDestinationAttribute(
                $channelAttribute, $this->prepareAttributeNames($channelAttribute, $names)
            );
        }

        $this->matchedAttributes = $this->getResolver()->resolve()->getResolvedAttributes();

        return $this;
    }

    private function validate()
    {
        if (is_null($this->marketplaceId)) {
            throw new Exception('Marketplace ID was not set.');
        }

        if (!$this->isAmountEqual()) {
            throw new Exception('Amounts of Source and Destination Attributes are not equal.');
        }
    }

    // ##########################################################

    private function getSourceAttributesData()
    {
        if (!is_null($this->magentoProduct)) {
            $magentoAttributesNames = $this->magentoProduct
                ->getVariationInstance()
                ->getTitlesVariationSet();

            $resultData = array();
            foreach ($magentoAttributesNames as $attribute => $data) {
                $resultData[$attribute] = $data['titles'];
            }

            return $resultData;
        }

        return array_fill_keys($this->sourceAttributes, array());
    }

    private function getDestinationAttributesData()
    {
        if (!$this->canUseDictionary) {
            return array_fill_keys($this->destinationAttributes, array());
        }

        $marketplaceDetails = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $marketplaceDetails->setMarketplaceId($this->marketplaceId);

        $resultData = array();
        foreach ($this->destinationAttributes as $attribute) {
            $resultData[$attribute] = $marketplaceDetails->getVocabularyAttributeNames($attribute);
        }

        return $resultData;
    }

    // ----------------------------------------------------------

    private function getResolver()
    {
        if (!is_null($this->resolver)) {
            return $this->resolver;
        }

        $this->resolver = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver');
        return $this->resolver;
    }

    private function prepareAttributeNames($attribute, $names)
    {
        if (!is_array($names)) {
            $names = array();
        }

        $names[] = $attribute;
        $names = array_unique($names);

        $names = array_map('trim', $names);
        $names = array_map('strtolower', $names);

        return $names;
    }

    // ##########################################################
}