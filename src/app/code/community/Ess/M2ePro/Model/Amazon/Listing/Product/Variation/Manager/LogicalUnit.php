<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_LogicalUnit
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Abstract
{
    // ########################################

    public function isActualProductAttributes()
    {
        $productAttributes = array_map('strtolower', (array)$this->getProductAttributes());
        $magentoAttributes = array_map('strtolower', (array)$this->getCurrentMagentoAttributes());

        return !array_diff($productAttributes, $magentoAttributes);
    }

    // ########################################

    public function getProductAttributes()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (empty($additionalData['variation_product_attributes'])) {
            return NULL;
        }

        sort($additionalData['variation_product_attributes']);

        return $additionalData['variation_product_attributes'];
    }

    public function resetProductAttributes($save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_product_attributes'] = $this->getCurrentMagentoAttributes();

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ########################################

    public function clearTypeData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        unset($additionalData['variation_product_attributes']);
        $this->getListingProduct()->setSettings('additional_data', $additionalData);

        $this->getListingProduct()->save();
    }

    // ########################################
}