<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Response
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'status' => Ess_M2ePro_Model_Listing_Product::STATUS_LISTED,
            'ignore_next_inventory_synch' => 1,
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendIdentifiersData($data);
        $data = $this->appendConditionValues($data);

        $data = $this->appendQtyValues($data);
        $data = $this->appendPriceValues($data);

        $data = $this->appendShippingValues($data);
        $data = $this->appendDispatchValues($data);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
    }

    // ########################################

    private function appendIdentifiersData($data)
    {
        $data['sku'] = $this->getRequestData()->getSku();
        $data['general_id'] = $this->getRequestData()->getGeneralId();
        $data['general_id_type'] = $this->getRequestData()->getGeneralIdType();

        return $data;
    }

    // ########################################
}