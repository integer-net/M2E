<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts_ServerRequester
    extends Ess_M2ePro_Model_Connector_Server_Play_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Play_Synchronization_Tasks_Defaults_UpdateListingsProducts_ServerResponser';
    }

    protected function setLocks($hash)
    {
        /** @var $tempModel Ess_M2ePro_Model_Play_Synchronization_Tasks_Defaults_UpdateListingsProducts_Requester */
        $tempModel = Mage::getModel('M2ePro/Play_Synchronization_Tasks_Defaults_UpdateListingsProducts_Requester');
        $tempModel->initialize($this->params,$this->marketplace,$this->account);
        $tempModel->setLocks($hash);
    }

    // ########################################

    protected function getPerformType()
    {
        return Ess_M2ePro_Model_Processing_Request::PERFORM_TYPE_PARTIAL;
    }

    // ########################################
}