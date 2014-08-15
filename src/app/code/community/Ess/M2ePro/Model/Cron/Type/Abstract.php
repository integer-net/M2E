<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Cron_Type_Abstract
{
    const MAX_MEMORY_LIMIT = 1024;

    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    //####################################

    public function process()
    {
        $this->initialize();
        $this->updateLastAccess();

        if (!$this->isPossibleToRun()) {
            return false;
        }

        $this->updateLastRun();
        $this->beforeStart();

        $result = true;

        try {

            // local tasks
            $result = !$this->processTask(Ess_M2ePro_Model_Cron_Task_LogsCleaning::NICK) ? false : $result;

            // request tasks
            $result = !$this->processTask(Ess_M2ePro_Model_Cron_Task_Servicing::NICK) ? false : $result;
            $result = !$this->processTask(Ess_M2ePro_Model_Cron_Task_Processing::NICK) ? false : $result;
            $result = !$this->processTask(Ess_M2ePro_Model_Cron_Task_Synchronization::NICK) ? false : $result;

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));
        }

        $this->afterEnd();

        return $result;
    }

    protected function processTask($task)
    {
        $task = str_replace('_',' ',$task);
        $task = str_replace(' ','',ucwords($task));

        /** @var $task Ess_M2ePro_Model_Cron_Task_Abstract **/
        $task = Mage::getModel('M2ePro/Cron_Task_'.trim($task));

        $task->setInitiator($this->getInitiator());
        $task->setParentLockItem($this->getLockItem());
        $task->setParentOperationHistory($this->getOperationHistory());

        $result = $task->process();

        return is_null($result) || $result;
    }

    // -----------------------------------

    abstract protected function getType();

    //####################################

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    //####################################

    protected function initialize()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();
    }

    protected function updateLastAccess()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        Mage::helper('M2ePro/Module_Cron')->setLastAccess($currentDateTime);
    }

    protected function isPossibleToRun()
    {
        $helper = Mage::helper('M2ePro/Module_Cron');

        return $this->getType() == $helper->getType() &&
               $helper->isModeEnabled() &&
               $helper->isReadyToRun() &&
               !$this->getLockItem()->isExist();
    }

    protected function updateLastRun()
    {
        $currentDateTime = Mage::helper('M2ePro')->getCurrentGmtDate();
        Mage::helper('M2ePro/Module_Cron')->setLastRun($currentDateTime);
    }

    // -----------------------------------

    protected function beforeStart()
    {
        $this->getLockItem()->create();
        $this->getLockItem()->makeShutdownFunction();

        $this->getOperationHistory()->cleanOldData();

        $this->getOperationHistory()->start('cron',NULL,$this->getInitiator());
        $this->getOperationHistory()->makeShutdownFunction();
    }

    protected function afterEnd()
    {
        $this->getOperationHistory()->stop();
        $this->getLockItem()->remove();
    }

    //####################################

    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/LockItem');
            $this->lockItem->setNick('cron');
        }
        return $this->lockItem;
    }

    protected function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/OperationHistory');
        }
        return $this->operationHistory;
    }

    //####################################
}