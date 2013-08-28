<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Buy_Orders_Get_Items
    extends Ess_M2ePro_Model_Connector_Server_Buy_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Buy_Orders_Get_ItemsResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
            'marketplace_id' => $this->marketplace->getId()
        );
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        return array();
    }

    // ########################################
}