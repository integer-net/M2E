<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Api_Virtual extends Ess_M2ePro_Model_Connector_Server_Api_Abstract
{
    // ########################################

    protected function getCommand()
    {
        $command = $this->params['__command__'];
        unset($this->params['__command__']);
        return $command;
    }

    // ########################################

    protected function getRequestData()
    {
        $requestData = $this->params['__request_data__'];
        unset($this->params['__request_data__']);
        return $requestData;
    }

    //----------------------------------------

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if (!is_null($this->params['__response_data_key__'])) {
            if (isset($response[$this->params['__response_data_key__']])) {
                return $response[$this->params['__response_data_key__']];
            } else {
                return NULL;
            }
        }
        return $response;
    }

    // ########################################
}