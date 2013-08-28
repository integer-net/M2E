<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Updater extends
                                                                  Ess_M2ePro_Model_Listing_Product_Variation_Updater
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    private $logsActionId = NULL;
    private $logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN;
    private $logsAction = Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING;

    // ########################################

    public function __construct()
    {
        $this->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
    }

    // ########################################

    public function updateVariations(Ess_M2ePro_Model_Listing_Product $listingProduct,
                                     $logsActionId = NULL,
                                     $logsInitiator = Ess_M2ePro_Model_Log_Abstract::INITIATOR_UNKNOWN,
                                     $logsAction = Ess_M2ePro_Model_Listing_Log::ACTION_ADD_PRODUCT_TO_LISTING)
    {
        $this->listingProduct = $listingProduct;

        $this->logsActionId = $logsActionId;
        $this->logsInitiator = $logsInitiator;
        $this->logsAction = $logsAction;

        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode()) {
            return;
        }

        $variations = parent::updateVariations($listingProduct);
        $this->saveVariationsSets($listingProduct,$variations);
    }

    public function isAddedNewVariationsAttributes(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        if (!$listingProduct->getChildObject()->isListingTypeFixed() ||
            !$listingProduct->getGeneralTemplate()->getChildObject()->isVariationMode()) {
            return false;
        }

        return parent::isAddedNewVariationsAttributes($listingProduct);
    }

    // ########################################

    protected function validateChannelConditions($sourceVariations, $writeLogs = true)
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

                $writeLogs && $tempLog->addProductMessage(
                    $this->listingProduct->getListingId(),
                    $this->listingProduct->getProductId(),
                    $this->listingProduct->getId(),
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

                $writeLogs && $tempLog->addProductMessage(
                    $this->listingProduct->getListingId(),
                    $this->listingProduct->getProductId(),
                    $this->listingProduct->getId(),
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

            $writeLogs && $tempLog->addProductMessage(
                $this->listingProduct->getListingId(),
                $this->listingProduct->getProductId(),
                $this->listingProduct->getId(),
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

    private function saveVariationsSets(Ess_M2ePro_Model_Listing_Product $listingProduct,$variations)
    {
        if (!isset($variations['set'])) {
            return;
        }

        $additionalData = $listingProduct->getChildObject()->getData('additional_data');
        $additionalData = is_null($additionalData)
                          ? array()
                          : json_decode($additionalData,true);

        $additionalData['variations_sets'] = $variations['set'];

        $listingProduct->getChildObject()
                       ->setData('additional_data',json_encode($additionalData))
                       ->save();
    }

    // ########################################
}