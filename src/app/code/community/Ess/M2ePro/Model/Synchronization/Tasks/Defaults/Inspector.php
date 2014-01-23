<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Tasks_Defaults_Inspector extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 65;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 35;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $inspectorMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/','mode'
        );

        if (!$inspectorMode) {
            return false;
        }
        //-----------------------------

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

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Inspector Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Inspector Actions" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Inspector Actions" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->processProductChanges();
        $this->processAutoActions();
    }

    // -----------------------------------

    private function processProductChanges()
    {
        $inspectorNick = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/defaults/inspector/product_changes/','mode'
        );

        if (!in_array($inspectorNick, array('circle'))) {
            return;
        }

        $modelName = 'M2ePro/Synchronization_Tasks_Defaults_Inspector_ProductChanges_'.ucfirst($inspectorNick);
        Mage::getModel($modelName)->process();
    }

    private function processAutoActions()
    {
        Mage::getModel('M2ePro/Synchronization_Tasks_Defaults_Inspector_AutoActions')->process();
    }

    //####################################
}