<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update_Responser
    extends Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->params['account_id'];

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->remove();

        $this->getAccount()->deleteObjectLocks(NULL,$this->hash);
        $this->getAccount()->deleteObjectLocks('synchronization',$this->hash);
        $this->getAccount()->deleteObjectLocks('synchronization_ebay',$this->hash);
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX,
            $this->hash
        );

        $this->getMarketplace()->deleteObjectLocks(NULL,$this->hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization',$this->hash);
        $this->getMarketplace()->deleteObjectLocks('synchronization_ebay',$this->hash);
        $this->getMarketplace()->deleteObjectLocks(
            Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX,
            $this->hash
        );

        $fail && $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($message),
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

            $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($exception->getMessage()),
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

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS);

        return $this->synchronizationLog;
    }

    // ########################################
}