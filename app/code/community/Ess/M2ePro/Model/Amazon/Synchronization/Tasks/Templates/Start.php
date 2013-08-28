<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Start
    extends Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Templates_Abstract
{
    const PERCENTS_START = 5;
    const PERCENTS_END = 8;
    const PERCENTS_INTERVAL = 3;

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
            $componentName = Ess_M2ePro_Helper_Component_Amazon::TITLE.' ';
        } else {
            $componentName = '';
        }

        $this->_profiler->addEol();
        $this->_profiler->addTitle($componentName.'Initial Actions');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__,'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Initial" action is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('The "Initial" action is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        $this->_profiler->addTimePoint(__METHOD__,'Check start synchronizations');

        foreach ($this->_synchronizations as &$synchronization) {

            foreach ($synchronization['listings'] as &$listing) {

                /** @var $listing Ess_M2ePro_Model_Listing */

                if ($listing->isSynchronizationAlreadyStart()) {
                    continue;
                }

                if ($listing->getSynchronizationTimestampStart() > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
                    continue;
                }

                $listing->setSynchronizationAlreadyStart(true);
                $listing->setSynchronizationOnlyStart(true);
            }
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################
}