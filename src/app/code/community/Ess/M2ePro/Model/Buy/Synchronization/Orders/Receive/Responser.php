<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Synchronization_Orders_Receive_Responser
    extends Ess_M2ePro_Model_Connector_Buy_Orders_Get_ItemsResponser
{
    // ##########################################################

    /** @var Ess_M2ePro_Model_Synchronization_Log $synchronizationLog */
    protected $synchronizationLog = NULL;

    // ##########################################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItemPrefix = Ess_M2ePro_Model_Buy_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX;

        $nick = $lockItemPrefix . '_' . $this->params['account_id'];
        $lockItem->setNick($nick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->remove();

        // --------------------

        $tempObjects = array(
            $this->getAccount(),
            Mage::helper('M2ePro/Component_Buy')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_buy',
            $lockItemPrefix
        );

        /* @var Ess_M2ePro_Model_Abstract $object */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->deleteObjectLocks($lock,$this->hash);
            }
        }

        $fail && $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($message),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    protected function processResponseData($response)
    {
        $response = parent::processResponseData($response);

        try {

            $account = $this->getAccount();
            if (!$account->getChildObject()->isOrdersModeEnabled()) {
                return;
            }

            $buyOrders = $this->processBuyOrders($response, $account);
            if (empty($buyOrders)) {
                return;
            }

            $this->createMagentoOrders($buyOrders);

        } catch (Exception $exception) {

            $this->getSynchronizationLog()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ----------------------------------------------------------

    private function processBuyOrders($response, Ess_M2ePro_Model_Account $account)
    {
        $orders = array();

        foreach ($response as $orderData) {
            /** @var $orderBuilder Ess_M2ePro_Model_Buy_Order_Builder */
            $orderBuilder = Mage::getModel('M2ePro/Buy_Order_Builder');
            $orderBuilder->initialize($account, $orderData);

            $order = $orderBuilder->process();

            $orders[] = $order;
        }

        return $orders;
    }

    private function createMagentoOrders($buyOrders)
    {
        foreach ($buyOrders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */
            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->createMagentoOrder();
                } catch (Exception $e) {
                    Mage::helper('M2ePro/Module_Exception')->process($e);
                }
            }
            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice();
            }
            if ($order->getStatusUpdateRequired()) {
                $order->updateMagentoOrderStatus();
            }
        }
    }

    // ##########################################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    // ----------------------------------------------------------

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Buy::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    // ##########################################################
}