<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Development_Module_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Development_CommandController
{
    //#############################################

    /**
     * @title "Cron Tasks"
     * @description "Run all cron synchronization tasks as developer mode"
     * @confirm "Are you sure?"
     * @components
     * @new_line
     */
    public function synchCronTasksAction()
    {
        $this->processSynchTasks(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
            Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS,
            Ess_M2ePro_Model_Synchronization_Tasks::POLICIES
        ));
    }

    //----------------------------------------------

    /**
     * @title "Defaults"
     * @description "Run only defaults synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchDefaultsAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS
        ));
    }

    //#############################################

    /**
     * @title "Templates"
     * @description "Run only stock level synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchTemplatesAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES
        ));
    }

    /**
     * @title "Orders"
     * @description "Run only orders synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchOrdersAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::ORDERS
        ));
    }

    /**
     * @title "Feedbacks"
     * @description "Run only feedbacks synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components ebay
     */
    public function synchFeedbacksAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS
        ));
    }

    /**
     * @title "Marketplaces"
     * @description "Run only marketplaces synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchMarketplacesAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::MARKETPLACES
        ));
    }

    /**
     * @title "3rd Party Listings"
     * @description "Run only 3rd party listings synchronization as developer mode"
     * @confirm "Are you sure?"
     * @components
     */
    public function synchOtherListingsAction()
    {
        $this->processSynchTasks(array(
             Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
        ));
    }

    //#############################################

    private function processSynchTasks($tasks)
    {
        $configProfiler = Mage::helper('M2ePro/Module')->getSynchronizationConfig()->getAllGroupValues('/settings/profiler/');

        if (count($configProfiler) > 0) {
            $shutdownFunctionCode = '';
            foreach ($configProfiler as $key => $value) {
                $shutdownFunctionCode .= "Mage::helper('M2ePro/Module')->getSynchronizationConfig()";
                $shutdownFunctionCode .= "->setGroupValue('/settings/profiler/', '{$key}', '{$value}');";
            }
            $shutdownFunctionInstance = create_function('', $shutdownFunctionCode);
            register_shutdown_function($shutdownFunctionInstance);
        }

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue('/settings/profiler/','mode','3');
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue('/settings/profiler/','delete_resources','0');
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue('/settings/profiler/','print_type','2');

        session_write_close();

        $components = Mage::helper('M2ePro/Component')->getComponents();
        if ($this->getRequest()->getParam('component')) {
            $components = array($this->getRequest()->getParam('component'));
        }

        /** @var $synchDispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_DEVELOPER);
        $synchDispatcher->setComponents($components);
        $synchDispatcher->setTasks($tasks);
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();

        if (count($configProfiler) > 0) {
            foreach ($configProfiler as $key => $value) {
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue('/settings/profiler/', $key, $value);
            }
        }
    }

    //#############################################
}