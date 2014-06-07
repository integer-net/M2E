<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_Synchronization extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'synchronization';

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return Ess_M2ePro_Model_Synchronization_Dispatcher::MAX_MEMORY_LIMIT;
    }

    //####################################

    protected function performActions()
    {
        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setParentLockItem($this->getLockItem());
        $dispatcher->setParentOperationHistory($this->getOperationHistory());

        $dispatcher->setAllowedComponents(array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Buy::NICK,
            Ess_M2ePro_Helper_Component_Play::NICK,
        ));

        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS,
            Ess_M2ePro_Model_Synchronization_Task::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS
        ));

        $dispatcher->setInitiator($this->getInitiator());
        $dispatcher->setParams(array());

        return $dispatcher->process();
    }

    //####################################
}