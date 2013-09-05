<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Cancellation
    extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    // ->__('eBay Orders Cancellation Synchronization')
    private $name = 'eBay Orders Cancellation Synchronization';

    private $totalOpenedUnpaidItemProcesses = 0;
    private $totalCanceledMagentoOrders = 0;

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //$this->createRunnerActions();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        //$this->executeRunnerActions();
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_ORDERS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle($this->name);
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__($this->name));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is started. Please wait...', $this->name));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "%s" is finished. Please wait...', $this->name));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Ebay_Account::ORDERS_MODE_YES);

        $accountsTotalCount = $accountsCollection->getSize();

        $accountIteration = 1;
        $percentsForAccount = self::PERCENTS_INTERVAL;

        if ($accountsTotalCount > 0) {
            $percentsForAccount = self::PERCENTS_INTERVAL/(int)$accountsCollection->getSize();
        }

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Model_Order_Log::INITIATOR_EXTENSION);

        foreach ($accountsCollection->getItems() as $accountObj) {

            /** @var $accountObj Ess_M2ePro_Model_Account */

            $this->processAccount($accountObj, $percentsForAccount);

            $this->_lockItem->setPercents(self::PERCENTS_START + $percentsForAccount*$accountIteration);
            $this->_lockItem->activate();
            $accountIteration++;
        }

        $this->_profiler->saveTimePoint(__METHOD__);
    }

    //####################################

    private function processAccount(Ess_M2ePro_Model_Account $account, $percentsForAccount)
    {
        //---------------------------
        $this->_profiler->addEol();
        $this->_profiler->addTitle('Starting account "'.$account->getTitle().'"');

        // ->__('Task "Orders Cancellation Synchronization" for eBay account: "%s" is started. Please wait...')
        $status = 'Task "Orders Cancellation Synchronization" for eBay account: "%s" is started. Please wait...';
        $tempString = Mage::helper('M2ePro')->__($status, $account->getTitle());
        $this->_lockItem->setStatus($tempString);

        $currentPercent = $this->_lockItem->getPercents();
        //---------------------------

        if (!$account->getChildObject()->shouldCreateMagentoOrderImmediately()
            || $account->getChildObject()->getMagentoOrdersReservationDays() <= 0
        ) {
            return;
        }

        $data = $this->getUnpaidOrdersUpdates($account);

        //---------------------------
        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getId(),'Get unpaid orders to process');

        $this->_profiler->addTitle('Total unpaid orders updates: '.count($data));

        $currentPercent = $currentPercent + $percentsForAccount * 0.1;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();
        //---------------------------

        if (count($data) == 0) {
            return;
        }

        //---------------------------
        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getId());
        $this->_profiler->addTimePoint(__METHOD__.'process'.$account->getId(),'Processing unpaid orders');

        $status = 'Task "Orders Synchronization" for eBay account: ';
        $status .= '"%s" is in data processing state. Please wait...';

        $tempString = Mage::helper('M2ePro')->__($status, $account->getTitle());
        $this->_lockItem->setStatus($tempString);
        //---------------------------

        /** @var $cancellationCandidates Ess_M2ePro_Model_Order[] */
        $cancellationCandidates = array();
        foreach ($data as $orderData) {
            $cancellationCandidates[] = $this->associateAndUpdateOrder($account, $orderData);
        }

        //---------------------------
        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getId(),'Update unpaid orders with data from eBay');

        $currentPercent = $currentPercent + $percentsForAccount * 0.1;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();
        //---------------------------

        $cancellationCandidates = array_filter($cancellationCandidates);

        if (count($cancellationCandidates) == 0) {
            return;
        }

        //---------------------------
        $this->totalOpenedUnpaidItemProcesses = 0;
        $this->totalCanceledMagentoOrders = 0;

        $percentPerOrder = ($percentsForAccount - $currentPercent) / count($cancellationCandidates);
        $tempPercent = 0;
        //---------------------------

        foreach ($cancellationCandidates as $order) {

            $this->processOrder($order);

            //---------------------------
            $tempPercent += $percentPerOrder;

            if (floor($tempPercent) > 0) {
                $currentPercent += floor($tempPercent);
                $tempPercent -= floor($tempPercent);

                $this->_lockItem->setPercents($currentPercent);
                $this->_lockItem->activate();
            }
            //---------------------------
        }

        //---------------------------
        $this->_profiler->saveTimePoint(__METHOD__.'cancellation_process'.$account->getId());

        $this->_profiler->addTitle('Total count unpaid item processes opened: '.$this->totalOpenedUnpaidItemProcesses);
        $this->_profiler->addTitle('Total count magento orders canceled: '.$this->totalCanceledMagentoOrders);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('End account "'.$account->getTitle().'"');
        //---------------------------
    }

    //####################################

    private function getUnpaidOrdersUpdates(Ess_M2ePro_Model_Account $account)
    {
        $reservationDays = $account->getChildObject()->getMagentoOrdersReservationDays();
        list($startDate, $endDate) = $this->getDateRangeForUnpaidOrders($reservationDays);

        $ordersIds = Mage::getResourceModel('M2ePro/Ebay_Order')
            ->getCancellationCandidatesChannelIds($account->getId(), $startDate, $endDate);

        if (count($ordersIds) == 0) {
            return array();
        }

        $request = array('orders_ids' => $ordersIds);
        $response = Mage::getModel('M2ePro/Connector_Server_Ebay_Dispatcher')
            ->processVirtualAbstract('sales', 'get', 'orders', $request, NULL, NULL, $account, NULL);

        return isset($response['orders']) ? $response['orders'] : array();
    }

    private function getDateRangeForUnpaidOrders($reservationDays)
    {
        $reservationDays = (int)$reservationDays;

        if ($reservationDays < 1) {
            throw new InvalidArgumentException('Reservation period cannot be less than 1 day.');
        }

        $endDate = new DateTime('now', new DateTimeZone('UTC'));
        $endDate->modify("-{$reservationDays} days");

        $startDate = clone $endDate;
        $startDate->modify('-3 days');

        return array($startDate, $endDate);
    }

    //####################################

    private function associateAndUpdateOrder(Ess_M2ePro_Model_Account $account, array $orderData)
    {
        /** @var $order Ess_M2ePro_Model_Order */
        $order = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order')
            ->addFieldToFilter('account_id', $account->getId())
            ->addFieldToFilter('ebay_order_id', $orderData['ebay_order_id'])
            ->getFirstItem();

        if (!$order->getId()) {
            return null;
        }

        $order->setAccount($account);

        /** @var $helper Ess_M2ePro_Model_Ebay_Order_Helper */
        $helper = Mage::getSingleton('M2ePro/Ebay_Order_Helper');

        $checkoutStatus = $helper->getCheckoutStatus($orderData['checkout_status']);
        $paymentStatus = $helper->getPaymentStatus(
            $orderData['payment_method'], $orderData['payment_date'], $orderData['payment_status_ebay']
        );
        $shippingStatus = $helper->getShippingStatus(
            $orderData['shipping_date'], $orderData['shipping_method_selected']
        );

        if ($paymentStatus == Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED) {
            $paymentMethodName = $helper->getPaymentMethodNameByCode(
                $orderData['payment_method'], $order->getMarketplaceId()
            );

            $order->setData('payment_status', $paymentStatus);
            $order->setData('payment_method', $paymentMethodName);
        }

        if (!$order->getChildObject()->isCheckoutCompleted() &&
            $checkoutStatus == Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED) {

            $shippingMethodName = $helper->getShippingMethodNameByCode(
                $orderData['shipping_method'], $order->getMarketplaceId()
            );

            $order->setData('tax_rate', (float)$orderData['tax_rate']);
            $order->setData('tax_state', $orderData['tax_state']);
            $order->setData('tax_amount', (float)$orderData['tax_amount']);
            $order->setData('tax_includes_shipping', (int)$orderData['tax_includes_shipping']);

            $order->setData('shipping_address', json_encode($orderData['shipping_address']));
            $order->setData('shipping_status', $shippingStatus);
            $order->setData('shipping_method', $shippingMethodName);
            $order->setData('shipping_price', (float)$orderData['shipping_price']);
        }

        $order->save();

        return $order;
    }

    //####################################

    private function processOrder(Ess_M2ePro_Model_Order $order)
    {
        if ($order->getChildObject()->isPaymentCompleted()) {
            // unpaid order became paid
            // immediately created magento order should be canceled
            // and new magento order should be created instead

            if ($order->canCancelMagentoOrder()) {
                $message = 'Payment Status was updated to Paid on eBay. '.
                           'As Magento Order #%order_id% can have wrong data, it have to be cancelled.';
                $order->addWarningLog($message, array('!order_id' => $order->getMagentoOrder()->getRealOrderId()));

                try {
                    $order->cancelMagentoOrder();
                    $this->totalCanceledMagentoOrders++;
                } catch (Exception $e) {
                    // magento order was not cancelled
                    // do not create new magento order to prevent oversell
                    return;
                }
            }

            $this->clearOrder($order);
            $this->createMagentoOrder($order);
        } else {
            // unpaid order did not become paid
            // immediately created magento order should be canceled
            // and unpaid item process should be opened for each order item

            if ($order->canCancelMagentoOrder()) {
                $message = 'Payment Status was not updated to Paid. Magento Order #%order_id% '.
                           'have to be cancelled according to Account\'s Automatic Cancellation setting.';
                $order->addWarningLog($message, array('!order_id' => $order->getMagentoOrder()->getRealOrderId()));

                try {
                    $order->cancelMagentoOrder();
                    $this->totalCanceledMagentoOrders++;
                } catch (Exception $e) {}
            }

            $this->openUnpaidItemProcess($order);
        }
    }

    private function clearOrder(Ess_M2ePro_Model_Order $order)
    {
        $order->setMagentoOrder(null);
        $order->setData('magento_order_id', null);
        $order->save();

        $order->getItemsCollection()->walk('setProduct', array(null));
    }

    private function createMagentoOrder(Ess_M2ePro_Model_Order $order)
    {
        if ($order->canCreateMagentoOrder()) {
            try {
                $order->createMagentoOrder();
            } catch (Exception $e) {
                Mage::helper('M2ePro/Module_Exception')->process($e);
            }
        }
        if ($order->getChildObject()->canCreatePaymentTransaction()) {
            $order->getChildObject()->createPaymentTransactions();
        }
        if ($order->getChildObject()->canCreateInvoice()) {
            $order->createInvoice();
        }
        if ($order->getChildObject()->canCreateShipment()) {
            $order->createShipment();
        }
        if ($order->getChildObject()->canCreateTracks()) {
            $order->getChildObject()->createTracks();
        }
        $order->updateMagentoOrderStatus();
    }

    //####################################

    private function openUnpaidItemProcess(Ess_M2ePro_Model_Order $order)
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Order_Item');
        $collection->addFieldToFilter('order_id', $order->getId());
        $collection->addFieldToFilter(
            'unpaid_item_process_state', Ess_M2ePro_Model_Ebay_Order_Item::UNPAID_ITEM_PROCESS_NOT_OPENED
        );

        $items = $collection->getItems();

        if (count($items) == 0) {
            return;
        }

        $action = Ess_M2ePro_Model_Connector_Server_Ebay_OrderItem_Dispatcher::ACTION_ADD_DISPUTE;
        $params = array(
            'explanation' => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_EXPLANATION_BUYER_HAS_NOT_PAID,
            'reason'      => Ess_M2ePro_Model_Ebay_Order_Item::DISPUTE_REASON_BUYER_HAS_NOT_PAID
        );

        /** @var $dispatcher Ess_M2ePro_Model_Connector_Server_Ebay_OrderItem_Dispatcher */
        $dispatcher = Mage::getModel('M2ePro/Connector_Server_Ebay_OrderItem_Dispatcher');
        $dispatcher->process($action, $items, $params);

        $this->totalOpenedUnpaidItemProcesses += count($items);
    }

    //####################################
}