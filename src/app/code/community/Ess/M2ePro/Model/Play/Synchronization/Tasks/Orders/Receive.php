<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Orders_Receive extends Ess_M2ePro_Model_Play_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_play_orders_receive';

    //####################################

    // ->__('Play.com Orders Receive Synchronization')
    private $name = 'Play.com Orders Receive Synchronization';

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
        $this->_profiler->addTimePoint(__METHOD__,'Get and process orders from Play');

        // Prepare last time
        $this->prepareSynchLastTime();

        // Check locked last time
        if ($this->isSynchLocked() &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $this->_initiator != Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return;
        }

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Play')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Play_Account::ORDERS_MODE_YES);

        $percentsForAccount = self::PERCENTS_INTERVAL;
        $accountsTotalCount = (int)$accountsCollection->getSize();

        if ($accountsTotalCount > 0) {
            $percentsForAccount = $percentsForAccount/$accountsTotalCount;
        }

        $marketplace = Mage::helper('M2ePro/Component_Play')->getCachedObject(
            'Marketplace', Ess_M2ePro_Helper_Component_Play::MARKETPLACE_VIRTUAL_ID
        );

        $accountIteration = 1;
        foreach ($accountsCollection->getItems() as $account) {
            if (!$this->isLockedAccountMarketplace($account->getId(), $marketplace->getId())) {
                $this->processAccountMarketplace($account, $marketplace);
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
        $title = 'Starting account "%s" and marketplace "%s%';
        $title = sprintf($title, $account->getTitle(), $marketplace->getTitle());

        $this->_profiler->addTitle($title);
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Get orders from Play');

        $status = 'Task "%s" for Play.com "%s" Account and "%s" marketplace is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle(), $marketplace->getTitle());
        $this->_lockItem->setStatus($status);

        //------------------------------
        $fromDate = $this->prepareFromDate($account->getData('orders_last_synchronization'));
        $params = array(
            'from_date' => $fromDate
        );

        if (is_null($account->getData('orders_last_synchronization'))) {
            $account->setData('orders_last_synchronization', $fromDate)->save();
        }
        //------------------------------

        // Get open orders from Play.com for account
        //---------------------------
        /** @var $dispatcherObject Ess_M2ePro_Model_Connector_Server_Play_Dispatcher */
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Play_Dispatcher');
        $prefix = 'Ess_M2ePro_Model_Play_Synchronization';
        $dispatcherObject->processConnector(
            'tasks', 'orders_receive', 'requester', $params, $marketplace, $account, $prefix
        );
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    //####################################

    private function prepareSynchLastTime()
    {
        $lastTime = $this->config->getGroupValue('/play/orders/receive/', 'last_time');

        if (!empty($lastTime)) {
            return;
        }

        $lastTime = new DateTime('now', new DateTimeZone('UTC'));
        $lastTime->modify('-1 year');

        $this->setSynchLastTime($lastTime);
    }

    private function isSynchLocked()
    {
        $lastTime = strtotime($this->config->getGroupValue('/play/orders/receive/', 'last_time'));
        $interval = (int)$this->config->getGroupValue('/play/orders/receive/','interval');

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

        $this->config->setGroupValue('/play/orders/receive/', 'last_time', $time);
    }

    //####################################

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $maxDeactivateTime = (int)$this->config->getGroupValue('/play/orders/receive/', 'max_deactivate_time');
        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        return $lockItem->isExist();
    }

    //####################################

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        //------------------------
        if (is_null($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }

        // we have to receive orders, which were placed at least 10 days before the date of the latest synch,
        // to get all updates, because it's not possible to receive only updated orders from Play.com
        $lastFromDate->modify('-10 days');
        //------------------------

        // Get min date for synch
        //------------------------
        $minDate = new DateTime('now', new DateTimeZone('UTC'));
        $minDate->modify('-20 days');
        //------------------------

        // Prepare last date
        //------------------------
        if ((int)$lastFromDate->format('U') < (int)$minDate->format('U')) {
            $lastFromDate = $minDate;
        }
        //------------------------

        return $lastFromDate->format('Y-m-d H:i:s');
    }

    //####################################
}