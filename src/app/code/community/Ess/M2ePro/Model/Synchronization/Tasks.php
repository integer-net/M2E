<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Tasks
{
    const DEFAULTS = 1;
    const TEMPLATES = 2;
    const ORDERS = 3;
    const FEEDBACKS = 4;
    const MARKETPLACES = 5;
    const OTHER_LISTINGS = 6;
    const POLICIES = 7;

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

    /**
     * @var null|int
     */
    protected $_synchId = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Profiler
     */
    protected $_profiler = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Run
     */
    protected $_runs = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Log
     */
    protected $_logs = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_LockItem
     */
    protected $_lockItem = NULL;

    //####################################

    public function __construct()
    {
        $this->_tasks = Mage::helper('M2ePro/Data_Global')->getValue('synchTasks');
        $this->_initiator = Mage::helper('M2ePro/Data_Global')->getValue('synchInitiator');
        $this->_params = Mage::helper('M2ePro/Data_Global')->getValue('synchParams');

        $this->_synchId = Mage::helper('M2ePro/Data_Global')->getValue('synchId');

        $this->_profiler = Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler');
        $this->_runs = Mage::helper('M2ePro/Data_Global')->getValue('synchRun');
        $this->_logs = Mage::helper('M2ePro/Data_Global')->getValue('synchLogs');
        $this->_lockItem = Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem');
    }

    //####################################

    abstract public function process();

    //####################################
}