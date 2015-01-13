<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Defaults_UpdateListingsProducts_Requester
    extends Ess_M2ePro_Model_Connector_Play_Inventory_Get_Items
{
    // ########################################

    protected function makeResponserModel()
    {
        return 'M2ePro/Play_Synchronization_Defaults_UpdateListingsProducts_Responser';
    }

    // ########################################

    protected function setLocks($hash)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $tempNick = Ess_M2ePro_Model_Play_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX;
        $tempNick .= '_'.$this->account->getId();

        $lockItem->setNick($tempNick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);

        $lockItem->create();

        $tempObjects = array(
            $this->account,
            Mage::helper('M2ePro/Component_Play')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_play',
            Ess_M2ePro_Model_Play_Synchronization_Defaults_UpdateListingsProducts::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->addObjectLock($lock, $hash);
            }
        }

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tempTable = Mage::getSingleton('core/resource')->getTableName('m2epro_play_processed_inventory');
        $connWrite->delete($tempTable,array('`hash` = ?'=>(string)$hash));
    }

    // ########################################
}