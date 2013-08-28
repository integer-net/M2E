<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update_Requester
{
    protected $params = array();

    /**
     * @var Ess_M2ePro_Model_Marketplace|null
     */
    protected $marketplace = NULL;

    /**
     * @var Ess_M2ePro_Model_Account|null
     */
    protected $account = NULL;

    // ########################################

    public function initialize(array $params = array(),
                               Ess_M2ePro_Model_Marketplace $marketplace = NULL,
                               Ess_M2ePro_Model_Account $account = NULL)
    {
        $this->params = $params;
        $this->marketplace = $marketplace;
        $this->account = $account;
    }

    // ########################################

    public function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->create();

        $this->account->addObjectLock(NULL,$hash);
        $this->account->addObjectLock('synchronization',$hash);
        $this->account->addObjectLock('synchronization_ebay',$hash);
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX,
            $hash
        );

        $this->marketplace->addObjectLock(NULL,$hash);
        $this->marketplace->addObjectLock('synchronization',$hash);
        $this->marketplace->addObjectLock('synchronization_ebay',$hash);
        $this->marketplace->addObjectLock(
            Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Update::LOCK_ITEM_PREFIX,
            $hash
        );
    }

    // ########################################
}