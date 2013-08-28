<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    // ->__('Task "Update 3rd Party Listings" has completed with errors. View %sl%listings log%el% for details.');
    // ->__('Task "Update 3rd Party Listings" has completed with warnings. View %sl%listings log%el% for details.');

    //####################################

    const PERCENTS_START = 50;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 50;

    //####################################

    public function process()
    {
        // Check tasks config mode
        //-----------------------------
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $reviseMode = (bool)(int)$config
                ->getGroupValue('/ebay/synchronization/settings/other_listings/templates/revise/','mode');
        $relistMode = (bool)(int)$config
                ->getGroupValue('/ebay/synchronization/settings/other_listings/templates/relist/','mode');
        $stopMode = (bool)(int)$config
                ->getGroupValue('/ebay/synchronization/settings/other_listings/templates/stop/','mode');

        if (!$reviseMode && !$relistMode && !$stopMode) {
            return false;
        }
        //-----------------------------

        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        $this->createRunnerActions();
        //---------------------------

        // RUN CHILD SYNCH
        //---------------------------
        if ($reviseMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Revise();
            $tempSynch->process();
        }

        if ($relistMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Relist();
            $tempSynch->process();
        }

        if ($stopMode) {
            $tempSynch = new Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Stop();
            $tempSynch->process();
        }
        //---------------------------

        Ess_M2ePro_Model_Ebay_Synchronization_Tasks_OtherListings_Templates_Abstract::clearCache();

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

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Ebay::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Templates');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Update 3rd Party Listings" action is started. Please wait...')
        );
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(
            Mage::helper('M2ePro')->__('The "Update 3rd Party Listings" action is finished. Please wait...')
        );

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function createRunnerActions()
    {
        $runnerActionsModel = Mage::getModel('M2ePro/Ebay_Listing_Other_RunnerActions');
        $runnerActionsModel->removeAllProducts();
        Mage::helper('M2ePro')->setGlobalValue('synchRunnerActions',$runnerActionsModel);
        $this->_runnerActions = $runnerActionsModel;
    }

    private function executeRunnerActions()
    {
        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__,'Apply products changes on eBay');

        $result = $this->_runnerActions->execute($this->_lockItem,
                                                 self::PERCENTS_START + 30,
                                                 self::PERCENTS_END);

        $startLink = '<a href="route:*/adminhtml_log/listingOther/tab/ebay;back:*/adminhtml_log/synchronization">';
        $endLink = '</a>';

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_ERROR) {

            $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Task "Update 3rd Party Listings" has completed with errors. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
            $this->_profiler->addTitle('Updating products on eBay ended with errors.');
        }

        if ($result == Ess_M2ePro_Model_Connector_Server_Ebay_Item_Abstract::STATUS_WARNING) {

            $tempString = Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Task "Update 3rd Party Listings" has completed with warnings. View %sl%listings log%el% for details.',
                array('!sl'=>$startLink,'!el'=>$endLink)
            );
            $this->_logs->addMessage($tempString,
                                     Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                                     Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM);
            $this->_profiler->addTitle('Updating products on eBay ended with warnings.');
        }

        $this->_runnerActions->removeAllProducts();
        Mage::helper('M2ePro')->unsetGlobalValue('synchRunnerActions');
        $this->_runnerActions = NULL;

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}