<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $options = array();

        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {

            $listingProduct->setData('is_variation_product',
                                     Ess_M2ePro_Model_Buy_Listing_Product::IS_VARIATION_PRODUCT_NO)
                           ->save();

            if ($listingProduct->getChildObject()->isVariationMatched()) {
                $listingProduct->getChildObject()->updateVariationOptions($options);
                $listingProduct->getChildObject()->unsetMatchedVariation();
            }

            return;
        }

        $listingProduct->setData('is_variation_product',
                                 Ess_M2ePro_Model_Buy_Listing_Product::IS_VARIATION_PRODUCT_YES)
                       ->save();

        $magentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        foreach ($magentoVariations['set'] as $attribute => $value) {
            $options[] = array(
                'attribute' => $attribute,
                'option' => NULL
            );
        }

        if (!$listingProduct->getChildObject()->isVariationMatched()) {
            $listingProduct->getChildObject()->updateVariationOptions($options);
            return;
        }

        // observe variation removal in Magento

        $currentVariation = $this->prepareCurrentVariations($listingProduct->getVariations(true));
        if (!isset($currentVariation[0]) || !isset($currentVariation[0]['options'])) {
            return;
        }
        $currentVariation = reset($currentVariation);
        $magentoVariations = $this->prepareMagentoVariations($magentoVariations);

        foreach ($magentoVariations as $magentoVariation) {
            if ($this->isEqualVariations($magentoVariation['options'],$currentVariation['options'])) {
                return;
            }
        }

        foreach ($listingProduct->getVariations(true) as $variation) {
            $variation->deleteInstance();
        }

        $listingProduct->getChildObject()->updateVariationOptions($options);
        $listingProduct->getChildObject()->unsetMatchedVariation();
    }

    // ########################################
}