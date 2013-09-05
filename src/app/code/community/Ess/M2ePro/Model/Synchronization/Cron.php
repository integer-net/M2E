<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Synchronization_Cron
{
    //####################################

    public function process()
    {
        Mage::helper('M2ePro/Client')->setMemoryLimit(512);
        Mage::helper('M2ePro/Module_Exception')->setFatalErrorHandler();

        /** @var $synchDispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_CRON);
        $synchDispatcher->setComponents(array(
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Buy::NICK,
            Ess_M2ePro_Helper_Component_Play::NICK,
        ));
        $synchDispatcher->setTasks(array(
            Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
            Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
            Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
        ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    //####################################
}