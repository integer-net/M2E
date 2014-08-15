<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update_Requester
    extends Ess_M2ePro_Model_Connector_Ebay_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Ebay_Synchronization_OtherListings_Update_Responser';
    }

    // ########################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->create();

        $this->account->addObjectLock(NULL,$hash);
        $this->account->addObjectLock('synchronization',$hash);
        $this->account->addObjectLock('synchronization_ebay',$hash);
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX,
            $hash
        );

        $this->marketplace->addObjectLock(NULL,$hash);
        $this->marketplace->addObjectLock('synchronization',$hash);
        $this->marketplace->addObjectLock('synchronization_ebay',$hash);
        $this->marketplace->addObjectLock(
            Ess_M2ePro_Model_Ebay_Synchronization_OtherListings_Update::LOCK_ITEM_PREFIX,
            $hash
        );
    }

    // ########################################
}