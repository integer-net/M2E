<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Ebay_SynchronizationController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        return $this->_redirect('*/adminhtml_synchronization/index');
    }

    //#############################################

    public function runNowFeedbacksAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    public function runNowMessagesAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    //#############################################
}