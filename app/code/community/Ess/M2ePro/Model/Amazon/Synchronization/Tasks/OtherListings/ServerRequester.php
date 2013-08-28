<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_OtherListings_ServerRequester extends
                                                        Ess_M2ePro_Model_Connector_Server_Amazon_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Amazon_Synchronization_Tasks_OtherListings_ServerResponser';
    }

    protected function setLocks($hash)
    {
        /** @var $tempModel Ess_M2ePro_Model_Amazon_Synchronization_Tasks_OtherListings_Requester */
        $tempModel = Mage::getModel('M2ePro/Amazon_Synchronization_Tasks_OtherListings_Requester');
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