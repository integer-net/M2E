<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    const VALIDATE_MESSAGE_DATA_KEY = '_validate_limits_conditions_message_';

    // ########################################

    public function process(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getMagentoProduct()->isProductWithVariations()) {
            return;
        }

        $rawMagentoVariations = $listingProduct->getMagentoProduct()
                                               ->getVariationInstance()
                                               ->getVariationsTypeStandard();
        $rawMagentoVariations = Mage::helper('M2ePro/Component_Ebay')
                                            ->reduceOptionsForVariations($rawMagentoVariations);

        $rawMagentoVariations = $this->validateExistenceConditions($rawMagentoVariations,$listingProduct);
        $rawMagentoVariations = $this->validateLimitsConditions($rawMagentoVariations,$listingProduct);

        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));

        $addedVariations = $this->getAddedVariations($magentoVariations,$currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations,$currentVariations);

        $this->addNewVariations($listingProduct,$addedVariations);
        $this->markAsDeletedVariations($deletedVariations);

        $this->saveVariationsSets($listingProduct,$rawMagentoVariations);
    }

    // ########################################

    protected function validateExistenceConditions($sourceVariations,
                                                   Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!isset($sourceVariations['set']) || !isset($sourceVariations['variations']) ||
            !is_array($sourceVariations['set']) || !is_array($sourceVariations['variations']) ||
            !count($sourceVariations['set']) || !count($sourceVariations['variations'])) {

            $listingProduct->setData(
                self::VALIDATE_MESSAGE_DATA_KEY,
                'The Product was Listed as a Simple Product because M2E Pro
                 cannot retrieve Magento variations from this Product.'
            );

            return array(
                'set' => array(),
                'variations' => array()
            );
        }

        return $sourceVariations;
    }

    protected function validateLimitsConditions($sourceVariations,
                                                Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (count($sourceVariations['set']) > 5) {

            // Max 5 pair attribute-option:
            // Color: Blue, Size: XL, ...

            $listingProduct->setData(self::VALIDATE_MESSAGE_DATA_KEY,
            'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
            'Reason: number of Options more than 5.'
            );

            return array(
                'set' => array(),
                'variations' => array()
            );
        }

        foreach ($sourceVariations['set'] as $singleSet) {

            if (count($singleSet) > 60) {

                // Maximum 60 options by one attribute:
                // Color: Red, Blue, Green, ...

                $listingProduct->setData(
                    self::VALIDATE_MESSAGE_DATA_KEY,
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: number of values for each Option more than 60.'
                );

                return array(
                    'set' => array(),
                    'variations' => array()
                );
            }
        }

        if (count($sourceVariations['variations']) > 250) {

            // Not more that 250 possible variations

            $listingProduct->setData(self::VALIDATE_MESSAGE_DATA_KEY,
            'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
            'Reason: sum of quantities of all possible Products options more than 250.'
            );

            return array(
                'set' => array(),
                'variations' => array()
            );
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

    private function getAddedVariations($magentoVariations, $currentVariations)
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

    private function getDeletedVariations($magentoVariations, $currentVariations)
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

    private function addNewVariations(Ess_M2ePro_Model_Listing_Product $listingProduct, $addedVariations)
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

    private function markAsDeletedVariations($deletedVariations)
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

    private function prepareMagentoVariations($variations)
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

    private function prepareCurrentVariations($variations)
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

    private function isEqualVariations($magentoVariation, $currentVariation)
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