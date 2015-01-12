<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive_Responser
    extends Ess_M2ePro_Model_Connector_Amazon_Orders_Get_ItemsResponser
{
    // ##########################################################

    protected $synchronizationLog = NULL;

    // ##########################################################

    protected function unsetLocks($fail = false, $message = NULL)
    {
        /** @var $lockItem Ess_M2ePro_Model_LockItem */
        $lockItem = Mage::getModel('M2ePro/LockItem');

        $lockItem->setNick(
            Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX.'_'.$this->params['account_id']
        );
        $lockItem->setMaxInactiveTime(Ess_M2ePro_Model_Processing_Request::MAX_LIFE_TIME_INTERVAL);
        $lockItem->remove();

        // ----------------------

        $tempObjects = array(
            $this->getAccount(), $this->getMarketplace()
        );

        $tempLocks = array(
            NULL,
            'synchronization', 'synchronization_amazon',
            Ess_M2ePro_Model_Amazon_Synchronization_Orders_Receive::LOCK_ITEM_PREFIX
        );

        /* @var $object Ess_M2ePro_Model_Abstract */
        foreach ($tempObjects as $object) {
            foreach ($tempLocks as $lock) {
                $object->deleteObjectLocks($lock,$this->hash);
            }
        }

        $fail && $this->getSynchronizationLog()->addMessage(Mage::helper('M2ePro')->__($message),
            Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR,
            Ess_M2ePro_Model_Log_Abstract::PRIORITY_HIGH);
    }

    // ##########################################################

    protected function processResponseData($response)
    {
        $response = parent::processResponseData($response);

        Mage::getSingleton('M2ePro/Order_Log_Manager')->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        try {

            $account = $this->getAccount();
            if (!$account->getChildObject()->isOrdersModeEnabled()) {
                return;
            }

            $amazonOrders = $this->processAmazonOrders($response, $account);
            if (empty($amazonOrders)) {
                return;
            }

            $this->createMagentoOrders($amazonOrders);

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

    private function processAmazonOrders($response, Ess_M2ePro_Model_Account $account)
    {
        $ordersLastSynchronization = $account->getData('orders_last_synchronization');

        $orders = array();

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

        $account->setData('orders_last_synchronization', $ordersLastSynchronization)->save();

        return $orders;
    }

    private function createMagentoOrders($amazonOrders)
    {
        foreach ($amazonOrders as $order) {
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

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAccount()->getChildObject()->getMarketplace();
    }

    // ##########################################################

    private function getSynchronizationLog()
    {
        if (!is_null($this->synchronizationLog)) {
            return $this->synchronizationLog;
        }

        $this->synchronizationLog = Mage::getModel('M2ePro/Synchronization_Log');
        $this->synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Amazon::NICK);
        $this->synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        return $this->synchronizationLog;
    }

    // ##########################################################
}