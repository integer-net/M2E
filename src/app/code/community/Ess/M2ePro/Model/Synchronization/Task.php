<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

abstract class Ess_M2ePro_Model_Synchronization_Task
{
    const DEFAULTS = 'defaults';
    const TEMPLATES = 'templates';
    const ORDERS = 'orders';
    const FEEDBACKS = 'feedbacks';
    const MARKETPLACES = 'marketplaces';
    const OTHER_LISTINGS = 'other_listings';
    const POLICIES = 'policies';

    //####################################

    private $allowedTasksTypes = array();

    private $lockItem = NULL;
    private $operationHistory = NULL;

    private $parentLockItem = NULL;
    private $parentOperationHistory = NULL;

    private $log = NULL;
    private $params = array();
    private $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN;

    //####################################

    public function process()
    {
        $this->initialize();

        if (!$this->isPossibleToRun()) {
            return false;
        }

        $this->beforeStart();

        $result = true;

        try {

            $tempResult = $this->performActions();

            if (!is_null($tempResult) && !$tempResult) {
                $result = false;
            }

            $this->getActualLockItem()->activate();

        } catch (Exception $exception) {

            $result = false;

            Mage::helper('M2ePro/Module_Exception')->process($exception);

            $this->getOperationHistory()->setContentData('exception', array(
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ));

            $this->getLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );
        }

        $this->afterEnd();

