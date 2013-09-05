<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Category_Get_Suggested
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Abstract
{
    protected function getCommand()
    {
        return array('category', 'get', 'suggested');
    }

    protected function getRequestData()
    {
        return array(
            'query' => $this->params['query']
        );
    }

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return array();
        }

        return $response;
    }

    protected function getConnectionTimeout()
    {
        return 30;
    }
}