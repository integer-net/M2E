<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    abstract public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct);

    // ########################################

    protected function prepareMagentoVariations($variations)
    {
        $result = array();

        if (isset($variations['variations'])) {
            $variations = $variations['variations'];
        }

        foreach ($variations as $variation) {
            $result[] = array(
                'variation' => array(),
                'options' => $variation
            );
        }

        return $result;
    }

    protected function prepareCurrentVariations($variations)
    {
        $result = array();

        foreach ($variations as $variation) {

            /** @var Ess_M2ePro_Model_Listing_Product_Variation $variation */

            $temp = array(
                'variation' => $variation->getData(),
                'options' => array()
            );

            foreach ($variation->getOptions(false) as $option) {
                $temp['options'][] = $option;
            }

            $result[] = $temp;
        }

        return $result;
    }

    // ----------------------------------------

    protected function isEqualVariations($magentoVariation, $currentVariation)
    {
        if (count($magentoVariation) != count($currentVariation)) {
            return false;
        }

        foreach ($magentoVariation as $mOption) {

            $haveOption = false;

            foreach ($currentVariation as $cOption) {
                if ($mOption['attribute'] == $cOption['attribute'] &&
                    $mOption['option'] == $cOption['option']) {
                    $haveOption = true;
                    break;
                }
            }

            if (!$haveOption) {
                return false;
            }
        }

        return true;
    }

    // ########################################
}