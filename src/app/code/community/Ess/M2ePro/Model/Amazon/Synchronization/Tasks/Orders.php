<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    const LOCK_ITEM_PREFIX = 'synchronization_amazon_orders';

    // ->__('Amazon Orders Synchronization')
    private $name = 'Amazon Orders Synchronization';

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

        // RUN RESERVE CANCELLATION SYNCH
        //---------------------------
        $this->executeReserveCancellationSynch();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->executeReceiveSynch();
        //---------------------------

        // RUN UPDATE SYNCH
        //---------------------------
        $this->executeUpdateSynch();
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

    public function executeReceiveSynch()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Get and process items from Amazon');

        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Amazon_Account::ORDERS_MODE_YES);

        $accountsTotalCount = $accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = self::PERCENTS_INTERVAL/(int)$accountsCollection->getSize();
        }

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            $marketplace = $accountObj->getChildObject()->getMarketplace();

            if (!$this->isLockedAccountMarketplace($accountObj->getId(),$marketplace->getId())) {
                $this->updateAccountMarketplace($accountObj,$marketplace);
            }

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function isLockedAccountMarketplace($accountId, $marketplaceId)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItem->setNick(self::LOCK_ITEM_PREFIX.'_'.$accountId.'_'.$marketplaceId);

        $maxDeactivateTime = (int)$this->config->getGroupValue('/amazon/orders/', 'max_deactivate_time');

        $lockItem->setMaxDeactivateTime($maxDeactivateTime);

        return $lockItem->isExist();
    }

    //####################################

    private function updateAccountMarketplace(
        Ess_M2ePro_Model_Account $account,
        Ess_M2ePro_Model_Marketplace $marketplace
    ) {
        $this->_profiler->addTitle(
            'Starting account "'.$account->getTitle().'" and marketplace "'.$marketplace->getTitle().'"'
        );
        $this->_profiler->addTimePoint(__METHOD__.'send'.$account->getId(),'Get orders from Amazon');

        //->__('Task "%s" for Amazon account: "%s" and marketplace "%s" is started. Please wait...')
        $status = 'Task "%s" for Amazon account: "%s" and marketplace "%s" is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle(), $marketplace->getTitle());
        $this->_lockItem->setStatus($status);

        // Get orders from Amazon for account
        //---------------------------
        $fromDate = $this->prepareFromDate($account->getData('orders_last_synchronization'));
        $params = array(
            'from_date' => $fromDate
        );

        if (is_null($account->getData('orders_last_synchronization'))) {
            $account->setData('orders_last_synchronization', $fromDate)->save();
        }

        $entity = 'tasks';
        $type   = 'orders';
        $name   = 'requester';
        $prefix = 'Ess_M2ePro_Model_Amazon_Synchronization';

        $dispatcherObject = Mage::getModel('M2ePro/Connector_Server_Amazon_Dispatcher');
        $dispatcherObject->processConnector(
            $entity, $type, $name, $params, $marketplace, $account, $prefix
        );
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'send'.$account->getId());
        $this->_profiler->addEol();
    }

    private function prepareFromDate($lastFromDate)
    {
        // Get last from date
        //------------------------
        if (is_null($lastFromDate)) {
            $lastFromDate = new DateTime('now', new DateTimeZone('UTC'));
            $lastFromDate->modify('-6 hours');
        } else {
            $lastFromDate = new DateTime($lastFromDate, new DateTimeZone('UTC'));
        }
        //------------------------

        // Get min date for synch
        //------------------------
        $minDate = new DateTime('now',new DateTimeZone('UTC'));
        $minDate->modify('-7 days');
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

    private function executeReserveCancellationSynch()
    {
        $mode = (bool)$this->config->getGroupValue('/amazon/orders/reserve_cancellation/', 'mode');

        if (!$mode) {
            return;
        }

        $interval = $this->config->getGroupValue('/amazon/orders/reserve_cancellation/', 'interval');
        $lastAccess = $this->config->getGroupValue('/amazon/orders/reserve_cancellation/', 'last_access');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $currentGmtDate   = Mage::helper('M2ePro')->getCurrentGmtDate();

        if (!is_null($lastAccess) && $currentTimeStamp < strtotime($lastAccess) + $interval) {
            return;
        }

        $this->config->setGroupValue('/amazon/orders/reserve_cancellation/', 'last_access', $currentGmtDate);

        $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Reserve_Cancellation();
        $tempSynch->process();
    }

    //####################################

    private function executeUpdateSynch()
    {
        $mode = (bool)$this->config->getGroupValue('/amazon/orders/update/', 'mode');

        if (!$mode) {
            return;
        }

        $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Update();
        $tempSynch->process();
    }

    //####################################
}