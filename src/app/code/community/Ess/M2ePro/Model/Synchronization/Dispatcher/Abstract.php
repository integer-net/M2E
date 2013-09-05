<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    //####################################

    /**
     * @var array
     */
    protected $_tasks = array();

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var int
     */
    protected $_initiator = Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN;

    //####################################

    abstract public function process();

    // -----------------------------------

    public function setTasks(array $tasks = array())
    {
        $this->_tasks = $tasks;
    }

    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    public function setInitiator($initiator = Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN)
    {
        if ($initiator !== Ess_M2ePro_Model_Synchronization_Run::INITIATOR_CRON &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER &&
            $initiator !== Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN) {
                $initiator = Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN;
        }

        $this->_initiator = $initiator;
    }

    //####################################

    protected function checkTask($task)
    {
        return in_array($task, $this->_tasks);
    }

    protected function catchException($exception)
    {
        Mage::helper('M2ePro/Module_Exception')->process($exception);

        Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')->addMessage(
            Mage::helper('M2ePro')->__($exception->getMessage()),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );

        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->addTitle(
            Mage::helper('M2ePro')->__($exception->getMessage()),
            Ess_M2ePro_Model_General_Profiler::TYPE_ERROR
        );
    }

    //####################################
}