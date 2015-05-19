<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Revise_Response
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'ignore_next_inventory_synch' => 1,
            'synch_status'  => Ess_M2ePro_Model_Listing_Product::SYNCH_STATUS_OK,
            'synch_reasons' => NULL,
        );

        $data = $this->appendStatusChangerValue($data);

        $data = $this->appendConditionValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $data = $this->appendShippingValues($data);
        $data = $this->appendDispatchValues($data);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
    }

    // ########################################
}