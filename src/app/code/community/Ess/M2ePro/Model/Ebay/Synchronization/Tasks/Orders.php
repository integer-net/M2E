<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    // ->__('eBay Orders Synchronization')
    private $name = 'eBay Orders Synchronization';

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
        // Check tasks config mode
        //-----------------------------
        $generalMode             = $this->config->getGroupValue('/ebay/orders/', 'mode');
        $receiveMode             = $this->config->getGroupValue('/ebay/orders/receive/', 'mode');
        $updateMode              = $this->config->getGroupValue('/ebay/orders/update/', 'mode');
        $cancellationMode        = $this->config->getGroupValue('/ebay/orders/cancellation/', 'mode');
        $reserveCancellationMode = $this->config->getGroupValue('/ebay/orders/reserve_cancellation/', 'mode');

        if (!$generalMode || (!$receiveMode && !$cancellationMode && !$reserveCancellationMode)) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CANCELLATION SYNCH
        //---------------------------
        $interval   = $this->config->getGroupValue('/ebay/orders/cancellation/', 'interval');
        $lastAccess = $this->config->getGroupValue('/ebay/orders/cancellation/', 'last_access');
        $startDate  = $this->config->getGroupValue('/ebay/orders/cancellation/', 'start_date');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $currentGmtDate   = Mage::helper('M2ePro')->getCurrentGmtDate();

        $isNowTimeToRun = is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval;
        $isNowTimeToRun && $isNowTimeToRun = (!is_null($startDate) && $currentTimeStamp > strtotime($startDate));

        if ($cancellationMode && $isNowTimeToRun) {
            $this->config->setGroupValue('/ebay/orders/cancellation/', 'last_access', $currentGmtDate);

            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Cancellation();
            $tempSynch->process();
        }

        if (is_null($startDate)) {
            $startDate = new DateTime('now', new DateTimeZone('UTC'));
            $startDate->modify('+7 days');

            $this->config->setGroupValue('/ebay/orders/cancellation/', 'start_date', $startDate->format('Y-m-d H:i:s'));
        }
        //---------------------------

        // RUN RESERVE CANCELLATION SYNCH
        //---------------------------
        $interval   = $this->config->getGroupValue('/ebay/orders/reserve_cancellation/', 'interval');
        $lastAccess = $this->config->getGroupValue('/ebay/orders/reserve_cancellation/', 'last_access');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $currentGmtDate   = Mage::helper('M2ePro')->getCurrentGmtDate();

        $isNowTimeToRun = is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval;

        if ($reserveCancellationMode && $isNowTimeToRun) {
            $this->config->setGroupValue('/ebay/orders/reserve_cancellation/', 'last_access', $currentGmtDate);

            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Reserve_Cancellation();
            $tempSynch->process();
        }
        //---------------------------

        // RUN RECEIVE SYNCH
        //---------------------------
        if ($receiveMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Receive();
            $tempSynch->process();
        }
        //---------------------------

        // RUN UPDATE SYNCH
        //------------------------------
        if ($updateMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Update();
            $tempSynch->process();
        }
        //------------------------------

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
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
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
}