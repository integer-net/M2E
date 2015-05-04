<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Matcher_Option_Resolver
{
    // ##########################################################

    private $sourceOption = array();

    private $destinationOptions = array();

    private $matchedAttributes = array();

    private $resolvedGeneralId = null;

    // ##########################################################

    public function setSourceOption(array $options)
    {
        $this->sourceOption      = $options;
        $this->resolvedGeneralId = null;

        return $this;
    }

    public function setDestinationOptions(array $options)
    {
        $this->destinationOptions = $options;
        $this->resolvedGeneralId  = null;

        return $this;
    }

    // ----------------------------------------------------------

    public function setMatchedAttributes(array $matchedAttributes)
    {
        $this->matchedAttributes = $matchedAttributes;
        return $this;
    }

    // ##########################################################

    public function resolve()
    {
        foreach ($this->destinationOptions as $generalId => $destinationOption) {
            if (count($this->sourceOption) != count($destinationOption)) {
                continue;
            }

            $isResolved = false;

            foreach ($destinationOption as $destinationAttribute => $destinationOptionNames) {
                $sourceAttribute = array_search($destinationAttribute, $this->matchedAttributes);
                $sourceOptionNames = $this->sourceOption[$sourceAttribute];

                if (count(array_intersect($sourceOptionNames, $destinationOptionNames)) > 0) {
                    $isResolved = true;
                    continue;
                }

                $isResolved = false;
                break;
            }

            if ($isResolved) {
                $this->resolvedGeneralId = $generalId;
                break;
            }
        }

        return $this;
    }

    public function getResolvedGeneralId()
    {
        return $this->resolvedGeneralId;
    }

    // ##########################################################
}