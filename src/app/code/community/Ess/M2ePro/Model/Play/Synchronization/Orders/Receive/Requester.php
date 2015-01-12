<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Orders_Receive_Requester
    extends Ess_M2ePro_Model_Connector_Play_Orders_Get_Items
{
    // ##########################################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Play_Synchronization_Orders_Receive_Responser';
    }

    // ##########################################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItemPrefix = Ess_M2ePro_Model_Play_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX;

        $nick = $lockItemPrefix . '_' . $this->account->getId();
        $lockItem->setNick($nick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->create();

        // --------------------

        $tempObjects = array(
            $this->account,
            Mage::helper('M2ePro/Component_Play')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_play',
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