<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    protected $componentMode = NULL;

    // ########################################

    public function setComponentMode($mode)
    {
        $mode = strtolower((string)$mode);
        $mode && $this->componentMode = $mode;
        return $this;
    }

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return false;
        }

        // Prepare Magento Variations
        //-----------------------------
        $tempMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $tempMagentoVariations = $this->validateChannelConditions($tempMagentoVariations,true);
        //-----------------------------

        // Get Variations
        //-----------------------------
        $magentoVariations = $this->prepareMagentoVariations($tempMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));
        //-----------------------------

        // Get Variations Changes
        //-----------------------------
        $addedVariations = $this->getAddedVariations($magentoVariations,$currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations,$currentVariations);
        //-----------------------------

        // Add And Mark As Delete from DB
        //-----------------------------
        $this->addVariations($listingProduct,$addedVariations);
        $this->markAsDeleteVariations($deletedVariations);
        //-----------------------------

        return $tempMagentoVariations;
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return false;
        }

        // Prepare Magento Variations
        //-----------------------------
        $tempMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $tempMagentoVariations = $this->validateChannelConditions($tempMagentoVariations,false);
        //-----------------------------

        // Get Variations
        //-----------------------------
        $magentoVariations = $this->prepareMagentoVariations($tempMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));
        //-----------------------------

        if (!isset($magentoVariations[0]) && !isset($currentVariations[0])) {
            return false;
        }

        if (!isset($magentoVariations[0]) || !isset($currentVariations[0])) {
            return true;
        }

        if (count($magentoVariations[0]['options']) != count($currentVariations[0]['options'])) {
            return true;
        }

        return false;
    }

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

    //-----------------------------------------

    protected function getAddedVariations($magentoVariations, $currentVariations)
    {
        $result = array();

        foreach ($magentoVariations as $mVariation) {
            $isExistVariation = false;
            $cVariationExist = NULL;
            foreach ($currentVariations as $cVariation) {
                if ($this->isEqualVariations($mVariation['options'],$cVariation['options'])) {
                    $isExistVariation = true;
                    $cVariationExist = $cVariation;
                    break;
                }
            }
            if (!$isExistVariation) {
                $result[] = $mVariation;
            } else {
                if ((int)$cVariationExist['variation']['delete'] ==
                    Ess_M2ePro_Model_Listing_Product_Variation::DELETE_YES) {
                    $result[] = $cVariationExist;
                }
            }
        }

        return $result;
    }

    protected function getDeletedVariations($magentoVariations, $currentVariations)
    {
        $result = array();

        foreach ($currentVariations as $cVariation) {
            if ((int)$cVariation['variation']['delete'] == Ess_M2ePro_Model_Listing_Product_Variation::DELETE_YES) {
                continue;
            }
            $isExistVariation = false;
            foreach ($magentoVariations as $mVariation) {
                if ($this->isEqualVariations($mVariation['options'],$cVariation['options'])) {
                    $isExistVariation = true;
                    break;
                }
            }
            if (!$isExistVariation) {
                $result[] = $cVariation;
            }
        }

        return $result;
    }

    // ########################################

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

    //-----------------------------------------

    protected function addVariations(Ess_M2ePro_Model_Listing_Product $listingProduct, $addedVariations)
    {
        foreach ($addedVariations as $aVariation) {

            if (isset($aVariation['variation']['id'])) {
                $dataForUpdate = array(
                    'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_YES,
                    'delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO
                );
                Mage::helper('M2ePro/Component')->getComponentObject(
                    $this->componentMode,'Listing_Product_Variation',$aVariation['variation']['id']
                )
                        ->addData($dataForUpdate)
                        ->save();
                continue;
            }

            $dataForAdd = array(
                'listing_product_id' => $listingProduct->getId(),
                'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_YES,
                'delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_NO,
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            );
            $newVariationId = Mage::helper('M2ePro/Component')->getComponentModel(
                $this->componentMode,'Listing_Product_Variation'
            )->addData($dataForAdd)->save()->getId();

            foreach ($aVariation['options'] as $aOption) {
                $dataForAdd = array(
                    'listing_product_variation_id' => $newVariationId,
                    'product_id' => $aOption['product_id'],
                    'product_type' => $aOption['product_type'],
                    'attribute' => $aOption['attribute'],
                    'option' => $aOption['option']
                );
                Mage::helper('M2ePro/Component')->getComponentModel(
                    $this->componentMode,'Listing_Product_Variation_Option'
                )->addData($dataForAdd)->save();
            }
        }
    }

    protected function markAsDeleteVariations($deletedVariations)
    {
        foreach ($deletedVariations as $dVariation) {
            if ($dVariation['variation']['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {
                Mage::helper('M2ePro/Component')->getComponentObject(
                    $this->componentMode,'Listing_Product_Variation',$dVariation['variation']['id']
                )->deleteInstance();
            } else {
                $dataForUpdate = array(
                    'add' => Ess_M2ePro_Model_Listing_Product_Variation::ADD_NO,
                    'delete' => Ess_M2ePro_Model_Listing_Product_Variation::DELETE_YES
                );
                Mage::helper('M2ePro/Component')->getComponentObject(
                    $this->componentMode,'Listing_Product_Variation',$dVariation['variation']['id']
                )->addData($dataForUpdate)->save();
            }
        }
    }

    // ########################################

    abstract protected function validateChannelConditions($sourceVariations, $writeLogs = true);

    // ########################################
}