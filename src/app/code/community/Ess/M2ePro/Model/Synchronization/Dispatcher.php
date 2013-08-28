<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    private $startTime = NULL;

    /**
     * @var array
     */
    private $_components = array();

    //####################################

    public function process()
    {
        Mage::helper('M2ePro/Server')->setMemoryLimit(512);
        Mage::helper('M2ePro/Exception')->setFatalErrorHandler();

        // Check global mode
        //----------------------------------
        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/synchronization/settings/','mode')) {
            return false;
        }
        //----------------------------------

        // Before dispatch actions
        //---------------------------
        if (!$this->beforeDispatch()) {
            return false;
        }
        //---------------------------

        if (in_array(Ess_M2ePro_Model_Synchronization_Tasks::ORDERS, $this->_tasks)) {
            Mage::dispatchEvent('m2epro_synchronization_before_start', array());
        }

        try {

            // DEFAULTS SYNCH
            //---------------------------
            $tempTask = $this->checkTask(Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS);
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/synchronization/settings/defaults/','mode'
            );
            if ($tempTask && $tempGlobalMode) {
                $tempSynch = new Ess_M2ePro_Model_Synchronization_Tasks_Defaults();
                $tempSynch->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // EBAY SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Ebay')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Ebay::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Ebay_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }

            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // AMAZON SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Amazon')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Amazon::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Amazon_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // BUY SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Buy')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Buy::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Buy_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        try {

            // PLAY SYNCH
            //---------------------------
            if (Mage::helper('M2ePro/Component_Play')->isActive() &&
                $this->checkComponent(Ess_M2ePro_Helper_Component_Play::NICK)) {

                $synchDispatcher = Mage::getModel('M2ePro/Play_Synchronization_Dispatcher');
                $synchDispatcher->setInitiator($this->_initiator);
                $synchDispatcher->setTasks($this->_tasks);
                $synchDispatcher->setParams($this->_params);
                $synchDispatcher->process();
            }
            //---------------------------

        } catch (Exception $exception) {
            $this->catchException($exception);
        }

        if (in_array(Ess_M2ePro_Model_Synchronization_Tasks::ORDERS, $this->_tasks)) {
            Mage::dispatchEvent('m2epro_synchronization_after_end', array());
        }

        // After dispatch actions
        //---------------------------
        if (!$this->afterDispatch()) {
            return false;
        }
        //---------------------------

        return true;
    }

    //####################################

    public function setComponents(array $components = array())
    {
        $this->_components = array();

        foreach ($components as $component) {
            if ($component !== Ess_M2ePro_Helper_Component_Ebay::NICK &&
                $component !== Ess_M2ePro_Helper_Component_Amazon::NICK &&
                $component !== Ess_M2ePro_Helper_Component_Buy::NICK &&
                $component !== Ess_M2ePro_Helper_Component_Play::NICK) {
                    continue;
            }
            $this->_components[] = $component;
        }
    }

    private function checkComponent($component)
    {
        return in_array($component, $this->_components);
    }

    //------------------------------------

    private function beforeDispatch()
    {
        $helper = Mage::helper('M2ePro');

        // Save start time stamp
        $this->startTime = Mage::helper('M2ePro')->getCurrentGmtDate();

        // Create and save tasks
        //----------------------------------
        $helper->setGlobalValue('synchTasks',$this->_tasks);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        $helper->setGlobalValue('synchInitiator',$this->_initiator);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        $helper->setGlobalValue('synchParams',$this->_params);
        //----------------------------------

        // Create and save profiler
        //----------------------------------
        $profilerParams = array();

        if ($this->_initiator == Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER) {
            $profilerParams['muteOutput'] = true;
        } else {
            $profilerParams['muteOutput'] = false;
        }

        $profiler = Mage::getModel('M2ePro/Synchronization_Profiler',$profilerParams);
        $helper->setGlobalValue('synchProfiler',$profiler);

        $helper->getGlobalValue('synchProfiler')->enable();
        $helper->getGlobalValue('synchProfiler')->start();
        $helper->getGlobalValue('synchProfiler')->makeShutdownFunction();

        $helper->getGlobalValue('synchProfiler')->setClearResources();
        //----------------------------------

        // Create and save synch session
        //----------------------------------
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        $helper->setGlobalValue('synchRun',$runs);

        $helper->getGlobalValue('synchRun')->start($this->_initiator);
        $helper->getGlobalValue('synchRun')->makeShutdownFunction();
        $helper->getGlobalValue('synchRun')->cleanOldData();

        $helper->setGlobalValue(
            'synchId',Mage::helper('M2ePro')->getGlobalValue('synchRun')->getLastId()
        );
        //----------------------------------

        // Create and save logs
        //----------------------------------
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        $helper->setGlobalValue('synchLogs',$logs);

        $helper->getGlobalValue('synchLogs')->setSynchronizationRun($helper->getGlobalValue('synchId'));
        $helper->getGlobalValue('synchLogs')->setSynchronizationTask(
            Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN
        );

        switch ($this->_initiator) {
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_CRON:
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER:
                $helper->getGlobalValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_EXTENSION);
                break;
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER:
                $helper->getGlobalValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_USER);
                break;
            default:
                $helper->getGlobalValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_UNKNOWN);
                break;
        }
        //----------------------------------

        // Create and save lock item
        //----------------------------------
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        $helper->setGlobalValue('synchLockItem',$lockItem);

        if ($helper->getGlobalValue('synchLockItem')->isExist()) {

            $helper->getGlobalValue('synchLogs')->addMessage(
                $helper->__('Another synchronization is already running'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            $helper->getGlobalValue('synchProfiler')->addTitle(
                'Another synchronization is already running.',Ess_M2ePro_Model_General_Profiler::TYPE_ERROR
            );
            return false;
        }

        $helper->getGlobalValue('synchLockItem')->setSynchRunObj($runs);
        $helper->getGlobalValue('synchLockItem')->create();
        $helper->getGlobalValue('synchLockItem')->makeShutdownFunction();
        //----------------------------------

        // Make shutdown function for clearing product changes
        $this->makeShutdownFunctionForProductChanges();

        return true;
    }

    private function afterDispatch()
    {
        Mage::helper('M2ePro')->getGlobalValue('synchRun')->stop();
        Mage::helper('M2ePro')->getGlobalValue('synchProfiler')->stop();
        Mage::helper('M2ePro')->getGlobalValue('synchLockItem')->remove();

        Mage::getModel('M2ePro/ProductChange')->clearAll(
            Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,$this->startTime
        );
        Mage::getModel('M2ePro/ProductChange')->clearAll(
            Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION
        );

        return true;
    }

    //------------------------------------

    private function makeShutdownFunctionForProductChanges()
    {
        $functionCode = "Mage::getModel('M2ePro/ProductChange')";
        $functionCode .= "->clearAll(Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_OBSERVER,'".$this->startTime."');";
        $functionCode .= "Mage::getModel('M2ePro/ProductChange')";
        $functionCode .= "->clearAll(Ess_M2ePro_Model_ProductChange::CREATOR_TYPE_SYNCHRONIZATION);";

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}