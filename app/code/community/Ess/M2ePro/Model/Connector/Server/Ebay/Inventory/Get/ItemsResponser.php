<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Inventory_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Responser
{
    // ########################################

    protected function unsetLocks($fail = false, $message = NULL) {}

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['items']) ||
            !isset($response['to_time'])) {
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