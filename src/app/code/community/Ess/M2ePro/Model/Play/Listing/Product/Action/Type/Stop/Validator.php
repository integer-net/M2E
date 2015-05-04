<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Validator
{
    // ########################################

    public function validate()
    {
        $params = $this->getParams();

        if (!$this->validateLockedObject()) {
            return false;
        }

        if (!$this->getListingProduct()->isStoppable()) {

            if (empty($params['remove'])) {

                // M2ePro_TRANSLATIONS
                // Item is not Listed or not available
                $this->addMessage('Item is not active or not available');

            } else {
                $this->getListingProduct()->deleteInstance();
            }

            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        if (!$this->validatePrice()) {
            return false;
        }

        return true;
    }

    // ########################################
}