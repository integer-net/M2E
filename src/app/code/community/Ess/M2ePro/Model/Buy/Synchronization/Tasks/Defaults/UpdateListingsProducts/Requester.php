<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults_UpdateListingsProducts_Requester
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

        $tempNick = Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId().'_'.$this->marketplace->getId();

        $lockItem->setNick($tempNick);
        $lockItem->create();

        $this->account->addObjectLock(NULL,$hash);
        $this->account->addObjectLock('synchronization',$hash);
        $this->account->addObjectLock('synchronization_buy',$hash);
        $this->account->addObjectLock(
            Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,$hash
        );

        $this->marketplace->addObjectLock(NULL,$hash);
        $this->marketplace->addObjectLock('synchronization',$hash);
        $this->marketplace->addObjectLock('synchronization_buy',$hash);
        $this->marketplace->addObjectLock(
            Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX,$hash
        );
    }

    // ########################################
}