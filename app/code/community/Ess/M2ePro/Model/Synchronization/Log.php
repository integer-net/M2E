<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Log extends Ess_M2ePro_Model_Log_Abstract
{
    const SYNCH_TASK_UNKNOWN = 0;
    const _SYNCH_TASK_UNKNOWN = 'System';

    const SYNCH_TASK_DEFAULTS = 1;
    const _SYNCH_TASK_DEFAULTS = 'Default Synchronization';
    const SYNCH_TASK_TEMPLATES = 2;
    const _SYNCH_TASK_TEMPLATES = 'Templates Synchronization';
    const SYNCH_TASK_ORDERS = 3;
    const _SYNCH_TASK_ORDERS = 'Orders Synchronization';
    const SYNCH_TASK_FEEDBACKS = 4;
    const _SYNCH_TASK_FEEDBACKS = 'Feedbacks Synchronization';
    const SYNCH_TASK_MARKETPLACES = 5;
    const _SYNCH_TASK_MARKETPLACES = 'Marketplaces Synchronization';
    const SYNCH_TASK_OTHER_LISTINGS = 6;
    const _SYNCH_TASK_OTHER_LISTINGS = '3rd Party Listings Synchronization';
    const SYNCH_TASK_MESSAGES = 7;
    const _SYNCH_TASK_MESSAGES = 'Messages Synchronization';

    /**
     * @var null|int
     */
    private $_synchRun = NULL;

    /**
     * @var int
     */
    private $_synchTask = self::SYNCH_TASK_UNKNOWN;

    /**
     * @var int
     */
    protected $_initiator = Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN;

    //####################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Synchronization_Log');
    }

    //####################################

    public function setSynchronizationRun($id)
    {
        $this->_synchRun = (int)$id;
    }

    public function setInitiator($initiator = parent::INITIATOR_UNKNOWN)
    {
        $this->_initiator = (int)$initiator;
    }

    public function setSynchronizationTask($task = self::SYNCH_TASK_UNKNOWN)
    {
        $this->_synchTask = (int)$task;
    }

    //####################################

    public function addMessage($description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = $this->makeDataForAdd( $this->makeCreator() ,
                                             $description ,
                                             $type ,
                                             $priority );

        $this->createMessage($dataForAdd);
    }

    //####################################

    public function getActionTitle($type)
    {
        return $this->getActionTitleByClass(__CLASS__,$type);
    }

    public function getActionsTitles()
    {
        return $this->getActionsTitlesByClass(__CLASS__,'SYNCH_TASK_');
    }

    public function clearMessages($synchTask = NULL)
    {
        $columnName = !is_null($synchTask) ? 'synch_task' : NULL;
        $this->clearMessagesByTable('M2ePro/Synchronization_Log',$columnName,$synchTask);
    }

    //####################################

    private function createMessage($dataForAdd)
    {
        $dataForAdd['synchronization_run_id'] = $this->_synchRun;
        $dataForAdd['synch_task'] = $this->_synchTask;
        $dataForAdd['initiator'] = $this->_initiator;
        $dataForAdd['component_mode'] = $this->componentMode;

        Mage::getModel('M2ePro/Synchronization_Log')
                 ->setData($dataForAdd)
                 ->save()
                 ->getId();
    }

    private function makeDataForAdd($creator , $description = NULL , $type = NULL , $priority = NULL)
    {
        $dataForAdd = array();

        $dataForAdd['creator'] = $creator;

        if (!is_null($description)) {
            $dataForAdd['description'] = Mage::helper('M2ePro')->__($description);
        } else {
            $dataForAdd['description'] = NULL;
        }

        if (!is_null($type)) {
            $dataForAdd['type'] = (int)$type;
        } else {
            $dataForAdd['type'] = self::TYPE_NOTICE;
        }

        if (!is_null($priority)) {
            $dataForAdd['priority'] = (int)$priority;
        } else {
            $dataForAdd['priority'] = self::PRIORITY_LOW;
        }

        return $dataForAdd;
    }

    //####################################
}