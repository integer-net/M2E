<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsResponser
{
    protected $synchronizationLog = NULL;

    // ########################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $lockItem->setNick(
            Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders::LOCK_ITEM_PREFIX.'_'.$this->params['account_id']
        );
        $lockItem->remove();

        // ----------------------

        $tempObjects = array(
            $this->getAccount(), $this->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_amazon',
            Ess_M2ePro_Model_Amazon_Synchronization_Tasks_Orders::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
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

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_EXTENSION);

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
                $currentOrderUpdateDate = $orderData['purchase_update_date'];

                if (strtotime($currentOrderUpdateDate) > strtotime($ordersLastSynchronization)) {
                    $ordersLastSynchronization = $currentOrderUpdateDate;
                }

                /** @var $orderBuilder Ess_M2ePro_Model_Amazon_Order_Builder */
                $orderBuilder = Mage::getModel('M2ePro/Amazon_Order_Builder');
                $orderBuilder->initialize($account, $orderData);

                $order = $orderBuilder->process();

                if (!$order) {
                    continue;
                }

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

                if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
                    $order->getReserve()->place();
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
                // -------------
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

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
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
        $logs->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $logs->setInitiator(Ess_M2ePro_Model_Synchronization_Run::INITIATOR_UNKNOWN);
        $logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        $this->synchronizationLog = $logs;

        return $this->synchronizationLog;
    }

    // ########################################
}