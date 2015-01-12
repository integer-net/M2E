<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Servicing_Dispatcher
{
    const DEFAULT_INTERVAL = 3600;
    const MAX_MEMORY_LIMIT = 256;

    private $params = array();
    private $forceTasksRunning = false;

    // ########################################

    public function getForceTasksRunning()
    {
        return $this->forceTasksRunning;
    }

    public function setForceTasksRunning($value)
    {
        $this->forceTasksRunning = (bool)$value;
    }

    // ----------------------------------------

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params = array())
    {
        $this->params = $params;
    }

    // ########################################

    public function process($minInterval = NULL)
    {
        $timeLastUpdate = $this->getLastUpdateTimestamp();

        if (!is_null($minInterval) &&
            $timeLastUpdate + (int)$minInterval > Mage::helper('M2ePro')->getCurrentGmtDate(true)) {
            return false;
        }

        $this->setLastUpdateDateTime();
        return $this->processTasks($this->getRegisteredTasks());
    }

    // ----------------------------------------

    public function processTask($allowedTask)
    {
        return $this->processTasks(array($allowedTask));
    }

    public function processTasks(array $allowedTasks = array())
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(self::MAX_MEMORY_LIMIT);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        $responseData = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher')
                                    ->processVirtual('servicing','update','data',
                                                     $this->getRequestData($allowedTasks));

        if (!is_array($responseData)) {
            return false;
        }

        $this->dispatchResponseData($responseData,$allowedTasks);

        return true;
    }

    // ########################################

    private function getRequestData(array $allowedTasks = array())
    {
        $requestData = array();

        foreach ($this->getRegisteredTasks() as $taskName) {

            if (!in_array($taskName,$allowedTasks)) {
                continue;
            }

            /** @var $taskModel Ess_M2ePro_Model_Servicing_Task */
            $taskModel = Mage::getModel('M2ePro/Servicing_Task_'.ucfirst($taskName));
            $taskModel->setParams($this->getParams());

            if (!$this->getForceTasksRunning() && !$taskModel->isAllowed()) {
                continue;
            }

            $requestData[$taskModel->getPublicNick()] = $taskModel->getRequestData();
        }

        return $requestData;
    }

    private function dispatchResponseData(array $responseData, array $allowedTasks = array())
    {
        foreach ($this->getRegisteredTasks() as $taskName) {

            if (!in_array($taskName,$allowedTasks)) {
                continue;
            }

            /** @var $taskModel Ess_M2ePro_Model_Servicing_Task */
            $taskModel = Mage::getModel('M2ePro/Servicing_Task_'.ucfirst($taskName));
            $taskModel->setParams($this->getParams());

            if (!isset($responseData[$taskModel->getPublicNick()]) ||
                !is_array($responseData[$taskModel->getPublicNick()])) {
                continue;
            }

            $taskModel->processResponseData($responseData[$taskModel->getPublicNick()]);
        }
    }

    // ########################################

    private function getRegisteredTasks()
    {
        return array(
            'license',
            'messages',
            'settings',
            'backups',
            'exceptions',
            'analytic',
            'marketplaces',
            'cron'
        );
    }

    // ----------------------------------------

    private function getLastUpdateTimestamp()
    {
        $lastUpdateDate = Mage::helper('M2ePro/Module')->getCacheConfig()
                            ->getGroupValue('/servicing/','last_update_time');

        if (is_null($lastUpdateDate)) {
            return Mage::helper('M2ePro')->getCurrentGmtDate(true) - 3600*24*30;
        }

        return Mage::helper('M2ePro')->getDate($lastUpdateDate,true);
    }

    private function setLastUpdateDateTime()
    {
        Mage::helper('M2ePro/Module')->getCacheConfig()
            ->setGroupValue('/servicing/', 'last_update_time',
                            Mage::helper('M2ePro')->getCurrentGmtDate());
    }

    // ########################################
}