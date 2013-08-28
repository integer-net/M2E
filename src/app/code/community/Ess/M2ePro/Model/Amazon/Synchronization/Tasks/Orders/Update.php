<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
*/

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Update extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    // we have a limit on the server to retrieve only last 30 orders
    // so if we will update 30 or more orders at a time, we will not be able to receive all updated orders next time
    const MAX_UPDATES_PER_TIME = 25;

    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders_update';

    //####################################

    /** @var Ess_M2ePro_Model_Config_Module */
    private $config = NULL;
    private $configGroup = '/amazon/synchronization/settings/orders/update/';

    //####################################

    public function process()
    {
        $this->config = Mage::helper('M2ePro/Module')->getConfig();

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Orders Update Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Orders Update Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Update Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Update Synchronization" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    public function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Update orders on Amazon');

        // Prepare last time
        $this->prepareSynchLastTime();

        // Check locked last time
        if ($this->isSynchLocked() &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return;
        }

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Amazon_Account::ORDERS_MODE_YES);

        $percentsForAccount = self::PERCENTS_INTERVAL;
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $accountIteration = 1;
        foreach ($accountsCollection->getItems() as $account) {

            /** @var Ess_M2ePro_Model_Account $account */

            /** @var Ess_M2ePro_Model_Marketplace[] $marketplaces */
            $marketplaces = $account->getChildObject()->getMarketplacesItems();

            foreach ($marketplaces as $marketplace) {

                $marketplace = $marketplace['object'];

                if (!$this->isLockedAccountMarketplace($account->getId(), $marketplace->getId())) {
                    $this->processAccountMarketplace($account, $marketplace);
                }
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->setSynchLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccountMarketplace(
        Ess_M2ePro_Model_Account $account,
        Ess_M2ePro_Model_Marketplace $marketplace
    ) {
        $title = 'Starting account "'.$account->getTitle().'" and marketplace "'.$marketplace->getTitle().'"';
        $this->_profiler->addTitle($title);
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Update orders on Amazon');

        $statusString = 'Task "Orders Update Synchronization" for Rakuten.com "%s" Account and "%s" marketplace ';
        $statusString .= 'is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($statusString, $account->getTitle(), $marketplace->getTitle());
        $this->_lockItem->setStatus($status);

        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Amazon::NICK);
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->setPageSize(self::MAX_UPDATES_PER_TIME);

        if ($changesCollection->getSize() == 0) {
            return;
        }

        // Update orders shipping status on Rakuten.com
        //---------------------------
        $items = array();

        foreach ($changesCollection as $change) {
            $changeParams = $change->getParams();

            $items[] = array(
                'order_id'         => $change->getOrderId(),
                'change_id'        => $change->getId(),
                'amazon_order_id'  => $changeParams['amazon_order_id'],
                'tracking_number'  => $changeParams['tracking_number'],
                'carrier_name'     => $changeParams['carrier_name'],
                'shipping_method'  => isset($changeParams['shipping_method']) ? $changeParams['shipping_method'] : null,
                'fulfillment_date' => $changeParams['fulfillment_date'],
                'items'            => $changeParams['items']
            );
        }

        if (count($items) == 0) {
            return;
        }

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Amazon_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector')->getDispatcher();
        $dispatcherObject->processConnector(
            'orders', 'update', 'items', array('items' => $items), $marketplace, $account
        );

        $changesCollection->walk('deleteInstance');
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function prepareSynchLastTime()
    {
        $lastTime = $this->getSynchLastTime();
        if (empty($lastTime)) {
            $lastTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastTime->modify('-1 year');
            $this->setSynchLastTime($lastTime);
        }
    }

    private function isSynchLocked()
    {
        $lastTime = strtotime($this->getSynchLastTime());
        $interval = (int)$this->config->getGroupValue($this->configGroup,'interval');

        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
    }

    private function getSynchLastTime()
    {
        return $this->config->getGroupValue($this->configGroup,'last_time');
    }

    private function setSynchLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }
        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }

        $this->config->setGroupValue($this->configGroup, 'last_time', $time);
    }

    //####################################

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $maxDeactivateTime = (int)$this->config->getGroupValue($this->configGroup, 'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        if ($lockItem->isExist()) {
            return true;
        }

        return false;
    }

    //####################################
}
