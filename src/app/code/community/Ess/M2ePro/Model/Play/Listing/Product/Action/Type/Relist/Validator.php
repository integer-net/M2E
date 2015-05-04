<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Relist_Validator
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function isValid()
    {
        if (!$this->validateLockedObject()) {
            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->getListingProduct()->isRelistable()) {

            // M2ePro_TRANSLATIONS
            // The Item either is listed, or not listed yet or not available
            $this->addMessage('The Item either is listed, or not listed yet or not available');

            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        if (!$this->validateQty()) {
            return false;
        }

        return true;
    }

    // ########################################
}