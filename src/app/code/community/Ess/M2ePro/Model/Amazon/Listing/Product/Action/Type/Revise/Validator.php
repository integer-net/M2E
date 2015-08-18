<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Revise_Validator
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        if (!$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getAmazonListingProduct()->isAfnChannel() && $this->getConfigurator()->isQtyAllowed()) {

            $this->getConfigurator()->disallowQty();

            // M2ePro_TRANSLATIONS
            // This Product is an FBA Item, so it’s Quantity updating will change it to MFN. Thus QTY feed, Handling Time and Restock Date Values will not be updated. Inventory management for FBA Items is currently unavailable in M2E Pro. However, you can do that directly in your Amazon Seller Central.
            $this->addMessage(
                'This Product is an FBA Item, so it’s Quantity updating will change it to MFN. Thus QTY feed, Handling
                Time and Restock Date Values will not be updated. Inventory management for FBA Items is currently
                unavailable in M2E Pro. However, you can do that directly in your Amazon Seller Central.',
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING
            );
        }

        if ($this->getVariationManager()->isPhysicalUnit() && !$this->validatePhysicalUnitMatching()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->getAmazonListingProduct()->isAfnChannel() &&
            (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isRevisable())
        ) {

            // M2ePro_TRANSLATIONS
            // Item is not Listed or not available
            $this->addMessage('Item is not Listed or not available');

            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    // ########################################
}