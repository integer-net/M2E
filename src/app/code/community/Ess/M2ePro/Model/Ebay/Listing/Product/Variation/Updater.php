<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater
    extends Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    private $logsActionId = NULL;
    private $logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
    private $logsAction = Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING;

    // ########################################

    public function setLoggingData($logsActionId = NULL,
                                   $logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                   $logsAction = Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING)
    {
        $this->logsActionId = $logsActionId;
        $this->logsInitiator = $logsInitiator;
        $this->logsAction = $logsAction;
    }

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (is_null($listingProduct->getChildObject()->getTemplateCategoryId())) {
            return;
        }

        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getChildObject()->getCategoryTemplate()->isVariationMode()) {
            return;
        }

        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        $rawMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $rawMagentoVariations = $this->validateLimitsConditions($rawMagentoVariations,$listingProduct);

        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));

        $addedVariations = $this->getAddedVariations($magentoVariations,$currentVariations);
        $deletedVariations = $this->getDeletedVariations($magentoVariations,$currentVariations);

        $this->addNewVariations($listingProduct,$addedVariations);
        $this->markAsDeletedVariations($deletedVariations);

        $this->saveVariationsSets($listingProduct,$rawMagentoVariations);
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (is_null($listingProduct->getChildObject()->getTemplateCategoryId())) {
            return false;
        }

        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getChildObject()->getCategoryTemplate()->isVariationMode()) {
            return false;
        }

        if ($listingProduct->getMagentoProduct()->isProductWithoutVariations()) {
            return false;
        }

        $rawMagentoVariations = $listingProduct->getMagentoProduct()->getProductVariations();
        $rawMagentoVariations = $this->validateLimitsConditions($rawMagentoVariations,NULL);

        $magentoVariations = $this->prepareMagentoVariations($rawMagentoVariations);
        $currentVariations = $this->prepareCurrentVariations($listingProduct->getVariations(true));

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

    protected function validateLimitsConditions($sourceVariations,
                                                Ess_M2ePro_Model_Listing_Product $listingProduct = NULL)
    {
        $tempLog = Mage::getModel('M2ePro/Listing_Log');
        $tempLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        $failResult = array(
            'set' => array(),
            'variations' => array()
        );

        $set = $sourceVariations['set'];
        $variations = $sourceVariations['variations'];

        foreach ($set as $singleSet) {

            if (count($singleSet) > 30) {

                // Maximum 30 options by one attribute:
                // Color: Red, Blue, Green, ...

                !is_null($listingProduct) && $tempLog->addProductMessage(
                    $listingProduct->getListingId(),
                    $listingProduct->getProductId(),
                    $listingProduct->getId(),
                    $this->logsInitiator,
                    $this->logsActionId,
                    $this->logsAction,
            'The product will be listed as a simple product as it has limitation for multi-variation items. Reason: '.
            'number of values for each option more than 30.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return $failResult;
            }
        }

        foreach ($variations as $singleVariation) {

            if (count($singleVariation) > 5) {

                // Max 5 pair attribute-option:
                // Color: Blue, Size: XL, ...

                !is_null($listingProduct) && $tempLog->addProductMessage(
                    $listingProduct->getListingId(),
                    $listingProduct->getProductId(),
                    $listingProduct->getId(),
                    Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                    NULL,
                    Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
            'The product will be listed as a simple product as it has limitation for multi-variation items. Reason: '.
            'number of options more than 5.',
                    Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                    Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
                );

                return $failResult;
            }
        }

        if (count($variations) > 250) {

            // Not more that 250 possible variations

            !is_null($listingProduct) && $tempLog->addProductMessage(
                $listingProduct->getListingId(),
                $listingProduct->getProductId(),
                $listingProduct->getId(),
                Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                NULL,
                Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING,
            'The product will be listed as a simple product as it has limitation for multi-variation items. Reason: '.
            'sum of quantities of all possible products options more than 250.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );

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