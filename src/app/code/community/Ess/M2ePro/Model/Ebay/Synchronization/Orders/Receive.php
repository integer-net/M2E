<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
*/

final class Ess_M2ePro_Model_Ebay_Synchronization_Orders_Receive
    extends Ess_M2ePro_Model_Ebay_Synchronization_Orders_Abstract
{
    // ##########################################################

    protected function getNick()
    {
        return '/receive/';
    }

    protected function getTitle()
    {
        return 'Orders Receive';
    }

    // ----------------------------------------------------------

    protected function getPercentsStart()
    {
        return 0;
    }

    protected function getPercentsEnd()
    {
        return 100;
    }

    // ##########################################################

    protected function performActions()
    {
        $permittedAccounts = $this->getPermittedAccounts();
        if (empty($permittedAccounts)) {
            return;
        }

        $iteration = 1;
        $percentsForOneStep = $this->getPercentsInterval() / count($permittedAccounts);

        foreach ($permittedAccounts as $account) {
            /** @var $account Ess_M2ePro_Model_Account **/

            // ----------------------------------------------------------
            $this->getActualOperationHistory()->addText('Starting account "'.$account->getTitle().'"');
            $this->getActualOperationHistory()->addTimePoint(__METHOD__.'get'.$account->getId(),'Get orders from eBay');

            // ->__('The "Receive" action for eBay account "%s" is in data receiving state...')
            $status = 'The "Receive" action for eBay account "%s" is in data receiving state...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ----------------------------------------------------------

            $ebayOrders = $this->processEbayOrders($account);

            // ----------------------------------------------------------
            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep * 0.3);

            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());
            $this->getActualOperationHistory()->addTimePoint(
                __METHOD__.'create_magento_orders'.$account->getId(),
                'Create magento orders'
            );

            // ->__('The "Receive" action for eBay account "%s" is in order creation state...')
            $status = 'The "Receive" action for eBay account "%s" is in order creation state...';
            $this->getActualLockItem()->setStatus(Mage::helper('M2ePro')->__($status, $account->getTitle()));
            // ----------------------------------------------------------

            $this->createMagentoOrders($ebayOrders);

            // ----------------------------------------------------------
            $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'create_magento_orders'.$account->getId());

            $this->getActualLockItem()->setPercents($this->getPercentsStart() + $iteration * $percentsForOneStep);
            $this->getActualLockItem()->activate();
            // ----------------------------------------------------------

            $iteration++;
        }
    }

    // ##########################################################

    private function getPermittedAccounts()
    {
        /** @var $accountsCollection Mage_Core_Model_Mysql4_Collection_Abstract */
        $accountsCollection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $accountsCollection->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Ebay_Account::ORDERS_MODE_YES);

        return $accountsCollection->getItems();
    }

    // ----------------------------------------------------------

    private function processEbayOrders($account)
    {
        $fromTime = $this->prepareFromTime($account);

        $request = array(
            'last_update' => $fromTime,
        );

        $entity = 'sales';
        $type   = 'get';
        $name   = 'list';

        $response = Mage::getModel('M2ePro/Connector_Ebay_Dispatcher')
            ->processVirtual($entity, $type, $name, $request, NULL, NULL, $account);

        $this->getActualOperationHistory()->saveTimePoint(__METHOD__.'get'.$account->getId());

        $ebayOrders = array();
        $toTime = $fromTime;

        if (isset($response['orders']) && isset($response['updated_to'])) {
            $ebayOrders = $response['orders'];
            $toTime = $response['updated_to'];
        }

        if (empty($ebayOrders)) {
            return array();
        }

        $orders = array();

        foreach ($ebayOrders as $ebayOrderData) {
            /** @var $ebayOrder Ess_M2ePro_Model_Ebay_Order_Builder */
            $ebayOrder = Mage::getModel('M2ePro/Ebay_Order_Builder');
            $ebayOrder->initialize($account, $ebayOrderData);

            $orders[] = $ebayOrder->process();
        }

        $this->saveLastUpdateTime($account, $toTime);

        return array_filter($orders);
    }

    private function createMagentoOrders($ebayOrders)
    {
        foreach ($ebayOrders as $order) {
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
            if ($order->getStatusUpdateRequired()) {
                $order->updateMagentoOrderStatus();
            }
        }
    }

    // ##########################################################

    private function prepareFromTime(Ess_M2ePro_Model_Account $account)
    {
        $lastSynchronizationDate = $account->getData('orders_last_synchronization');

        if (is_null($lastSynchronizationDate)) {
            $sinceTime = new DateTime('now', new DateTimeZone('UTC'));
            $sinceTime->modify('-10 days');

            $sinceTime = Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($sinceTime);
            $this->saveLastUpdateTime($account, $sinceTime);

            return $sinceTime;
        }

        $sinceTime = new DateTime($lastSynchronizationDate, new DateTimeZone('UTC'));
        return Ess_M2ePro_Model_Connector_Ebay_Abstract::ebayTimeToString($sinceTime);
    }

    private function saveLastUpdateTime(Ess_M2ePro_Model_Account $account, $lastUpdateTime)
    {
        $account->setData('orders_last_synchronization', $lastUpdateTime)->save();
    }

    // ##########################################################
}