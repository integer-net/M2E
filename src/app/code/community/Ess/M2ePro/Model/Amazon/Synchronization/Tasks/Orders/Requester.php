<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Requester
    extends Ess_M2ePro_Model_Connector_Amazon_Orders_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Amazon_Synchronization_Tasks_Orders_Responser';
    }

    // ########################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders::LOCK_ITEM_PREFIX.'_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->create();

        // ----------------------

        $tempObjects = array(
            $this->account,
            $this->account->getChildObject()->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_amazon',
            Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->addObjectLock($lock,$hash);
            }

        }
    }

    // ########################################
}