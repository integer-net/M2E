<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $detailsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/ebay/synchronization/settings/marketplaces/details/','mode');
        $categoriesMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/ebay/synchronization/settings/marketplaces/categories/','mode');
        $motorsSpecificsMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()
            ->getGroupValue('/ebay/synchronization/settings/marketplaces/motors_specifics/','mode');
        if (!$detailsMode && !$categoriesMode && !$motorsSpecificsMode) {
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
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_Details();
            $tempSynch->process();
        }

        if ($categoriesMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_Categories();
            $tempSynch->process();
        }

        if ($motorsSpecificsMode
            && !empty($this->_params['marketplace_id'])
            && $this->_params['marketplace_id'] == Ess_M2ePro_Helper_Component_Ebay::MARKETPLACE_MOTORS
        ) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Marketplaces_MotorsSpecifics();
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
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
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
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Marketplaces Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Marketplaces Synchronization" is finished. Please wait...')
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