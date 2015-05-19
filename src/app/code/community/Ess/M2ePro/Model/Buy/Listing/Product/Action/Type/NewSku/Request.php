<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_NewSku_Request
    extends Ess_M2ePro_Model_Buy_Listing_Product_Action_Type_Request
{
    // ########################################

    protected function getActionData()
    {
        return $this->getRequestNewProduct()->getData();
    }

    // -----------------------------------------

    protected function prepareFinalData(array $data)
    {
        return $data;
    }

    // ########################################
}