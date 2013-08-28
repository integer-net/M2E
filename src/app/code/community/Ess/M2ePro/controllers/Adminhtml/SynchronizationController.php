<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Adminhtml_SynchronizationController extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //#############################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_setActiveMenu('m2epro/configuration')
             ->_title(Mage::helper('M2ePro')->__('M2E Pro'))
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/Configuration/SynchronizationHandler.js');

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('m2epro/configuration/synchronization');
    }

    //#############################################

    public function indexAction()
    {
        $this->_initAction()
             ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_synchronization'))
             ->renderLayout();
    }

    //#############################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/synchronization/settings/defaults/inspector/', 'mode',
            (int)$this->getRequest()->getParam('inspector_mode')
        );

        $components = json_decode($this->getRequest()->getParam('components'));

        foreach ($components as $component) {

            if ($component == Ess_M2ePro_Helper_Component_Ebay::NICK) {

                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/synchronization/settings/templates/', 'mode',
                    (int)$this->getRequest()->getParam('ebay_templates_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/synchronization/settings/orders/', 'mode',
                    (int)$this->getRequest()->getParam('ebay_orders_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/synchronization/settings/feedbacks/', 'mode',
                    (int)$this->getRequest()->getParam('ebay_feedbacks_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/synchronization/settings/other_listings/', 'mode',
                    (int)$this->getRequest()->getParam('ebay_other_listings_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/ebay/synchronization/settings/messages/', 'mode',
                    (int)$this->getRequest()->getParam('ebay_messages_mode')
                );

            } elseif ($component == Ess_M2ePro_Helper_Component_Amazon::NICK) {

                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/amazon/synchronization/settings/templates/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_templates_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/amazon/synchronization/settings/orders/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_orders_mode')
                );
                Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
                    '/amazon/synchronization/settings/other_listings/', 'mode',
                    (int)$this->getRequest()->getParam('amazon_other_listings_mode')
                );

            } elseif ($component == Ess_M2ePro_Helper_Component_Buy::NICK) {

                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/buy/synchronization/settings/templates/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('buy_templates_mode'));
                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/buy/synchronization/settings/orders/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('buy_orders_mode'));
                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/buy/synchronization/settings/other_listings/',
                                                             'mode',
                                                             (int)$this->getRequest()
                                                                        ->getParam('buy_other_listings_mode'));

            }  elseif ($component == Ess_M2ePro_Helper_Component_Play::NICK) {

                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/play/synchronization/settings/templates/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('play_templates_mode'));
                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/play/synchronization/settings/orders/',
                                                             'mode',
                                                             (int)$this->getRequest()->getParam('play_orders_mode'));
                Mage::helper('M2ePro/Module')->getConfig()
                                             ->setGroupValue('/play/synchronization/settings/other_listings/',
                                                             'mode',
                                                             (int)$this->getRequest()
                                                                        ->getParam('play_other_listings_mode'));
            }
        }

        exit();
    }

    public function clearLogAction()
    {
        $synchTask = $this->getRequest()->getParam('synch_task');
        $component = $this->getRequest()->getParam('component');

        if (empty($component)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Component can\'be empty'));
            return $this->_redirect('*/*/index');
        }
        if (is_null($synchTask)) {
            $this->_getSession()->addError(Mage::helper('M2ePro')->__('Please select item(s) to clear'));
            return $this->_redirect('*/*/index');
        }

        Mage::getModel('M2ePro/Synchronization_Log')
                ->setComponentMode($component)
                ->clearMessages($synchTask);

        $this->_getSession()->addSuccess(
            Mage::helper('M2ePro')->__('The synchronization task log has been successfully cleaned.')
        );
        $this->_redirectUrl(Mage::helper('M2ePro')->getBackUrl('index'));
    }

    //#############################################

    public function runAllEnabledNowAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES,
                                       Ess_M2ePro_Model_Synchronization_Tasks::ORDERS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::FEEDBACKS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::MESSAGES,
                                       Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    //------------------------

    public function runNowTemplatesAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::TEMPLATES
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    public function runNowOrdersAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::ORDERS
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
    }

    public function runNowOtherListingsAction()
    {
        session_write_close();

        $synchDispatcher = Mage::getModel('M2ePro/Synchronization_Dispatcher');
        $synchDispatcher->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_USER);
        $synchDispatcher->setComponents(json_decode($this->getRequest()->getParam('components')));
        $synchDispatcher->setTasks(array(
                                       Ess_M2ePro_Model_Synchronization_Tasks::DEFAULTS,
                                       Ess_M2ePro_Model_Synchronization_Tasks::OTHER_LISTINGS
                                   ));
        $synchDispatcher->setParams(array());
        $synchDispatcher->process();
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

        $synchronizationEbayOtherListingsProcessing = Mage::getModel('M2ePro/LockItem')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_ebay_other_listings%'))
            ->getSize();

        if ($synchronizationEbayOtherListingsProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'eBay 3rd Party Listings are being downloaded now. ' .
                'They will be available soon in %s > Manage Listing > 3rd Party Listings. ' .
                'You can continue working with M2E Pro.',
                Mage::helper('M2ePro/Module')->getMenuRootNodeLabel()
            );
        }

        exit(json_encode(array(
            'messages' => $warningMessages
        )));
    }

    //#############################################
}