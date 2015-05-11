<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_List_Validator_General
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Validator
{
    public function validate()
    {
        if (!$this->getListingProduct()->isListable()) {

            // M2ePro_TRANSLATIONS
            // Item is already on Rakuten.com, or not available.
            $this->addMessage('Item is already on Rakuten.com, or not available.');

            return false;
        }

        $generalId = $this->getBuyListingProduct()->getGeneralId();
        if (empty($generalId)) {
            $generalId = $this->getBuyListingProduct()->getListingSource()->getSearchGeneralId();

            if (!empty($generalId)) {
                $this->data['general_id_mode'] = $this->getBuyListing()->getGeneralIdMode();
            }
        }

        if (empty($generalId)) {
            $this->addMessage('Product cannot be Listed because Rakuten.com SKU is not specified.');
            return false;
        }

        $this->data['general_id'] = $generalId;

        if ($this->getVariationManager()->isVariationProduct() && !$this->validateVariationProductMatching()) {
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

}