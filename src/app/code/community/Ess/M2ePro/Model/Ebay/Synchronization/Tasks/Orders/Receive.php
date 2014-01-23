<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

class Ess_M2ePro_Model_Ebay_Synchronization_Tasks_Orders_Receive extends Ess_M2ePro_Model_Ebay_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    //####################################

    // ->__('eBay Orders Receive Synchronization')
    private $name = 'eBay Orders Receive Synchronization';

    //####################################

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
        $this->_profiler->addTimePoint(__METHOD__,'Get and process orders from eBay');

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

    protected function processAccount(Ess_M2ePro_Model_Account $account, $percentsForAccount)
    {
        $this->_profiler->addEol();
        $this->_profiler->addTitle('Starting account "'.$account->getTitle().'"');

        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'get'.$account->getId(),'Get orders from eBay');

        // ->__('Task "%s" for eBay account: "%s" is started. Please wait...')
        $status = 'Task "%s" for eBay account: "%s" is started. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        $currentPercent = $this->_lockItem->getPercents();

        // Get from time
        //---------------------------
        $fromTime = $this->prepareSinceTime($account->getData('orders_last_synchronization'));
        //---------------------------

        // Get orders from eBay
        //---------------------------
        $request = array(
            'last_update' => $fromTime
        );

        if (is_null($account->getData('orders_last_synchronization'))) {
            $account->setData('orders_last_synchronization', $fromTime)->save();
        }

        $entity = 'sales';
        $type   = 'get';
        $name   = 'list';

        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
            ->processVirtual($entity, $type, $name, $request, NULL, NULL, $account);

        $ebayOrders = array();
        $toTime = $fromTime;

        if (isset($response['orders']) && isset($response['updated_to'])) {
            $ebayOrders = $response['orders'];
            $toTime = $response['updated_to'];
        }

        if (count($ebayOrders) == 0) {
            return;
        }
        //---------------------------

        $currentPercent = $currentPercent + $percentsForAccount * 0.15;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        $this->_profiler->saveTimePoint(__METHOD__.'get'.$account->getId());

        $this->_profiler->addTitle('Total count orders received from eBay: '.count($ebayOrders));
        $this->_profiler->addTimePoint(__METHOD__.'process'.$account->getId(),'Processing received orders from eBay');

        // ->__('Task "%s" for eBay account: "%acc%" is in data processing state. Please wait...')
        $status = 'Task "%s" for eBay account: "%s" is in data processing state. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        // Save eBay orders
        //---------------------------
        $orders = array();

        foreach ($ebayOrders as $ebayOrderData) {
            /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order_Builder */
            $ebayOrder = Mage::getModel('M2ePro/Ebay_Order_Builder');
            $ebayOrder->initialize($account, $ebayOrderData);

            $orders[] = $ebayOrder->process();
        }
        //---------------------------

        $account->setData('orders_last_synchronization', $toTime)->save();

        /** @var $orders Ess_M2ePro_Model_Order[] */
        $orders = array_filter($orders);

        if (count($orders) == 0) {
            return;
        }

        $currentPercent = $currentPercent + $percentsForAccount * 0.05;
        $this->_lockItem->setPercents($currentPercent);
        $this->_lockItem->activate();

        $this->_profiler->saveTimePoint(__METHOD__.'process'.$account->getId());

        $this->_profiler->addEol();
        $this->_profiler->addTimePoint(__METHOD__.'magento_orders_process'.$account->getId(),'Creating magento orders');

        // ->__('Task "%s" for eBay account: "%s" is in order creation state.. Please wait...')
        $status = 'Task "%s" for eBay account: "%s" is in order creation state.. Please wait...';
        $status = Mage::helper('M2ePro')->__($status, $this->name, $account->getTitle());
        $this->_lockItem->setStatus($status);

        // Create magento orders
        //---------------------------
        $magentoOrders = $paymentTransactions = $invoices = $shipments = $tracks = 0;

        $percentPerOrder = ($percentsForAccount - $currentPercent) / count($orders);
        $tempPercent = 0;

        foreach ($orders as $order) {
            /** @var $order Ess_M2ePro_Model_Order */
            if ($order->canCreateMagentoOrder()) {
                try {
                    $order->createMagentoOrder();
                    $magentoOrders++;
                } catch (Exception $e) {
                    Mage::helper('M2ePro/Module_Exception')->process($e);
                }
            }

            if ($order->getReserve()->isNotProcessed() && $order->isReservable()) {
                $order->getReserve()->place();
            }

            if ($order->getChildObject()->canCreatePaymentTransaction()) {
                $order->getChildObject()->createPaymentTransactions() && $paymentTransactions++;
            }
            if ($order->getChildObject()->canCreateInvoice()) {
                $order->createInvoice() && $invoices++;
            }
            if ($order->getChildObject()->canCreateShipment()) {
                $order->createShipment() && $shipments++;
            }
            if ($order->getChildObject()->canCreateTracks()) {
                $order->getChildObject()->createTracks() && $tracks++;
            }
            if ($order->getStatusUpdateRequired()) {
                $order->updateMagentoOrderStatus();
            }

            $tempPercent += $percentPerOrder;

            if (floor($tempPercent) > 0) {
                $currentPercent += floor($tempPercent);
                $tempPercent -= floor($tempPercent);

                $this->_lockItem->setPercents($currentPercent);
                $this->_lockItem->activate();
            }
        }
        //---------------------------

        $this->_profiler->saveTimePoint(__METHOD__.'magento_orders_process'.$account->getId());

        $this->_profiler->addTitle('Total count magento orders created: ' . $magentoOrders);
        $this->_profiler->addTitle('Total count payment transactions created: ' . $paymentTransactions);
        $this->_profiler->addTitle('Total count invoices created: ' . $invoices);
        $this->_profiler->addTitle('Total count shipments created: ' . $shipments);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('End account "'.$account->getTitle().'"');
    }

    //####################################

    private function prepareSinceTime($sinceTime)
    {
        if (is_null($sinceTime)) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify('-10 days');
        } else {
            $sinceTime = new DateTime($sinceTime, new DateTimeZone('UTC'));
        }

        return Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($sinceTime);
    }

    //####################################
}