        return $result;
    }

    protected function processTask($taskPath)
    {
        $result = $this->makeTask($taskPath)->process();
        return is_null($result) || $result;
    }

    protected function makeTask($taskPath)
    {
        $taskPath = ($this->isComponentTask() ? ucfirst($this->getComponent()).'_' : '').'Synchronization_'.$taskPath;

        /** @var $task Ess_M2ePro_Model_Synchronization_Task **/
        $task = Mage::getModel('M2ePro/'.$taskPath);

        $task->setParentLockItem($this->getActualLockItem());
        $task->setParentOperationHistory($this->getActualOperationHistory());

        $task->setAllowedTasksTypes($this->getAllowedTasksTypes());

        $task->setLog($this->getLog());
        $task->setInitiator($this->getInitiator());
        $task->setParams($this->getParams());

        return $task;
    }

    // -----------------------------------

    abstract protected function getComponent();

    abstract protected function getType();

    abstract protected function getNick();

    // ----------------------------------

    abstract protected function getPercentsStart();

    abstract protected function getPercentsEnd();

    // ----------------------------------

    abstract protected function performActions();

    //####################################

    public function setAllowedTasksTypes(array $types)
    {
        $this->allowedTasksTypes = $types;
    }

    public function getAllowedTasksTypes()
    {
        return $this->allowedTasksTypes;
    }

    // -----------------------------------

    public function setParams(array $params)
    {
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    // -----------------------------------

    public function setInitiator($value)
    {
        $this->initiator = (int)$value;
    }

    public function getInitiator()
    {
        return $this->initiator;
    }

    // -----------------------------------

    public function setParentLockItem(Ess_M2ePro_Model_Synchronization_LockItem $object)
    {
        $this->parentLockItem = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_LockItem
     */
    public function getParentLockItem()
    {
        return $this->parentLockItem;
    }

    // -----------------------------------

    public function setParentOperationHistory(Ess_M2ePro_Model_Synchronization_OperationHistory $object)
    {
        $this->parentOperationHistory = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_OperationHistory
     */
    public function getParentOperationHistory()
    {
        return $this->parentOperationHistory;
    }

    // -----------------------------------

    public function setLog(Ess_M2ePro_Model_Synchronization_Log $object)
    {
        $this->log = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    public function getLog()
    {
        return $this->log;
    }

    //####################################

    protected function initialize()
    {
        $this->intervalIsEnabled() && $this->intervalPrepareLastTime();
    }

    protected function isPossibleToRun()
    {
        if ($this->isComponentLauncherTask() &&
            !Mage::helper('M2ePro/Component_'.ucfirst($this->getComponent()))->isActive()) {
            return false;
        }

        if ($this->isContainerTask() &&
            !in_array($this->getType(),$this->getAllowedTasksTypes())) {
            return false;
        }

        $tempSettingsPath = '/';
        foreach (array_values(array_filter(explode('/',$this->getFullSettingsPath()))) as $node) {

            $tempSettingsPath .= $node.'/';
            $tempMode = $this->getConfigValue($tempSettingsPath,'mode');

            if (!is_null($tempMode) && !$tempMode) {
                return false;
            }
        }

        if (!$this->getParentLockItem() && $this->getLockItem()->isExist()) {
            return false;
        }

        if ($this->intervalIsEnabled() && $this->intervalIsLocked()) {
            return false;
        }

        return true;
    }

    // -----------------------------------

    protected function beforeStart()
    {
        if (!$this->getParentLockItem()) {
            $this->getLockItem()->create();
            $this->getLockItem()->makeShutdownFunction();
        }

        if (!$this->getParentOperationHistory() || $this->isComponentLauncherTask() || $this->isContainerTask()) {

            $operationHistoryNickSuffix = str_replace('/','_',trim($this->getFullSettingsPath(),'/'));

            $operationHistoryParentId = $this->getParentOperationHistory() ?
                    $this->getParentOperationHistory()->getObject()->getId() : NULL;

            $this->getOperationHistory()->start('synchronization_'.$operationHistoryNickSuffix,
                                                $operationHistoryParentId,
                                                $this->getInitiator());

            $this->getOperationHistory()->makeShutdownFunction();
        }

        $this->configureLogBeforeStart();
        $this->configureProfilerBeforeStart();
        $this->configureLockItemBeforeStart();
    }

    protected function afterEnd()
    {
        $this->configureLockItemAfterEnd();
        $this->configureProfilerAfterEnd();
        $this->configureLogAfterEnd();

        if ($this->intervalIsEnabled()) {
            $this->intervalSetLastTime(Mage::helper('M2ePro')->getCurrentGmtDate(true));
        }

        if (!$this->getParentOperationHistory() || $this->isComponentLauncherTask() || $this->isContainerTask()) {
            $this->getOperationHistory()->stop();
        }

        if (!$this->getParentLockItem()) {
            $this->getLockItem()->remove();
        }
    }

    //####################################

    protected function getOperationHistory()
    {
        if (is_null($this->operationHistory)) {
            $this->operationHistory = Mage::getModel('M2ePro/Synchronization_OperationHistory');
        }
        return $this->operationHistory;
    }

    protected function getLockItem()
    {
        if (is_null($this->lockItem)) {
            $this->lockItem = Mage::getModel('M2ePro/Synchronization_LockItem');
            $operationHistoryNickSuffix = str_replace('/','_',trim($this->getFullSettingsPath(),'/'));
            $this->lockItem->setNick('synchronization_'.$operationHistoryNickSuffix);
        }
        return $this->lockItem;
    }

    // -----------------------------------

    /**
     * @return Ess_M2ePro_Model_Synchronization_OperationHistory
     * @throws Exception
     */
    protected function getActualOperationHistory()
    {
        if ($this->operationHistory) {
            return $this->operationHistory;
        }

        if (!$this->getParentOperationHistory()) {
            throw new Exception('Parent Operation History must be specified');
        }

        return $this->getParentOperationHistory();
    }

    /**
     * @return Ess_M2ePro_Model_Synchronization_LockItem
     * @throws Exception
     */
    protected function getActualLockItem()
    {
        if ($this->lockItem) {
            return $this->lockItem;
        }

        if (!$this->getParentLockItem()) {
            throw new Exception('Parent Lock item must be specified');
        }

        return $this->getParentLockItem();
    }

    //####################################

    private function isComponentTask()
    {
        return (bool)$this->getComponent();
    }

    // -----------------------------------

    private function isComponentLauncherTask()
    {
        return $this->isComponentTask() &&
               !(bool)$this->getType() &&
               !(bool)$this->getNick();
    }

    private function isContainerTask()
    {
        return (bool)$this->getType() &&
               !(bool)$this->getNick();
    }

    private function isStandardTask()
    {
        return !$this->isComponentLauncherTask() &&
               !$this->isContainerTask();
    }

    //####################################

    protected function getTitle()
    {
        if ($this->isComponentLauncherTask()) {
            $title = ucfirst($this->getComponent());
        } elseif ($this->isContainerTask()) {
            $title = ucfirst($this->getType());
        } else {
            $title = ucwords(str_replace('/',' ',trim($this->getNick(),'/')));
        }

        return $title;
    }

    protected function getLogTask()
    {
        switch ($this->getType()) {
            case self::DEFAULTS:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS;
            case self::TEMPLATES:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_TEMPLATES;
            case self::ORDERS:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS;
            case self::FEEDBACKS:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_FEEDBACKS;
            case self::MARKETPLACES:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_MARKETPLACES;
            case self::OTHER_LISTINGS:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER_LISTINGS;
            case self::POLICIES:
                return Ess_M2ePro_Model_Synchronization_Log::TASK_POLICIES;
        }

        return Ess_M2ePro_Model_Synchronization_Log::TASK_UNKNOWN;
    }

    // -----------------------------------

    protected function getFullSettingsPath()
    {
        $path = '/'.($this->getComponent() ? strtolower($this->getComponent()).'/' : '');
        $path .= $this->getType() ? strtolower($this->getType()).'/' : '';
        return $path.trim(strtolower($this->getNick()),'/').'/';
    }

    protected function getPercentsInterval()
    {
        return $this->getPercentsEnd() - $this->getPercentsStart();
    }

    //####################################

    protected function configureLogBeforeStart()
    {
        if ($this->isComponentLauncherTask()) {
            $this->getLog()->setComponentMode($this->getComponent());
        }

        if ($this->isContainerTask()) {
            $this->getLog()->setSynchronizationTask($this->getLogTask());
        }
    }

    protected function configureLogAfterEnd()
    {
        if ($this->isComponentLauncherTask()) {
            $this->getLog()->setComponentMode(NULL);
        }

        if ($this->isContainerTask()) {
            $this->getLog()->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_UNKNOWN);
        }
    }

    // -----------------------------------

    protected function configureProfilerBeforeStart()
    {
        if (!$this->isStandardTask()) {
            $this->getActualOperationHistory()->increaseLeftPadding();
            return;
        }

        $this->getActualOperationHistory()->appendEol();
        $this->getActualOperationHistory()->appendText($this->getTitle());
        $this->getActualOperationHistory()->appendLine();

        $this->getActualOperationHistory()->saveBufferString();

        $this->getActualOperationHistory()->increaseLeftPadding();
    }

    protected function configureProfilerAfterEnd()
    {
        $this->getActualOperationHistory()->decreaseLeftPadding();

        if ($this->isStandardTask()) {
            $this->getActualOperationHistory()->appendLine();
        }

        $this->getActualOperationHistory()->saveBufferString();
    }

    // -----------------------------------

    protected function configureLockItemBeforeStart()
    {
        $suffix = Mage::helper('M2ePro')->__('Synchronization');

        if ($this->isComponentLauncherTask() || $this->isContainerTask()) {

            $title = $suffix;

            if ($this->isContainerTask()) {
                $title = $this->getTitle().' '.$title;
            }

            if ($this->isComponentTask() && count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
                $componentHelper = 'Ess_M2ePro_Helper_Component_'.ucfirst($this->getComponent());
                $title = constant($componentHelper . '::TITLE').' '.$title;
            }

            $this->getActualLockItem()->setTitle(Mage::helper('M2ePro')->__($title));
        }

        $this->getActualLockItem()->setPercents($this->getPercentsStart());

        $status = 'Task "%s" is started. Please wait...';
        $title = ($this->isComponentLauncherTask() || $this->isContainerTask()) ?
                    $this->getTitle().' '.$suffix : $this->getTitle();

        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status,$title));
    }

    protected function configureLockItemAfterEnd()
    {
        $suffix = Mage::helper('M2ePro')->__('Synchronization');

        if ($this->isComponentLauncherTask() || $this->isContainerTask()) {

            $title = $suffix;

            if ($this->isContainerTask()) {
                $title = $this->getTitle().' '.$title;
            }

            if ($this->isComponentTask() && count(Mage::helper('M2ePro/Component')->getActiveComponents()) > 1) {
                $componentHelper = 'Ess_M2ePro_Helper_Component_'.ucfirst($this->getComponent());
                $title = constant($componentHelper.'::TITLE').' '.$title;
            }

            $this->getActualLockItem()->setTitle(Mage::helper('M2ePro')->__($title));
        }

        $this->getActualLockItem()->setPercents($this->getPercentsEnd());

        $status = 'Task "%s" is finished. Please wait...';
        $title = ($this->isComponentLauncherTask() || $this->isContainerTask()) ?
                    $this->getTitle().' '.$suffix : $this->getTitle();

        $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status,$title));
    }

    //####################################

    protected function intervalIsEnabled()
    {
        return false;
    }

    protected function intervalIsLocked()
    {
        $lastTime = strtotime($this->getConfigValue($this->getFullSettingsPath(),'last_time'));
        $interval = (int)$this->getConfigValue($this->getFullSettingsPath(),'interval');
        return $lastTime + $interval > Mage::helper('M2ePro')->getCurrentGmtDate(true);
    }

    //------------------------------------

    protected function intervalPrepareLastTime()
    {
        $lastTime = $this->getConfigValue($this->getFullSettingsPath(),'last_time');
        if (empty($lastTime)) {
            $lastTime = new DateTime('now', new DateTimeZone('UTC'));
            $lastTime->modify("-1 year");
            $this->intervalSetLastTime($lastTime);
        }
    }

    protected function intervalSetLastTime($time)
    {
        if ($time instanceof DateTime) {
            $time = (int)$time->format('U');
        }

        if (is_int($time)) {
            $oldTimezone = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $time = strftime('%Y-%m-%d %H:%M:%S', $time);
            date_default_timezone_set($oldTimezone);
        }

        $this->setConfigValue($this->getFullSettingsPath(),'last_time',$time);
    }

    //####################################

    private function getConfig()
    {
        return Mage::helper('M2ePro/Module')->getSynchronizationConfig();
    }

    // ----------------------------------------

    protected function getConfigValue($group, $key)
    {
        return $this->getConfig()->getGroupValue($group, $key);
    }

    protected function setConfigValue($group, $key, $value)
    {
        return $this->getConfig()->setGroupValue($group, $key, $value);
    }

    //####################################
}