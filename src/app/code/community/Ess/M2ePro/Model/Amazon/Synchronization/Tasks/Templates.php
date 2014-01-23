<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $amazonSynch = '/amazon/templates/list/';
        $listMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                            ->getGroupValue($amazonSynch,'mode');
        $amazonSynch = '/amazon/templates/revise/';
        $reviseMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                              ->getGroupValue($amazonSynch,'mode');
        $amazonSynch = '/amazon/templates/relist/';
        $relistMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                              ->getGroupValue($amazonSynch,'mode');
        $amazonSynch = '/amazon/templates/stop/';
        $stopMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                                            ->getGroupValue($amazonSynch,'mode');

        if (!$listMode && !$reviseMode && !$relistMode && !$stopMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        $this->createRunnerActions();
        //---------------------------

        // GET TEMPLATES
        //---------------------------
        $this->_profiler->addEol();
        $this->_lockItem->setPercents(self::PERCENTS_START + 5);
        $this->_lockItem->activate();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($listMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_List();
            $tempSynch->process();
        }

        if ($reviseMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Revise();
            $tempSynch->process();
        }

        if ($relistMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Relist();
            $tempSynch->process();
        }

        if ($stopMode) {
            $tempSynch = new Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Stop();
            $tempSynch->process();
        }
        //---------------------------

        // UNSET TEMPLATES
        //---------------------------
        Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Abstract::clearCache();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        $this->executeRunnerActions();
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_TEMPLATES);

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Inventory Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($componentName.'Inventory Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Inventory Synchronization" is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('Task "Inventory Synchronization" is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    private function createRunnerActions()
    {
        $runnerActionsModel = Mage::getModel('M2ePro/Amazon_Template_Synchronization_RunnerActions');
        $runnerActionsModel->removeAllProducts();
        Mage::helper('M2ePro/Data_Global')->setValue('synchRunnerActions',$runnerActionsModel);
        $this->_runnerActions = $runnerActionsModel;
    }

    private function executeRunnerActions()
    {
        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__,'Apply products changes on Amazon');

        $result = $this->_runnerActions->execute($this->_lockItem,
                                                 self::PERCENTS_START + 60,
                                                 self::PERCENTS_END);

        $startLink = '<a target="_blank" href="route:*/adminhtml_common_log/listing/;';
        $startLink .= 'back:*/adminhtml_common_log/synchronization/;';
        $startLink .= 'filter:component_mode='.Ess_M2ePro_Helper_Component_Amazon::NICK.'">';
        $endLink = '</a>';

        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {

            $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
         // ->__('Task "Inventory Synchronization" has completed with errors. View %sl%listings log%el% for details.');
                'Task "Inventory Synchronization" has completed with errors. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
            $this->_profiler->addTitle('Updating products on Amazon ended with errors.');
        }

        if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {

            $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
       // ->__('Task "Inventory Synchronization" has completed with warnings. View %sl%listings log%el% for details.');
                'Task "Inventory Synchronization" has completed with warnings. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            $this->_profiler->addTitle('Updating products on Amazon ended with warnings.');
        }

        $this->_runnerActions->removeAllProducts();
        Mage::helper('M2ePro/Data_Global')->unsetValue('synchRunnerActions');
        $this->_runnerActions = NULL;

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}