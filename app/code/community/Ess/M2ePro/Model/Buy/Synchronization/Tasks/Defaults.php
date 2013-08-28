<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults extends Ess_M2ePro_Model_Buy_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $buySynchGroup = '/buy/synchronization/settings/defaults/update_listings_products/';
        $ulpMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                           ->getGroupValue($buySynchGroup,'mode');
        if (!$ulpMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($ulpMode) {
            $tempSynch = new Ess_M2ePro_Model_Buy_Synchronization_Tasks_Defaults_UpdateListingsProducts();
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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_DEFAULTS);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Buy::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Default Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Default Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')
                                                    ->__('Task "Default Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')
                                                ->__('Task "Default Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################
}