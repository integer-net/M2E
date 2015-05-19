<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Stop_Response
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Response
{
    // ########################################

    public function processSuccess($params = array())
    {
        $data = array(
            'ignore_next_inventory_synch' => 1
        );

        $data = $this->appendStatusChangerValue($data);
        $data = $this->appendAfnChannelValues($data);

        $data = $this->appendQtyValues($data);

        $this->getListingProduct()->addData($data);
        $this->getListingProduct()->save();
    }

    // ########################################
}