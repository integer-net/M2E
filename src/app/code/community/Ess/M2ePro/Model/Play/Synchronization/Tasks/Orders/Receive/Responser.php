<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Play_Synchronization_Tasks_Orders_Receive_Responser
    extends Ess_M2ePro_Model_Connector_Play_Orders_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');
        $lockItemPrefix = Ess_M2ePro_Model_Play_Synchronization_Tasks_Orders_Receive::LOCK_ITEM_PREFIX;

        $nick = $lockItemPrefix . '_' . $this->params['account_id'];
        $lockItem->setNick($nick);
        $lockItem->remove();

        // --------------------

        $tempObjects = array(
            $this->getAccount(),
            Mage::helper('M2ePro/Component_Play')->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_play',
            $lockItemPrefix
        );

        /* @var Ess_M2ePro_Model_Abstract $object */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->deleteObjectLocks($lock,$this->hash);
            }
        }

        $fail && $this->getSynchLogModel()->addMessage(Mage::helper('M2ePro')->__($message),
                                                       Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                                                       Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    // ########################################

    protected function processResponseData($response)
    {
        $response = parent::processResponseData($response);

        try {

            $account = $this->getAccount();

            if (!$account->getChildObject()->isOrdersModeEnabled()) {
                return;
            }

            $orders = array();

            $ordersLastSynchronization = $account->getData('orders_last_synchronization');

            // Create m2e orders
            //---------------------------
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
            //---------------------------

            $account->setData('orders_last_synchronization', $ordersLastSynchronization)->save();

            if (count($orders) == 0) {
                return;
            }

            // Create magento orders
            //---------------------------
            foreach ($orders as $order) {
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
            //---------------------------

        } catch (Exception $exception) {

            $this->getSynchLogModel()->addMessage(
                Mage::helper('M2ePro')->__($exception->getMessage()),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
                Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH
            );

            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getObjectByParam('Account','account_id');
    }

    //-----------------------------------------

    protected function getSynchLogModel()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        /** @var $runs Ess_M2ePro_Model_Synchronization_Run */
        $runs = Mage::getModel('M2ePro/Synchronization_Run');
        $runs->start(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $runsId = $runs->getLastId();
        $runs->stop();

        /** @var $logs Ess_M2ePro_Model_Synchronization_Log */
        $logs = Mage::getModel('M2ePro/Synchronization_Log');
        $logs->setSynchronizationRun($runsId);
        $logs->setComponentMode(Ess_M2ePro_Helper_Component_Play::NICK);
        $logs->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        $this->synchronizationLog = $logs;

        return $this->synchronizationLog;
    }

    // ########################################
}