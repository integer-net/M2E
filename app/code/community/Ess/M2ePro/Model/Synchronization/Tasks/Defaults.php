<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $processingMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/synchronization/settings/defaults/processing/','mode'
        );
        $deletedProductsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/synchronization/settings/defaults/deleted_products/','mode'
        );
        $inspectorMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/synchronization/settings/defaults/inspector/','mode'
        );
        if (!$deletedProductsMode && !$inspectorMode && !$processingMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($processingMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Processing();
            $tempSynch->process();
        }

        if ($deletedProductsMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults_DeletedProducts();
            $tempSynch->process();
        }

        if ($inspectorMode) {
            $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Inspector();
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

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Default Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('Default Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Default Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Default Synchronization" is finished. Please wait...')
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