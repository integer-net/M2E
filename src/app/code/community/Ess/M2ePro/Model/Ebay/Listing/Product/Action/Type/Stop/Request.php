<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Stop_Request
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Type_Request
{
    // ########################################

    public function getActionData()
    {
        return array(
            'item_id' => $this->getEbayListingProduct()->getEbayItemIdReal()
        );
    }

    protected function prepareFinalData(array $data)
    {
        $data = parent::prepareFinalData($data);
        unset($data['is_eps_ebay_images_mode']);
        return $data;
    }

    // ########################################

    protected function initializeVariations() {}

    // ########################################
}