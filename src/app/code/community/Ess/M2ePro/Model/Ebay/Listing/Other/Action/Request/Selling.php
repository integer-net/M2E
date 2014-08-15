<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request_Selling
    extends Ess_M2ePro_Model_Ebay_Listing_Other_Action_Request
{
    // ########################################

    public function getData()
    {
        return array_merge(
            $this->getQtyData(),
            $this->getPriceData()
        );
    }

    // ########################################

    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQty()) {
            return array();
        }

        $qty = $this->getEbayListingOther()->getMappedQty();

        if (is_null($qty)) {
            return array();
        }

        return array(
            'qty' => $qty
        );
    }

    public function getPriceData()
    {
        if (!$this->getConfigurator()->isPrice()) {
            return array();
        }

        $price = $this->getEbayListingOther()->getMappedPrice();

        if (is_null($price)) {
            return array();
        }

        return array(
            'price_fixed' => $price
        );
    }

    // ########################################
}