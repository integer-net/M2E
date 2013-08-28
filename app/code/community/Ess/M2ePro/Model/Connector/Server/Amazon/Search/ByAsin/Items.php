<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Amazon_Search_ByAsin_Items
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('product','search','byAsin');
    }

    // ########################################

    protected function getQueryItems()
    {
        return array();
    }

    protected function getResponserModel()
    {
        return 'Amazon_Search_ByAsin_ItemsResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId(),
            'items' => $this->getQueryItems()
        );
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        return array(
            'items' => $this->getQueryItems()
        );
    }

    // ########################################
}