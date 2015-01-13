<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Buy_Synchronization_Defaults_UpdateListingsProducts_Responser';
    }

    // ########################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->create();

        $tempObjects = array(
            $this->account,
            Mage::helper('M2ePro/Component_Buy')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_buy',
            Ess_M2ePro_Model_Buy_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->addObjectLock($lock, $hash);
            }
        }
    }

    // ########################################
}