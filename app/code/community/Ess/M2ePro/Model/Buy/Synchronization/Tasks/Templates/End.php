<?php

/*
 * @copyright  Copyright (c) 2012 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Tasks_Templates_End
    extends Ess_M2ePro_Model_Buy_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 8;
    const PERCENTS_END = 10;
    const PERCENTS_INTERVAL = 2;

    private $_synchronizations = array();

    //####################################

    public function __construct()
    {
        parent::__construct();
        $this->_synchronizations = Mage::helper('M2ePro')->getGlobalValue('synchTemplatesArray');
    }

    //####################################

    public function process()
    {
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

        if (count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
            $componentName = Ess_M2ePro_Helper_Component_Buy::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Final Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Final" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Final" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check end synchronizations');

        foreach ($this->_synchronizations as &$synchronization) {

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if ($listing->isSynchronizationAlreadyStop()) {
                    continue;
                }

                if ($listing->getSynchronizationTimestampStop() > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                    continue;
                }

                $listing->setSynchronizationAlreadyStop(true);
                $listing->setSynchronizationOnlyStop(true);
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}