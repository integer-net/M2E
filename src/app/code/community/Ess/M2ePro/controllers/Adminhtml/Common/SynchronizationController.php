<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_Common_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Common_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/SynchronizationHandler.js');

        $this->_initPopUp();

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro_common/configuration/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_common_synchronization'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        $components = json_decode($this->getRequest()->getParam('components'));

        foreach ($components as $component) {

            if ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {

                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                    '/amazon/templates/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_templates_mode')
                );
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                    '/amazon/orders/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_orders_mode')
                );
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
                    '/amazon/other_listings/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_other_listings_mode')
                );

            } elseif ($component == Ess_M2ePro_Helper_Component_Buy::NICK) {

                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/buy/templates/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('buy_templates_mode'));
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/buy/orders/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('buy_orders_mode'));
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/buy/other_listings/',
                                                             'mode',
                                                             (int)$this->getRequest()
                                                                        ->getParam('buy_other_listings_mode'));

            } elseif ($component == Ess_M2ePro_Helper_Component_Play::NICK) {

                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/play/templates/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('play_templates_mode'));
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/play/orders/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('play_orders_mode'));
                Mage::helper('M2ePro/Module')->getSynchronizationConfig()
                                             ->setGroupValue('/play/other_listings/',
                                                             'mode',
                                                             (int)$this->getRequest()
                                                                        ->getParam('play_other_listings_mode'));
            }
        }
    }

    public function clearLogAction()
    {
        $task = $this->getRequest()->getParam('task');
        $component = $this->getRequest()->getParam('component');

        if (empty($component)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Component can\'be empty'));
            return $this->_redirect('*/*/index');
        }
        if (is_null($task)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        Mage::getModel('M2ePro/Synchronization_Log')
                ->setComponentMode($component)
                ->clearMessages($task);

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The synchronization task log has been successfully cleaned.')
        );
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('index'));
    }

    //#############################################

    public function runAllEnabledNowAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(json_decode($this->getRequest()->getParam('components')));
        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS,
            Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS
        ));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    //------------------------

    public function runNowTemplatesAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(json_decode($this->getRequest()->getParam('components')));
        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::TEMPLATES
        ));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    public function runNowOrdersAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(json_decode($this->getRequest()->getParam('components')));
        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::ORDERS
        ));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    public function runNowOtherListingsAction()
    {
        session_write_close();

        /** @var $dispatcher Ess_M2ePro_Model_Synchronization_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');

        $dispatcher->setAllowedComponents(json_decode($this->getRequest()->getParam('components')));
        $dispatcher->setAllowedTasksTypes(array(
            Ess_M2ePro_Model_Synchronization_Task::DEFAULTS,
            Ess_M2ePro_Model_Synchronization_Task::OTHER_LISTINGS
        ));

        $dispatcher->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_USER);
        $dispatcher->setParams(array());

        $dispatcher->process();
    }

    //#############################################

    public function synchCheckProcessingNowAction()
    {
        $warningMessages = array();

        $amazonProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_amazon%'))
            ->getSize();

        $buyProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_buy%'))
            ->getSize();

        $playProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_play%'))
            ->getSize();

        if ($amazonProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Amazon. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        if ($buyProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Rakuten.com. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        if ($playProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Play.com. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        return $this->getResponse()->setBody(json_encode(array(
            'messages' => $warningMessages
        )));
    }

    //#############################################

    public function runReviseAllAction()
    {
        $startDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $component = $this->getRequest()->getParam('component');

        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'start_date', $startDate
        );
        Mage::helper('M2ePro/Module')->getSynchronizationConfig()->setGroupValue(
            "/{$component}/templates/revise/total/", 'last_listing_product_id', 0
        );

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->getResponse()->setBody(json_encode(array(
            'start_date' => Mage::app()->getLocale()->date(strtotime($startDate))->toString($format)
        )));

    }

    //#############################################
}