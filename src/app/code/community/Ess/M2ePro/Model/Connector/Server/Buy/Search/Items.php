<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Search_Items
    extends Ess_M2ePro_Model_Connector_Server_Buy_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byQuery');
    }

    // ########################################

    protected function getQueryString()
    {
        return $this->params['query'];
    }

    protected function getResponserModel()
    {
        return 'Buy_Search_ItemsResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId(),
            'query' => $this->getQueryString()
        );
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        return array(
            'query' => $this->getQueryString()
        );
    }

    // ########################################
}