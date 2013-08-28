<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_ServerRequester
    extends Ess_M2ePro_Model_Connector_Server_Amazon_Orders_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Amazon_Synchronization_Tasks_Orders_ServerResponser';
    }

    protected function setLocks($hash)
    {
        /** @var $tempModel Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Requester */
        $tempModel = Mage::getModel('M2ePro/Amazon_Synchronization_Tasks_Orders_Requester');
        $tempModel->initialize($this->params,$this->marketplace,$this->account);
        $tempModel->setLocks($hash);
    }

    // ########################################
}