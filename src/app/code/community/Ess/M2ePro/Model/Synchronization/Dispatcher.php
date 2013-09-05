<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Dispatcher extends Ess_M2ePro_Model_Synchronization_Dispatcher_Abstract
{
    /**
     * @var array
     */
    private $_components = array();

    //####################################

    public function process()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(512);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        // Check global mode
        //----------------------------------
        if (!(bool)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGlobalValue('mode')) {
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
            $tempGlobalMode = (bool)(int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/defaults/','mode'
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

        //----------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('synchStartTime',$helper->getCurrentGmtDate());
        //----------------------------------

        // Create and save tasks
        //----------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('synchTasks',$this->_tasks);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('synchInitiator',$this->_initiator);
        //----------------------------------

        // Create and save initiator
        //----------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('synchParams',$this->_params);
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
        Mage::helper('M2ePro/Data_Global')->setValue('synchProfiler',$profiler);

        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->enable();
        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->start();
        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->makeShutdownFunction();

        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->setClearResources();
        //----------------------------------

        // Create and save synch session
        //----------------------------------
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        Mage::helper('M2ePro/Data_Global')->setValue('synchRun',$runs);

        Mage::helper('M2ePro/Data_Global')->getValue('synchRun')->start($this->_initiator);
        Mage::helper('M2ePro/Data_Global')->getValue('synchRun')->makeShutdownFunction();
        Mage::helper('M2ePro/Data_Global')->getValue('synchRun')->cleanOldData();

        Mage::helper('M2ePro/Data_Global')->setValue(
            'synchId',Mage::helper('M2ePro/Data_Global')->getValue('synchRun')->getLastId()
        );
        //----------------------------------

        // Create and save logs
        //----------------------------------
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        Mage::helper('M2ePro/Data_Global')->setValue('synchLogs',$logs);

        Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')
                                          ->setSynchronizationRun(Mage::helper('M2ePro/Data_Global')
                                          ->getValue('synchId'));

        Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')->setSynchronizationTask(
            Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN
        );

        switch ($this->_initiator) {
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_CRON:
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER:
                Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_EXTENSION);
                break;
            case Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER:
                Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_USER);
                break;
            default:
                Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')
                    ->setInitiator(Ess_M2ePro_Model_Synchronization_Log::INITIATOR_UNKNOWN);
                break;
        }
        //----------------------------------

        // Create and save lock item
        //----------------------------------
        $lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
        Mage::helper('M2ePro/Data_Global')->setValue('synchLockItem',$lockItem);

        if (Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem')->isExist()) {

            Mage::helper('M2ePro/Data_Global')->getValue('synchLogs')->addMessage(
                $helper->__('Another synchronization is already running'),
                Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM
            );
            Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->addTitle(
                'Another synchronization is already running.',Ess_M2ePro_Model_General_Profiler::TYPE_ERROR
            );
            return false;
        }

        Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem')->setSynchRunObj($runs);
        Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem')->create();
        Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem')->makeShutdownFunction();
        //----------------------------------

        Mage::getModel('M2ePro/ProductChange')->clearOutdated(
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/settings/product_change/', 'max_lifetime'
            )
        );
        Mage::getModel('M2ePro/ProductChange')->clearExcessive(
            (int)Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/settings/product_change/', 'max_count'
            )
        );

        // Make shutdown function for clearing product changes
        $this->makeShutdownFunctionForProductChanges();

        return true;
    }

    private function afterDispatch()
    {
        Mage::helper('M2ePro/Data_Global')->getValue('synchRun')->stop();
        Mage::helper('M2ePro/Data_Global')->getValue('synchProfiler')->stop();
        Mage::helper('M2ePro/Data_Global')->getValue('synchLockItem')->remove();

        Mage::getModel('M2ePro/ProductChange')->clearLastProcessed(
            Mage::helper('M2ePro/Data_Global')->getValue('synchStartTime'),
            Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
                '/settings/product_change/', 'max_count_per_one_time'
            )
        );

        return true;
    }

    //------------------------------------

    private function makeShutdownFunctionForProductChanges()
    {
        $synchStartTime = Mage::helper('M2ePro/Data_Global')->getValue('synchStartTime');
        $maxPerOneTime = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getGroupValue(
            '/settings/product_change/', 'max_count_per_one_time'
        );

        $functionCode = <<<PHP
Mage::getModel('M2ePro/ProductChange')->clearLastProcessed('{$synchStartTime}',{$maxPerOneTime});
PHP;

        $shutdownDeleteFunction = create_function('', $functionCode);
        register_shutdown_function($shutdownDeleteFunction);

        return true;
    }

    //####################################
}