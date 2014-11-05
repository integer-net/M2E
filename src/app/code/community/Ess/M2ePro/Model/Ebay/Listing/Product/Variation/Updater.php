<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    const VALIDATE_MESSAGE_DATA_KEY = '_validate_limits_conditions_message_';

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getMagentoProduct()->isProductWithVariations()) {
            return;
        }

        $rawMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $rawMagentoVariations = Mage::helper('M2ePro/Component_Ebay')
                                            ->reduceOptionsForVariations($rawMagentoVariations);
        $rawMagentoVariations = $this->validateLimitsConditions($rawMagentoVariations,$listingProduct);

        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));

        $addedVariations = $this->getAddedVariations($magentoVariations,$currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations,$currentVariations);

        $this->addNewVariations($listingProduct,$addedVariations);
        $this->markAsDeletedVariations($deletedVariations);

        $this->saveVariationsSets($listingProduct,$rawMagentoVariations);
    }

//    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
//    {
//        if (!$listingProduct->getChildObject()->isVariationsMode()) {
//            return false;
//        }
//
//        $rawMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
//        $rawMagentoVariations = $this->validateLimitsConditions($rawMagentoVariations,NULL);
//
//        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);
//        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));
//
//        if (!isset($magentoVariations[0]) && !isset($currentVariations[0])) {
//            return false;
//        }
//
//        if (!isset($magentoVariations[0]) || !isset($currentVariations[0])) {
//            return true;
//        }
//
//        if (count($magentoVariations[0]['options']) != count($currentVariations[0]['options'])) {
//            return true;
//        }
//
//        return false;
//    }

    // ########################################

    protected function validateLimitsConditions($sourceVariations,
                                                Ess_M2ePro_Model_Listing_Product $listingProduct = NULL)
    {
        $failResult = array(
            'set' => array(),
            'variations' => array()
        );

        $set = $sourceVariations['set'];
        $variations = $sourceVariations['variations'];

        foreach ($set as $singleSet) {

            if (count($singleSet) > 60) {

                // Maximum 60 options by one attribute:
                // Color: Red, Blue, Green, ...

                if (!is_null($listingProduct)) {
                    $listingProduct->setData(self::VALIDATE_MESSAGE_DATA_KEY,
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: number of values for each option more than 60.'
                    );
                }

                return $failResult;
            }
        }

        foreach ($variations as $singleVariation) {

            if (count($singleVariation) > 5) {

                // Max 5 pair attribute-option:
                // Color: Blue, Size: XL, ...

                if (!is_null($listingProduct)) {
                    $listingProduct->setData(self::VALIDATE_MESSAGE_DATA_KEY,
                    'The product was listed as a simple product as it has limitation for multi-variation items. '.
                    'Reason: number of options more than 5.'
                    );
                }

                return $failResult;
            }
        }

        if (count($variations) > 250) {

            // Not more that 250 possible variations

            if (!is_null($listingProduct)) {
                $listingProduct->setData(self::VALIDATE_MESSAGE_DATA_KEY,
                'The product was listed as a simple product as it has limitation for multi-variation items. '.
                'Reason: sum of quantities of all possible products options more than 250.'
                );
            }

            return $failResult;
        }

        return $sourceVariations;
    }

    protected function saveVariationsSets(Ess_M2ePro_Model_Listing_Product $listingProduct,$variations)
    {
        if (!isset($variations['set'])) {
            return;
        }

        $additionalData = $listingProduct->getData('additional_data');
        $additionalData = is_null($additionalData)
                          ? array()
                          : json_decode($additionalData,true);

        $additionalData['variations_sets'] = $variations['set'];

        $listingProduct->setData('additional_data',json_encode($additionalData))
                       ->save();
    }

    // ########################################

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
                if ((bool)$cVariationExist['variation']['delete']) {
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

            if ((bool)$cVariation['variation']['delete']) {
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

    // ----------------------------------------

    protected function addNewVariations(Ess_M2ePro_Model_Listing_Product $listingProduct, $addedVariations)
    {
        foreach ($addedVariations as $aVariation) {

            if (isset($aVariation['variation']['id'])) {

                $dataForUpdate = array(
                    'add' => 1,
                    'delete' => 0
                );

                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $aVariation['variation']['id']
                )->addData($dataForUpdate)->save();

                continue;
            }

            $dataForAdd = array(
                'listing_product_id' => $listingProduct->getId(),
                'add' => 1,
                'delete' => 0,
                'status' => Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED
            );

            $newVariationId = Mage::helper('M2ePro/Component')->getComponentModel(
                Ess_M2ePro_Helper_Component_Ebay::NICK, 'Listing_Product_Variation'
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
                    Ess_M2ePro_Helper_Component_Ebay::NICK,'Listing_Product_Variation_Option'
                )->addData($dataForAdd)->save();
            }
        }
    }

    protected function markAsDeletedVariations($deletedVariations)
    {
        foreach ($deletedVariations as $dVariation) {

            if ($dVariation['variation']['status'] == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED) {

                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $dVariation['variation']['id']
                )->deleteInstance();

            } else {

                $dataForUpdate = array(
                    'add' => 0,
                    'delete' => 1
                );

                Mage::helper('M2ePro/Component')->getComponentObject(
                    Ess_M2ePro_Helper_Component_Ebay::NICK,
                    'Listing_Product_Variation',
                    $dVariation['variation']['id']
                )->addData($dataForUpdate)->save();
            }
        }
    }

    // ########################################
}