<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders_Receive_ServerRequester
    extends Ess_M2ePro_Model_Connector_Server_Buy_Orders_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Buy_Synchronization_Tasks_Orders_Receive_ServerResponser';
    }

    protected function setLocks($hash)
    {
        /** @var $tempModel Ess_M2ePro_Model_Buy_Synchronization_Tasks_Orders_Receive_Requester */
        $tempModel = Mage::getModel('M2ePro/Buy_Synchronization_Tasks_Orders_Receive_Requester');
        $tempModel->initialize($this->params,$this->marketplace,$this->account);
        $tempModel->setLocks($hash);
    }

    // ########################################
}