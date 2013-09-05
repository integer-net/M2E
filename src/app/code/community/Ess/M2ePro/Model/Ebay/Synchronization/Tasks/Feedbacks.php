<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($this->canRunTask('receive')) {
            $currentGmtDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            $config->setGroupValue(
                '/ebay/feedbacks/receive/', 'last_access', $currentGmtDate
            );

            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks_Receive();
            $tempSynch->process();
        }

        if ($this->canRunTask('response')) {
            $currentGmtDate = Mage::helper('M2ePro')->getCurrentGmtDate();
            $config->setGroupValue(
                '/ebay/feedbacks/response/', 'last_access', $currentGmtDate
            );

            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Feedbacks_Response();
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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_FEEDBACKS);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Feedback Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Feedback Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Feedback Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Feedback Synchronization" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    private function canRunTask($task)
    {
        /** @var $config Ess_M2ePro_Model_Config_Module */
        $config = Mage::helper('M2ePro/Module')->getSynchronizationConfig();
        $mode = (bool)$config->getGroupValue('/ebay/feedbacks/'.$task.'/', 'mode');

        if (!$mode) {
            return false;
        }

        if ($this->_initiator == Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER ||
            $this->_initiator == Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER) {
            return true;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $interval = $config->getGroupValue('/ebay/feedbacks/'.$task.'/', 'interval');
        $lastAccess = $config->getGroupValue('/ebay/feedbacks/'.$task.'/', 'last_access');

        if (is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval) {
           return true;
        }

        return false;
    }

    //####################################
}