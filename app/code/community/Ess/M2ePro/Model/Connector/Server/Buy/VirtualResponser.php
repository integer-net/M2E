<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_VirtualResponser
    extends Ess_M2ePro_Model_Connector_Server_Buy_Responser
{
    // ########################################

    protected function unsetLocks($isFailed = false, $message = NULL) {}

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function processResponseData($response)
    {
        return $response;
    }

    // ########################################
}