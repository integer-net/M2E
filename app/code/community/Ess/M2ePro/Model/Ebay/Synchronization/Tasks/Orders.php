<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    protected $configGroup = '/ebay/synchronization/settings/orders/';

    //####################################

    public function process()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getConfig();

        // Check tasks config mode
        //-----------------------------
        $generalMode = (bool)$config->getGroupValue($this->configGroup, 'mode');
        $receiveMode = (bool)$config->getGroupValue($this->configGroup.'receive/', 'mode');
        $cancellationMode = (bool)$config->getGroupValue($this->configGroup.'cancellation/', 'mode');
        $reserveCancellationMode = (bool)$config->getGroupValue($this->configGroup.'reserve_cancellation/', 'mode');

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
        $interval = $config->getGroupValue($this->configGroup.'cancellation/', 'interval');
        $lastAccess = $config->getGroupValue($this->configGroup.'cancellation/', 'last_access');
        $startDate = $config->getGroupValue($this->configGroup.'cancellation/', 'start_date');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $isNowTimeToRun = is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval;
        $isNowTimeToRun && $isNowTimeToRun = (!is_null($startDate) && $currentTimeStamp > strtotime($startDate));

        if ($cancellationMode && $isNowTimeToRun) {
            $config->setGroupValue(
                $this->configGroup.'cancellation/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Cancellation();
            $tempSynch->process();
        }

        if (is_null($startDate)) {
            $startDate = new DateTime('now', new DateTimeZone('UTC'));
            $startDate->modify('+7 days');
            $config->setGroupValue($this->configGroup.'cancellation/', 'start_date', $startDate->format('Y-m-d H:i:s'));
        }
        //---------------------------

        // RUN RESERVE CANCELLATION SYNCH
        //---------------------------
        $interval = $config->getGroupValue($this->configGroup.'reserve_cancellation/', 'interval');
        $lastAccess = $config->getGroupValue($this->configGroup.'reserve_cancellation/', 'last_access');

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        $isNowTimeToRun = is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval;

        if ($reserveCancellationMode && $isNowTimeToRun) {
            $config->setGroupValue(
                $this->configGroup.'reserve_cancellation/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate()
            );

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
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Orders Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Orders Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Orders Synchronization" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################
}