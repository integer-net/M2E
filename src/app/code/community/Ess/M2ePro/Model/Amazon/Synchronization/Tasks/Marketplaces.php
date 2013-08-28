<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Marketplaces extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $amazonSynchGroup = '/amazon/synchronization/settings/marketplaces/details/';
        $detailsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                               ->getGroupValue($amazonSynchGroup,'mode');

        $amazonSynchGroup = '/amazon/synchronization/settings/marketplaces/categories/';
        $categoriesMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                  ->getGroupValue($amazonSynchGroup,'mode');

        $amazonSynchGroup = '/amazon/synchronization/settings/marketplaces/specifics/';
        $specificsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                                                 ->getGroupValue($amazonSynchGroup,'mode');
        if (!$detailsMode && !$categoriesMode && !$specificsMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($detailsMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Marketplaces_Details();
            $tempSynch->process();
        }

        if ($categoriesMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Marketplaces_Categories();
            $tempSynch->process();
        }

        if ($specificsMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Marketplaces_Specifics();
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
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_MARKETPLACES);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Marketplaces Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Marketplaces Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')
                                                ->__('Task "Marketplaces Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')
                                               ->__('Task "Marketplaces Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################
}