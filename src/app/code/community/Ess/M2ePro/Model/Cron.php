<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Cron
{
    const TASK_SERVICING = 'servicing';
    const TASK_PROCESSING = 'processing';
    const TASK_LOGS_CLEANING = 'logs_cleaning';
    const TASK_SYNCHRONIZATION = 'synchronization';

    const MIN_DISTRIBUTION_EXECUTION_TIME = 300;
    const MAX_DISTRIBUTION_WAIT_INTERVAL = 59;

    private $lockFileHandler = false;

    //####################################

    public function process()
    {
        if (!$this->isPossibleToRunCron() ||
            !$this->checkDoubleRunProtection()) {
            return;
        }

        $this->updateCronLastRunDate();

        Mage::helper('M2ePro/Client')->setMemoryLimit(256);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        // Run local cron tasks
        //----------------------
        $this->processLogsCleaning();
        //----------------------

        // distribute server load
        //--------------------
        $this->distributeServerLoad();
        //--------------------

        // Run remote cron tasks
        //----------------------
        $this->processServicing();
        $this->processProcessing();
        $this->processSynchronization();
        //----------------------

        $this->actionAfterCronWasPerformed();
    }

    //####################################

    public function isShowError()
    {
        if (!$this->isPossibleToRunCron()) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/common/cron/error/', 'mode')) {
            return false;
        }

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        if (is_null($cronLastAccessTime)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access',
                                                                      Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/common/cron/error/', 'max_inactive_hours'
        );

        $temp = (strtotime($cronLastAccessTime) + ($allowedInactiveHours * 60*60));

        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) > $temp) {
            return true;
        }

        return false;
    }

    public function isShowNotification()
    {
        if (!$this->isPossibleToRunCron()) {
            return false;
        }

        if (!(bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/view/ebay/cron/notification/', 'mode')) {
            return false;
        }

        $cronLastAccessTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'last_access');

        if (is_null($cronLastAccessTime)) {
            Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/cron/', 'last_access',
                                                                      Mage::helper('M2ePro')->getCurrentGmtDate());
            return false;
        }

        $allowedInactiveHours = (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/cron/notification/', 'max_inactive_hours'
        );

        $temp = (strtotime($cronLastAccessTime) + ($allowedInactiveHours * 60*60));

        if (Mage::helper('M2ePro')->getCurrentGmtDate(true) > $temp) {
            return true;
        }

        return false;
    }

    //####################################

    private function processLogsCleaning()
    {
        $task = self::TASK_LOGS_CLEANING;

        if (!$this->isNowTimeToRun($task)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Log_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //------------------------------------

    private function processServicing()
    {
        $task = self::TASK_SERVICING;

        if (!$this->isNowTimeToRun($task)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Servicing_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    private function processProcessing()
    {
        $task = self::TASK_PROCESSING;

        if (!$this->isNowTimeToRun($task)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Processing_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    private function processSynchronization()
    {
        $task = self::TASK_SYNCHRONIZATION;

        if (!$this->isNowTimeToRun($task)) {
            return;
        }

        try {
            Mage::getModel('M2ePro/Synchronization_Cron')->process();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //####################################

    private function isNowTimeToRun($task)
    {
        if (!$this->isModeEnable($task)) {
            return false;
        }

        $interval = $this->getIntervalRuns($task);

        $lastAccess = $this->getLastAccessTime($task);
        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);

        if (is_null($lastAccess) || $currentTimeStamp > strtotime($lastAccess) + $interval) {
            $this->updateLastAccessTime($task);
            return true;
        }

        return false;
    }

    //------------------------------------

    private function isModeEnable($task)
    {
        $tempGroup = '/cron/task/'.strtolower(trim($task)).'/';
        return (bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'mode');
    }

    private function getIntervalRuns($task)
    {
        $tempGroup = '/cron/task/'.strtolower(trim($task)).'/';
        return (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'interval');
    }

    //------------------------------------

    private function getLastAccessTime($task)
    {
        $tempGroup = '/cron/task/'.strtolower(trim($task)).'/';
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($tempGroup,'last_access');
    }

    private function updateLastAccessTime($task)
    {
        $tempGroup = '/cron/task/'.strtolower(trim($task)).'/';
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($tempGroup,'last_access',
                                                                  Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    //####################################

    private function isPossibleToRunCron()
    {
        if (!Mage::helper('M2ePro/Module')->isPossibleToRunCron()) {
            return false;
        }

        if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'mode')) {
            return false;
        }

        return true;
    }

    private function checkDoubleRunProtection()
    {
        usleep(rand(0,1000000));

        if (!is_null(Mage::helper('M2ePro/Data_Global')->getValue('cron_running'))) {
            return false;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('cron_running',true);

        if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/cron/', 'double_run_protection')) {
            return true;
        }

        try {

            Mage::getModel('M2ePro/General_VariablesDir')->createBase();
            $lockFilePath = Mage::getModel('M2ePro/General_VariablesDir')->getBasePath().'cron.lock';
            $this->lockFileHandler = fopen($lockFilePath, is_file($lockFilePath) ? 'r+' : 'w+');

            if (!$this->lockFileHandler) {
                return false;
            }

            if (!flock($this->lockFileHandler, LOCK_EX | LOCK_NB)) {
                fclose($this->lockFileHandler);
                $this->lockFileHandler = false;
                return false;
            }

            ftruncate($this->lockFileHandler,0);
            rewind($this->lockFileHandler);
            fwrite($this->lockFileHandler,'PID: '.getmypid());

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
            return false;
        }

        return true;
    }

    //------------------------------------

    private function updateCronLastRunDate()
    {
        Mage::helper('M2ePro/Module')->getConfig()
                            ->setGroupValue('/cron/', 'last_access',
                                            Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    private function distributeServerLoad()
    {
        if (Mage::helper('M2ePro/Magento')->isDeveloper()) {
            return;
        }

        $maxExecutionTime = (int)@ini_get('max_execution_time');

        if ($maxExecutionTime <= 0 || $maxExecutionTime < self::MIN_DISTRIBUTION_EXECUTION_TIME) {
            return;
        }

        sleep(rand(0,self::MAX_DISTRIBUTION_WAIT_INTERVAL));
    }

    private function actionAfterCronWasPerformed()
    {
        if ($this->lockFileHandler) {
            ftruncate($this->lockFileHandler,0);
            flock($this->lockFileHandler, LOCK_UN);
            fclose($this->lockFileHandler);
        }

        $this->lockFileHandler = false;
    }

    //####################################
}