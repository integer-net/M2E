<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update_Responser
    extends Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL,$this->hash);
        $this->getAccount()->deleteObjectLocks('synchronization',$this->hash);
        $this->getAccount()->deleteObjectLocks('synchronization_ebay',$this->hash);
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX,
            $this->hash
        );

        $this->getMarketplace()->deleteObjectLocks(NULL,$this->hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization',$this->hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization_ebay',$this->hash);
        $this->getMarketplace()->deleteObjectLocks(
            Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX,
            $this->hash
        );

        $fail && $this->getSynchLogModel()->addMessage(Mage::helper('M2ePro')->__($message),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    // ########################################

    protected function processResponseData($response)
    {
        $response = parent::processResponseData($response);

        try {

            /** @var $updatingModel Ess_M2ePro_Model_Ebay_Listing_Other_Updating */
            $updatingModel = Mage::getModel('M2ePro/Ebay_Listing_Other_Updating');
            $updatingModel->initialize($this->getAccount());
            $updatingModel->processResponseData($response);

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getSynchLogModel()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
                                                  Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                  Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getObjectByParam('Marketplace','marketplace_id');
    }

    //-----------------------------------------

    protected function getSynchLogModel()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        /** @var $runs Ess_M2ePro_Model_Synchronization_Run */
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        $runs->start(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $runsId = $runs->getLastId();
        $runs->stop();

        /** @var $logs Ess_M2ePro_Model_Synchronization_Log */
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        $logs->setSynchronizationRun($runsId);
        $logs->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $logs->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_OTHER_LISTINGS);

        $this->synchronizationLog = $logs;

        return $this->synchronizationLog;
    }

    // ########################################
}