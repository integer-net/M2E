<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Orders_Receive_Responser
    extends Ess_M2ePro_Model_Connector_Play_Orders_Get_ItemsResponser
{
    // ##########################################################

    protected $synchronizationLog = NULL;

    // ##########################################################

    public function unsetProcessingLocks(Ess_M2ePro_Model_Processing_Request $processingRequest)
    {
        parent::unsetProcessingLocks($processingRequest);

        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItemPrefix = Ess_M2ePro_Model_Play_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX;

        $nick = $lockItemPrefix . '_' . $this->params['account_id'];
        $lockItem->setNick($nick);
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->remove();

        // --------------------

        $this->getAccount()->deleteObjectLocks(NULL, $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks('synchronization_play', $processingRequest->getHash());
        $this->getAccount()->deleteObjectLocks(
            Ess_M2ePro_Model_Play_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX, $processingRequest->getHash()
        );
    }

    public function eventFailedExecuting($message)
    {
        parent::eventFailedExecuting($message);

        $this->getSynchronizationLog()->addMessage(
            Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
        );
    }

    // ##########################################################

    protected function processResponseData($response)
    {
        try {

            $account = $this->getAccount();
            if (!$account->getChildObject()->isOrdersModeEnabled()) {
                return;
            }

            $playOrders = $this->processPlayOrders($response, $account);
            if (empty($playOrders)) {
                return;
            }

            $this->createMagentoOrders($playOrders);

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

    private function processPlayOrders($response, Ess_M2ePro_Model_Account $account)
    {
        $ordersLastSynchronization = $account->getData('orders_last_synchronization');

        $orders = array();

        foreach ($response as $orderData) {
            $currentOrderDate = $orderData['purchase_create_date'];

            if (strtotime($currentOrderDate) > strtotime($ordersLastSynchronization)) {
                $ordersLastSynchronization = $currentOrderDate;
            }

            /** @var $orderBuilder Ess_M2ePro_Model_Play_Order_Builder */
            $orderBuilder = Mage::getModel('M2ePro/Play_Order_Builder');
            $orderBuilder->initialize($account, $orderData);

            $order = $orderBuilder->process();

            $orders[] = $order;
        }

        $account->setData('orders_last_synchronization', $ordersLastSynchronization)->save();

        return $orders;
    }

    private function createMagentoOrders($playOrders)
    {
        foreach ($playOrders as $order) {
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
            if ($order->getChildObject()->canCreateShipment()) {
                $order->createShipment();
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

    // ##########################################################

    protected function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_DEFAULTS);

        return $this->synchronizationLog;
    }

    // ##########################################################
}