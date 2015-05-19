<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Relist_Request
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku' => $this->getPlayListingProduct()->getSku()
            ),
            $this->getRequestDetails()->getData(),
            $this->getRequestSelling()->getData()
        );

        return $data;
    }

    // ########################################
}