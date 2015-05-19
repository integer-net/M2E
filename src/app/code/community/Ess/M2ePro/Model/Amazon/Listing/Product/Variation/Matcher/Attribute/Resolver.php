<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Attribute_Resolver
{
    // ##########################################################

    private $sourceAttributes = array();

    private $sourceAttributesNames = array();

    private $destinationAttributes = array();

    private $destinationAttributesNames = array();

    private $resolvedAttributes = array();

    // ##########################################################

    public function addSourceAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->sourceAttributes)) {
            return $this;
        }

        $this->sourceAttributes[] = $attribute;
        $this->sourceAttributesNames[$attribute] = $names;

        return $this;
    }

    public function addDestinationAttribute($attribute, array $names)
    {
        if (in_array($attribute, $this->destinationAttributes)) {
            return $this;
        }

        $this->destinationAttributes[] = $attribute;
        $this->destinationAttributesNames[$attribute] = $names;

        return $this;
    }

    // ##########################################################

    public function resolve()
    {
        $matchedAttributes = array();

        foreach ($this->sourceAttributes as $magentoAttribute) {
            $matchedAttributes[$magentoAttribute] = null;

            $sourceNames = $this->sourceAttributesNames[$magentoAttribute];

            foreach ($this->destinationAttributes as $channelAttribute) {
                $destinationNames = $this->destinationAttributesNames[$channelAttribute];

                if (count(array_intersect($sourceNames, $destinationNames)) > 0 &&
                    !in_array($channelAttribute, $matchedAttributes)) {

                    $matchedAttributes[$magentoAttribute] = $channelAttribute;
                    break;
                }
            }
        }

        $this->resolvedAttributes = $matchedAttributes;

        return $this;
    }

    public function getResolvedAttributes()
    {
        return $this->resolvedAttributes;
    }

    // ##########################################################
}