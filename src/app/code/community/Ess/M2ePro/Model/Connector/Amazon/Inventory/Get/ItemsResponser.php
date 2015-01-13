<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Amazon_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Amazon_Responser
{
    // ########################################

    protected function unsetLocks($fail = false, $message = NULL) {}

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['data'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        return $response;
    }

    // ########################################
}