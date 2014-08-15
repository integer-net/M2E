<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Amazon_Synchronization_Templates
    extends Ess_M2ePro_Model_Amazon_Synchronization_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Runner
     */
    private $runner = NULL;

    /**
     * @var Ess_M2ePro_Model_Amazon_Synchronization_Templates_Inspector
     */
    private $inspector = NULL;

    /**
     * @var Ess_M2ePro_Model_Synchronization_Templates_Changes
     */
    protected $changesHelper = NULL;

    //####################################

    protected function getType()
    {
        return Ess_M2ePro_Model_Synchronization_Task_Abstract::TEMPLATES;
    }

    protected function getNick()
    {
        return NULL;
    }

    protected function getTitle()
    {
        return 'Inventory';
    }

    // -----------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    //####################################

    protected function beforeStart()
    {
        parent::beforeStart();

        $this->runner = Mage::getModel('M2ePro/Synchronization_Templates_Runner');

        $this->runner->setConnectorModel('Connector_Amazon_Product_Dispatcher');
        $this->runner->setMaxProductsPerStep(1000);

        $this->runner->setLockItem($this->getActualLockItem());
        $this->runner->setPercentsStart($this->getPercentsStart() + $this->getPercentsInterval()/2);
        $this->runner->setPercentsEnd($this->getPercentsEnd());

        $this->inspector = Mage::getModel('M2ePro/Amazon_Synchronization_Templates_Inspector');
        $this->inspector->setRunner($this->runner);

        $this->changesHelper = Mage::getModel('M2ePro/Synchronization_Templates_Changes');
        $this->changesHelper->setComponent($this->getComponent());
        $this->changesHelper->init();
    }

    protected function afterEnd()
    {
        $this->changesHelper->clearCache();
        $this->executeRunner();

        parent::afterEnd();
    }

    // -----------------------------------

    protected function performActions()
    {
        $result = true;

        $result = !$this->processTask('Templates_List') ? false : $result;
        $result = !$this->processTask('Templates_Revise') ? false : $result;
        $result = !$this->processTask('Templates_Relist') ? false : $result;
        $result = !$this->processTask('Templates_Stop') ? false : $result;

        return $result;
    }

    protected function makeTask($taskPath)
    {
        $task = parent::makeTask($taskPath);

        $task->setRunner($this->runner);
        $task->setInspector($this->inspector);
        $task->setChangesHelper($this->changesHelper);

        return $task;
    }

    //####################################

    private function executeRunner()
    {
        $this->getActualOperationHistory()->addTimePoint(__METHOD__,'Apply products changes on Amazon');

        $result = $this->runner->execute();
        $this->affectResultRunner($result);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__);
    }

    private function affectResultRunner($result)
    {
        if ($result == Ess_M2ePro_Helper_Data::STATUS_ERROR) {

            $resultString = 'errors';
            $resultType = Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR;
            $resultPriority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH;

        } else if ($result == Ess_M2ePro_Helper_Data::STATUS_WARNING) {

            $resultString = 'warnings';
            $resultType = Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING;
            $resultPriority = Ess_M2ePro_Model_Log_Abstract::PRIORITY_MEDIUM;

        } else {
            return;
        }

        $this->getLog()->addMessage(
            Mage::getModel('M2ePro/Log_Abstract')->encodeDescription(
                'Task "Inventory Synchronization" has completed with %result%. View %sl%listings log%el% for details.',
                array(
                    '!sl'=>'<a target="_blank" href="route:*/adminhtml_common_log/listing/;'.
                             'back:*/adminhtml_common_log/synchronization/;">',
                    '!el'=>'</a>',
                    '!result'=>$resultString
                )
            ), $resultType, $resultPriority
        );

        $this->getActualOperationHistory()->addText('Updating products on Amazon ended with '.$resultString.'.');
    }

    //####################################
}