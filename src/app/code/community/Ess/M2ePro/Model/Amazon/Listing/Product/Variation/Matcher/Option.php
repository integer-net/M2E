<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option
{
    // ##########################################################

    /** @var Ess_M2ePro_Model_Magento_Product $magentoProduct */
    private $magentoProduct = null;

    private $marketplaceId = null;

    private $destinationOptions = array();

    private $destinationOptionsNames = array();

    private $matchedAttributes = array();

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option_Resolver $resolver */
    private $resolver = null;

    // ##########################################################

    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $this->magentoProduct = $magentoProduct;
        return $this;
    }

    public function setMarketplaceId($marketplaceId)
    {
        $this->marketplaceId = $marketplaceId;
        return $this;
    }

    // ----------------------------------------------------------

    /**
     *  $destinationOptions = array(
     *      'B00005N5PF' => array(
     *         'Color' => 'Red',
     *         'Size'  => 'XL',
     *      ),
     *      ...
     *  )
     */
    public function setDestinationOptions(array $destinationOptions)
    {
        $this->destinationOptions      = $destinationOptions;
        $this->destinationOptionsNames = array();

        return $this;
    }

    public function setMatchedAttributes(array $matchedAttributes)
    {
        $this->matchedAttributes = $matchedAttributes;
        return $this;
    }

    // ##########################################################

    /**
     *  $sourceOption = array(
     *      'Color' => 'red',
     *      'Size'  => 'L',
     *  )
     */
    public function getMatchedOptionGeneralId(array $sourceOption)
    {
        $this->validate();

        $this->getResolver()
            ->setSourceOption($this->getSourceOptionNames($sourceOption))
            ->setDestinationOptions($this->getDestinationOptionNames())
            ->setMatchedAttributes($this->matchedAttributes);

        return $this->getResolver()->resolve()->getResolvedGeneralId();
    }

    // ##########################################################

    private function validate()
    {
        if (is_null($this->marketplaceId)) {
            throw new Exception('Marketplace ID was not set.');
        }

        if (is_null($this->magentoProduct)) {
            throw new Exception('Magento Product was not set.');
        }

        if (empty($this->destinationOptions)) {
            throw new Exception('Destination Options is empty.');
        }
    }

    // ----------------------------------------------------------

    private function getSourceOptionNames($sourceOption)
    {
        $magentoOptionNames = $this->magentoProduct->getVariationInstance()->getTitlesVariationSet();

        $resultNames = array();
        foreach ($magentoOptionNames as $attribute => $data) {
            $resultNames[$attribute] = $this->prepareOptionNames(
                $sourceOption[$attribute], $data['values'][$sourceOption[$attribute]]
            );
        }

        return $resultNames;
    }

    private function getDestinationOptionNames()
    {
        if (!empty($this->destinationOptionsNames)) {
            return $this->destinationOptionsNames;
        }

        $marketplaceDetails = Mage::getModel('M2ePro/Amazon_Marketplace_Details');
        $marketplaceDetails->setMarketplaceId($this->marketplaceId);

        foreach ($this->destinationOptions as $generalId => $destinationOption) {
            foreach ($destinationOption as $attributeName => $optionName) {
                $this->destinationOptionsNames[$generalId][$attributeName] = $this->prepareOptionNames(
                    $optionName, $marketplaceDetails->getVocabularyOptionNames($attributeName, $optionName)
                );
            }
        }

        return $this->destinationOptionsNames;
    }

    // ##########################################################

    private function getResolver()
    {
        if (!is_null($this->resolver)) {
            return $this->resolver;
        }

        $this->resolver = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Matcher_Option_Resolver');
        return $this->resolver;
    }

    private function prepareOptionNames($option, $names)
    {
        $names[] = $option;
        $names = array_unique($names);

        $names = array_map('trim', $names);
        $names = array_map('strtolower', $names);

        return $names;
    }

    // ##########################################################
}