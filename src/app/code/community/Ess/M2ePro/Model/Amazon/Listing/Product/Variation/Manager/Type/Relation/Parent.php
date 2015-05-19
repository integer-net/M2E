<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_LogicalUnit
{
    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor $processor */
    private $processor;

    // ########################################

    public function getProcessor()
    {
        if (is_null($this->processor)) {
            $this->processor = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Manager'
                . '_Type_Relation_Parent_Processor');
            $this->processor->setListingProduct($this->getListingProduct());
        }

        return $this->processor;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product[]
     */
    public function getChildListingsProducts()
    {
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->addFieldToFilter('variation_parent_id',$this->getListingProduct()->getId());
        return $collection->getItems();
    }

    // ########################################

    public function isNeedProcessor()
    {
        return (bool)$this->getAmazonListingProduct()->getData('variation_parent_need_processor');
    }

    // ########################################

    public function hasChannelTheme()
    {
        return (bool)$this->getChannelTheme();
    }

    public function getChannelTheme()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        return !empty($additionalData['variation_channel_theme']) ?
                      $additionalData['variation_channel_theme'] : NULL;
    }

    // ----------------------------------------

    public function isChannelThemeSetManually()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['is_variation_channel_theme_set_manually'])) {
            return false;
        }

        return (bool)$additionalData['is_variation_channel_theme_set_manually'];
    }

    public function setIsChannelThemeSetManually($value = false, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['is_variation_channel_theme_set_manually'] = (bool)$value;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ----------------------------------------

    public function setChannelTheme($value, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_channel_theme'] = $value;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ########################################

    public function hasMatchedAttributes()
    {
        return (bool)$this->getMatchedAttributes();
    }

    public function getMatchedAttributes()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (empty($additionalData['variation_matched_attributes'])) {
            return NULL;
        }

        ksort($additionalData['variation_matched_attributes']);

        return $additionalData['variation_matched_attributes'];
    }

    // ----------------------------------------

    public function setMatchedAttributes(array $matchedAttributes, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_matched_attributes'] = $matchedAttributes;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ########################################

    public function getChannelAttributesSets()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        return !empty($additionalData['variation_channel_attributes_sets']) ?
                      $additionalData['variation_channel_attributes_sets'] : NULL;
    }

    public function setChannelAttributesSets(array $channelAttributesSets, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_channel_attributes_sets'] = $channelAttributesSets;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ----------------------------------------

    public function getChannelVariations()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        return !empty($additionalData['variation_channel_variations']) ?
               $additionalData['variation_channel_variations'] : NULL;
    }

    public function getChannelVariationGeneralId(array $options)
    {
        foreach ($this->getChannelVariations() as $asin => $variation) {
            if ($options == $variation) {
                return $asin;
            }
        }

        return null;
    }

    public function setChannelVariations(array $channelVariations, $save = true)
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_channel_variations'] = $channelVariations;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ----------------------------------------

    public function getRemovedProductOptions()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();
        return !empty($additionalData['variation_removed_product_variations']) ?
               $additionalData['variation_removed_product_variations'] : array();
    }

    public function isProductsOptionsRemoved(array $productOptions)
    {
        foreach ($this->getRemovedProductOptions() as $removedProductOptions) {
            if ($productOptions != $removedProductOptions) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function addRemovedProductOptions(array $productOptions, $save = true)
    {
        if ($this->isProductsOptionsRemoved($productOptions)) {
            return;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (!isset($additionalData['variation_removed_product_variations'])) {
            $additionalData['variation_removed_product_variations'] = array();
        }

        $additionalData['variation_removed_product_variations'][] = $productOptions;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    public function restoreRemovedProductOptions(array $productOptions, $save = true)
    {
        if (!$this->isProductsOptionsRemoved($productOptions)) {
            return;
        }

        $removedProductOptions = $this->getRemovedProductOptions();

        foreach ($removedProductOptions as $key => $removedOptions) {
            if ($productOptions != $removedOptions) {
                continue;
            }

            unset($removedProductOptions[$key]);
            break;
        }

        $additionalData = $this->getListingProduct()->getAdditionalData();
        $additionalData['variation_removed_product_variations'] = $removedProductOptions;

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $save && $this->getListingProduct()->save();
    }

    // ########################################

    public function getUsedProductOptions($freeOptionsFilter = false)
    {
        $usedVariations = array();

        foreach ($this->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationProductMatched() ||
                ($childTypeModel->isVariationChannelMatched() && $freeOptionsFilter)
            ) {
                continue;
            }

            if ($freeOptionsFilter
                && ($childListingProduct->isLocked()
                    || $childListingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ) {
                continue;
            }

            $usedVariations[] = $childTypeModel->getProductOptions();
        }

        return $usedVariations;
    }

    public function getUsedChannelOptions($freeOptionsFilter = false)
    {
        $usedOptions = array();

        foreach ($this->getChildListingsProducts() as $childListingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Child $childTypeModel */
            $childTypeModel = $childListingProduct->getChildObject()->getVariationManager()->getTypeModel();

            if (!$childTypeModel->isVariationChannelMatched() ||
                ($childTypeModel->isVariationProductMatched() && $freeOptionsFilter)
            ) {
                continue;
            }

            if ($freeOptionsFilter
                && ($childListingProduct->isLocked()
                    || $childListingProduct->getStatus() != Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED)
            ) {
                continue;
            }

            $usedOptions[] = $childTypeModel->getChannelOptions();
        }

        return $usedOptions;
    }

    // ----------------------------------------------------------

    public function getUnusedProductOptions()
    {
        return $this->getUnusedOptions($this->getCurrentProductOptions(), $this->getUsedProductOptions());
    }

    public function getNotRemovedUnusedProductOptions()
    {
        return $this->getUnusedOptions($this->getUnusedProductOptions(), $this->getRemovedProductOptions());
    }

    public function getUnusedChannelOptions()
    {
        return $this->getUnusedOptions($this->getChannelVariations(), $this->getUsedChannelOptions());
    }

    private function getUnusedOptions($currentOptions, $usedOptions)
    {
        if (empty($currentOptions)) {
            return array();
        }

        if (empty($usedOptions)) {
            return $currentOptions;
        }

        $unusedOptions = array();

        foreach ($currentOptions as $id => $currentOption) {

            $isExist = false;
            foreach ($usedOptions as $option) {
                if ($option != $currentOption) {
                    continue;
                }

                $isExist = true;
                break;
            }

            if ($isExist) {
                continue;
            }

            $unusedOptions[$id] = $currentOption;
        }

        return $unusedOptions;
    }

    // ----------------------------------------------------------

    private function getCurrentProductOptions()
    {
        $magentoProductVariations = $this->getMagentoProduct()->getVariationInstance()->getVariationsTypeStandard();

        $productOptions = array();

        foreach ($magentoProductVariations['variations'] as $option) {
            $productOption = array();

            foreach ($option as $attribute) {
                $productOption[$attribute['attribute']] = $attribute['option'];
            }

            $productOptions[] = $productOption;
        }

        return $productOptions;
    }

    // ########################################

    public function createChildListingProduct(array $productOptions = array(),
                                              array $channelOptions = array(),
                                              $generalId = null)
    {
        $data = array(
            'listing_id' => $this->getListingProduct()->getListingId(),
            'product_id' => $this->getListingProduct()->getProductId(),
            'status'     => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED,
            'general_id' => $generalId,
            'is_general_id_owner' => $this->getAmazonListingProduct()->isGeneralIdOwner(),
            'status_changer'   => Ess_M2ePro_Model_Listing_Product::STATUS_CHANGER_UNKNOWN,
            'is_variation_product'    => 1,
            'is_variation_parent'     => 0,
            'variation_parent_id'     => $this->getListingProduct()->getId(),
            'template_description_id' => $this->getAmazonListingProduct()->getTemplateDescriptionId(),
        );

        /** @var Ess_M2ePro_Model_Listing_Product $childListingProduct */
        $childListingProduct = Mage::helper('M2ePro/Component_Amazon')->getModel('Listing_Product')->setData($data);
        $childListingProduct->save();

        if (empty($productOptions) && empty($channelOptions)) {
            return $childListingProduct;
        }

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonChildListingProduct */
        $amazonChildListingProduct = $childListingProduct->getChildObject();

        $childTypeModel = $amazonChildListingProduct->getVariationManager()->getTypeModel();

        if (!empty($productOptions)) {
            $productVariation = $this->getListingProduct()
                ->getMagentoProduct()
                ->getVariationInstance()
                ->getVariationTypeStandard($productOptions);

            $childTypeModel->setProductVariation($productVariation);
        }

        if (!empty($channelOptions)) {
            $childTypeModel->setChannelVariation($channelOptions);
        }

        return $childListingProduct;
    }

    // ########################################

    public function clearTypeData()
    {
        parent::clearTypeData();

        $additionalData = $this->getListingProduct()->getAdditionalData();

        unset($additionalData['variation_channel_theme']);
        unset($additionalData['is_variation_channel_theme_set_manually']);

        unset($additionalData['variation_matched_attributes']);
        unset($additionalData['variation_channel_attributes_sets']);
        unset($additionalData['variation_channel_variations']);
        unset($additionalData['variation_removed_product_variations']);

        $this->getListingProduct()->setSettings('additional_data', $additionalData);
        $this->getListingProduct()->setData('variation_parent_need_processor', 0);
        $this->getListingProduct()->save();

        foreach ($this->getChildListingsProducts() as $child) {

            /** @var $child Ess_M2ePro_Model_Listing_Product */

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $childVariationManager */
            $childVariationManager = $child->getChildObject()->getVariationManager();

            $childVariationManager->getTypeModel()->unsetChannelVariation();
            $childVariationManager->setIndividualType();

            $child->save();
        }
    }

    // ########################################
}