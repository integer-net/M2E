<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Play_Orders_Get_Items
    extends Ess_M2ePro_Model_Connector_Play_Requester
{
    // ########################################

    public function getCommand()
    {
        return array('orders','get','items');
    }

    // ########################################

    protected function getResponserModel()
    {
        return 'Play_Orders_Get_ItemsResponser';
    }

    protected function getResponserParams()
    {
        return array(
            'account_id' => $this->account->getId(),
        );
    }

    // ########################################

    protected function setLocks($hash) {}

    // ########################################

    protected function getRequestData()
    {
        return array(
            'from_date' => $this->params['from_date']
        );
    }

    // ########################################
}