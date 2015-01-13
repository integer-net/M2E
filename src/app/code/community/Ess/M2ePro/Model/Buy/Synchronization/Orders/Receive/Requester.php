<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Orders_Receive_Requester
    extends Ess_M2ePro_Model_Connector_Buy_Orders_Get_Items
{
    // ##########################################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Buy_Synchronization_Orders_Receive_Responser';
    }

    // ##########################################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItemPrefix = Ess_M2ePro_Model_Buy_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX;

        $nick = $lockItemPrefix . '_' . $this->account->getId();
        $lockItem->setNick($nick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->create();

        // --------------------

        $tempObjects = array(
            $this->account,
            Mage::helper('M2ePro/Component_Buy')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_buy',
            $lockItemPrefix
        );

        /* @var Ess_M2ePro_Model_Abstract $object */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->addObjectLock($lock,$hash);
            }
        }
    }

    // ##########################################################
}