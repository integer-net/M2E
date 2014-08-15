<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_Items
    extends Ess_M2ePro_Model_Connector_Ebay_Requester
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

    protected function getPerformType()
    {
        return Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_PARTIAL;
    }

    // ########################################
}