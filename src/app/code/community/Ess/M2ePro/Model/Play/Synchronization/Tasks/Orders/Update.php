<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Orders_Update extends Ess_M2ePro_Model_Play_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_play_orders_update';

    //####################################

    // ->__('Play.com Orders Update Synchronization')
    private $name = 'Play.com Orders Update Synchronization';

    /** @var Ess_M2ePro_Model_Config_Synchronization */
    private $config = NULL;

    //####################################

    public function __construct()
    {
        $this->config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        parent::__construct();
    }

    //####################################

    public function process()
    {
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

        $this->_profiler->addEol();
        $this->_profiler->addTitle($this->name);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($this->name));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is started. Please wait...', $this->name));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is finished. Please wait...', $this->name));

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
        $this->_profiler->addTimePoint(__METHOD__,'Update orders on Play');

        // Prepare last time
        $this->prepareSynchLastTime();

        // Check locked last time
        if ($this->isSynchLocked() &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return;
        }

        // delete changes, which were processed 3 or more times
        //------------------------------
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Play::NICK
            );
        //------------------------------

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Play_Account::ORDERS_MODE_YES);
        $accountsTotalCount = (int)$accountsCollection->getSize();
        $accountIteration = 1;

        $percentsForAccount = self::PERCENTS_INTERVAL;
        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        foreach ($accountsCollection->getItems() as $account) {
            if (!$this->isLockedAccount($account->getId())) {
                $this->processAccount($account);
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration++);
            $this->_lockItem->activate();
        }

        $this->setSynchLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Account $account)
    {
        $title = 'Starting account "%s"';
        $title = sprintf($title, $account->getTitle());

        $this->_profiler->addTitle($title);
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Update orders on Play');

        $status = 'Task "%s" for Play.com "%s" Account is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS);

        $changesCollection = Mage::getModel('M2ePro/Order_Change')->getCollection();
        $changesCollection->addAccountFilter($account->getId());
        $changesCollection->addProcessingAttemptDateFilter();
        $changesCollection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);
        $changesCollection->getSelect()->group(array('order_id'));

        if ($changesCollection->getSize() == 0) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount($changesCollection->getAllIds());

        // Update orders shipping status on Rakuten.com
        //---------------------------
        $params = array();

        foreach ($changesCollection as $change) {
            $changeParams = $change->getParams();

            $params[] = array(
                'change_id'       => $change->getId(),
                'order_id'        => $change->getOrderId(),
                'play_order_id'   => $changeParams['play_order_id'],
                'carrier_name'    => $changeParams['carrier_name'],
                'tracking_number' => $changeParams['tracking_number']
            );
        }

        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Play_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Play_Dispatcher');
        $dispatcherObject->processConnector(
            'orders', 'update', 'shipping', $params, $account
        );
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function prepareSynchLastTime()
    {
        $lastTime = $this->config->getGroupValue('/play/orders/update/','last_time');

        if (!empty($lastTime)) {
            return;
        }

        $lastTime = new DateTime('now', new DateTimeZone('UTC'));
        $lastTime->modify('-1 year');

        $this->setSynchLastTime($lastTime);
    }

    private function isSynchLocked()
    {
        $lastTime = strtotime($this->config->getGroupValue('/play/orders/update/','last_time'));
        $interval = (int)$this->config->getGroupValue('/play/orders/update/','interval');

        if ($lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return true;
        }

        return false;
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

        $this->config->setGroupValue('/play/orders/update/', 'last_time', $time);
    }

    //####################################

    private function isLockedAccount($accountId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId);

        $maxDeactivateTime = (int)$this->config->getGroupValue('/play/orders/update/', 'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        return $lockItem->isExist();
    }

    //####################################
}