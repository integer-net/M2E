<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Selling
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Abstract
{
    // ##########################################################

    protected function check() {}

    protected function execute()
    {
        $qty = null;
        $price = null;

        foreach ($this->getProcessor()->getChildListingProducts() as $listingProduct) {
            if ($listingProduct->isNotListed()) {
                continue;
            }

            /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
            $amazonListingProduct = $listingProduct->getChildObject();

            $qty = (int)$qty + (int)$amazonListingProduct->getOnlineQty();

            if (is_null($price) || $price > (float)$amazonListingProduct->getOnlinePrice()) {
                $price = (float)$amazonListingProduct->getOnlinePrice();
            }
        }

        $this->getProcessor()->getListingProduct()->addData(array(
            'online_qty'   => $qty,
            'online_price' => $price,
        ));
    }

    // ##########################################################
}