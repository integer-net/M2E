<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Listing_Product_Action_Type_List_Request
    extends Ess_M2ePro_Model_Play_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        $data = array_merge(
            array(
                'sku'             => $this->validatorsData['sku'],
                'general_id'      => $this->validatorsData['general_id'],
                'general_id_type' => $this->validatorsData['general_id_type'],
            ),
            $this->getRequestDetails()->getData(),
            $this->getRequestSelling()->getData()
        );

        return $data;
    }

    // ########################################
}