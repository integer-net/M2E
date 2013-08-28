<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update_ServerRequester extends
                                                        Ess_M2ePro_Model_Connector_Server_Ebay_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Ebay_Synchronization_Tasks_OtherListings_Update_ServerResponser';
    }

    protected function setLocks($hash)
    {
        /** @var $tempModel Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update_Requester */
        $tempModel = Mage::getModel('M2ePro/Ebay_Synchronization_Tasks_OtherListings_Update_Requester');
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