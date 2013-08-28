<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Server_Ebay_Inventory_Get_Items
    extends Ess_M2ePro_Model_Connector_Server_Ebay_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('inventory','get','items');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Ebay_Inventory_Get_ItemsResponser';
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