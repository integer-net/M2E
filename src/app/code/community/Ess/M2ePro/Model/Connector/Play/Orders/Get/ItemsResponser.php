<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Orders_Get_ItemsResponser
    extends Ess_M2ePro_Model_Connector_Play_Responser
{
    // ########################################

    protected function unsetLocks($fail = false, $message = NULL) {}

    // ########################################

    protected function validateResponseData($response)
    {
        if (!isset($response['orders'])) {
            return false;
        }

        return true;
    }

    protected function processResponseData($response)
    {
        return $response['orders'];
    }

    // ########################################
}